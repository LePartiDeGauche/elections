<?php
/*
 * This file is part of the Parti de Gauche elections data project.
 *
 * The Parti de Gauche elections data project is free software: you can
 * redistribute it and/or modify it under the terms of the GNU Affero General
 * Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * The Parti de Gauche elections data project is distributed in the hope
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with the Parti de Gauche elections data project.
 * If not, see <http://www.gnu.org/licenses/>.
 */

namespace PartiDeGauche\ElectionsBundle\Repository;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException as DoctrineException;
use PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat;
use PartiDeGauche\ElectionDomain\Entity\Candidat\Specification\CandidatNuanceSpecification;
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\Entity\Election\Election;
use PartiDeGauche\ElectionDomain\Entity\Election\ElectionRepositoryInterface;
use PartiDeGauche\ElectionDomain\Entity\Election\UniqueConstraintViolationException;
use PartiDeGauche\ElectionDomain\VO\Score;
use PartiDeGauche\ElectionDomain\VO\VoteInfo;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\CirconscriptionEuropeenne;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Region;

class DoctrineElectionRepository implements ElectionRepositoryInterface
{
    private $cache = array();

    public function __construct($doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->cache['getVoteInfo'] = new \SplObjectStorage();
    }

    public function add(Election $element)
    {
        $this
            ->em
            ->persist($element);
    }

    public function get(
        Echeance $echeance,
        AbstractTerritoire $circonscription
    ) {
        $election = $this
            ->em
            ->getRepository(
                'PartiDeGauche\ElectionDomain\Entity\Election\Election'
            )
            ->findOneBy(array(
                'echeance' => $echeance,
                'circonscription' => $circonscription
            ))
        ;

        if ($election) {
            return $election;
        }

        if ($circonscription instanceof Commune) {
            return $this->get($echeance, $circonscription->getDepartement());
        }

        if ($circonscription instanceof Departement) {
            return $this->get($echeance, $circonscription->getRegion());
        }

        if ($circonscription instanceof Region) {
            $circo = $circonscription->getCirconscriptionEuropeenne() ?
            $circonscription->getCirconscriptionEuropeenne()
            : $circonscription->getPays();

            return $this->get($echeance, $circo);
        }

        if ($circonscription instanceof CirconscriptionEuropeenne) {
            return $this->get(
                $echeance,
                $circonscription->getPays()
            );
        }
    }

    public function getScore(
        Echeance $echeance,
        $territoire,
        $candidat
    ) {
        if (
            is_array($territoire)
            || $territoire instanceof \ArrayAccess
            || $territoire instanceof \IteratorAggregate
        ) {
            $score = 0;
            foreach ($territoire as $division) {
                $scoreVO = $this->getScore($echeance, $division, $candidat);
                $score += $scoreVO->toVoix();
            }
            if (!$score) {
                return new Score();
            }
            $score = Score::fromVoix($score);
        }

        if (!isset($score) || !$score) {
            $score = $this->doScoreQuery($echeance, $territoire, $candidat);
        }

        if (!$score) {
            if ($territoire instanceof Region) {
                $score = $this->doScoreRegionQuery($echeance, $territoire, $candidat);
            }
            if ($territoire instanceof Departement) {
                $score = $this->doScoreDepartementQuery($echeance, $territoire, $candidat);
            }
            if ($territoire instanceof CirconscriptionEuropeenne) {
                $score = $this->getScore(
                    $echeance,
                    $territoire->getRegions(),
                    $candidat
                );
            }
        }

        return $score ?
            Score::fromVoixAndExprimes(
                $score->toVoix(),
                $this->getVoteInfo($echeance, $territoire)->getExprimes()
            )
            : new Score();
    }

    public function getVoteInfo(Echeance $echeance, $territoire)
    {
        if (
            is_array($territoire)
            || $territoire instanceof \ArrayAccess
            || $territoire instanceof \IteratorAggregate
        ) {
            $exprimes = 0;
            $votants = 0;
            $inscrits = 0;
            foreach ($territoire as $division) {
                $voteInfoVO = $this->getVoteInfo($echeance, $division);
                if ($voteInfoVO) {
                    $exprimes += $voteInfoVO->getExprimes();
                    $votants += $voteInfoVO->getVotants();
                    $inscrits += $voteInfoVO->getInscrits();
                }
            }
            if (!$exprimes && !$votants && !$inscrits) {
                return new VoteInfo(null, null, null);
            }
            $voteInfo = new VoteInfo($inscrits, $votants, $exprimes);
        }

        if (
            isset($this->cache['getVoteInfo'][$echeance])
            && isset($this->cache['getVoteInfo'][$echeance][$territoire])
        ) {
            return $this->cache['getVoteInfo'][$echeance][$territoire];
        }

        if (!isset($voteInfo) || !$voteInfo || !$voteInfo->getExprimes()) {
            $voteInfo = $this->doVoteInfoQuery($echeance, $territoire);
        }

        if (!$voteInfo || !$voteInfo->getExprimes()) {
            if ($territoire instanceof Region) {
                $voteInfo =  $this->doVoteInfoRegionQuery($echeance, $territoire);
            }
            if ($territoire instanceof Departement) {
                $voteInfo = $this->doVoteInfoDepartementQuery($echeance, $territoire);
            }
            if ($territoire instanceof CirconscriptionEuropeenne) {
                $voteInfo = $this->getVoteInfo(
                    $echeance,
                    $territoire->getRegions()
                );
            }
        }

        if (!isset($this->cache['getVoteInfo'][$echeance])) {
            $this->cache['getVoteInfo'][$echeance] = new \SplObjectStorage();
        }
        $this->cache['getVoteInfo'][$echeance][$territoire] = $voteInfo;

        return $voteInfo;
    }

    public function remove(Election $element)
    {
        $this->em->remove($element);
    }

    public function save()
    {
        try {
            $this->em->flush();
        } catch (DoctrineException $exception) {
            throw new UniqueConstraintViolationException(
                $exception->getMessage()
            );
        } catch (\Doctrine\DBAL\Exception\DriverException $exception) {
            throw new UniqueConstraintViolationException(
                $exception->getMessage()
            );
        }

        $this->cache['getVoteInfo'] = new \SplObjectStorage;
    }

    private function doScoreDepartementQuery(
        Echeance $echeance,
        Departement $territoire,
        $candidat
    ) {
        $query = $this
            ->em
            ->createQuery(
                'SELECT SUM(score.scoreVO.voix)
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune
                    territoire,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                WHERE territoire.departement  = :territoire
                    AND score.territoire = territoire
                    AND score.candidat
                        IN (' . $this->getCandidatSubquery($candidat) . ')
                    AND election.echeance = :echeance'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'territoire' => $territoire,
            ))
        ;
        if ($candidat instanceof CandidatNuanceSpecification) {
            $query->setParameter('nuances', $candidat->getNuances());
        } else {
            $query->setParameter('candidat', $candidat);
        }

        $result = $query->getSingleScalarResult();

        return $result ? Score::fromVoix($result) : null;
    }

    private function doScoreRegionQuery(
        Echeance $echeance,
        Region $territoire,
        $candidat
    ) {

        $query = $this
            ->em
            ->createQuery(
                'SELECT SUM(score.scoreVO.voix) as total, territoire.id
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement
                    departement,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                JOIN score.territoire territoire
                WHERE departement.region  = :territoire
                AND score.territoire = departement
                AND score.candidat
                    IN (' . $this->getCandidatSubquery($candidat) . ')
                AND election.echeance = :echeance'
            )
                        ->setParameters(array(
                'echeance' => $echeance,
                'territoire' => $territoire,
            ))
        ;
        if ($candidat instanceof CandidatNuanceSpecification) {
            $query->setParameter('nuances', $candidat->getNuances());
        } else {
            $query->setParameter('candidat', $candidat);
        }

        $departementsAcResultats = $query->getResult();
        $result = $departementsAcResultats[0]['total'];
        $departementsAcResultats = array_map(function ($line) {
            return $line['id'];
        }, $departementsAcResultats);
        $departementsAcResultats = array_filter($departementsAcResultats, function ($element) {
            return ($element);
        });

        $query = $this
            ->em
            ->createQuery(
                'SELECT SUM(score.scoreVO.voix)
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement
                    departement,
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune
                    commune,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                JOIN score.territoire territoire
                WHERE departement.region  = :territoire
                    ' . (
                        empty($departementsAcResultats) ? ''
                        : 'AND departement NOT IN (:departementsAcResultats)'
                    ) . '
                    AND (
                        commune.departement = departement
                        AND score.territoire = commune
                    )
                    AND score.candidat
                        IN (' . $this->getCandidatSubquery($candidat) . ')
                    AND election.echeance = :echeance'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'territoire' => $territoire
            ))
        ;
        if (!empty($departementsAcResultats)) {
            $query->setParameter('departementsAcResultats', $departementsAcResultats);
        }
        if ($candidat instanceof CandidatNuanceSpecification) {
            $query->setParameter('nuances', $candidat->getNuances());
        } else {
            $query->setParameter('candidat', $candidat);
        }

        $result += $query->getSingleScalarResult();

        return $result ? Score::fromVoix($result) : null;
    }

    private function doScoreQuery(
        Echeance $echeance,
        $territoire,
        $candidat
    ) {
        $query = $this
            ->em
            ->createQuery(
                'SELECT SUM(score.scoreVO.voix)
                FROM
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                WHERE  score.territoire  = :territoire
                    AND score.candidat
                        IN (' . $this->getCandidatSubquery($candidat) . ')
                    AND election.echeance = :echeance'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'territoire' => $territoire,
            ))
        ;
        if ($candidat instanceof CandidatNuanceSpecification) {
            $query->setParameter('nuances', $candidat->getNuances());
        } else {
            $query->setParameter('candidat', $candidat);
        }

        $result = $query->getSingleScalarResult();

        return $result ? Score::fromVoix($result) : null;
    }

    private function doVoteInfoDepartementQuery(
        Echeance $echeance,
        Departement $territoire
    ) {
        $query = $this
            ->em
            ->createQuery(
                'SELECT
                    SUM(voteInfo.voteInfoVO.exprimes) AS exprimes,
                    SUM(voteInfo.voteInfoVO.votants) AS votants,
                    SUM(voteInfo.voteInfoVO.inscrits) AS inscrits
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune
                    territoire,
                    PartiDeGauche\ElectionDomain\Entity\Election\VoteInfoAssignment
                    voteInfo
                JOIN voteInfo.election election
                WHERE territoire.departement  = :territoire
                    AND voteInfo.territoire = territoire
                    AND election.echeance = :echeance'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'territoire' => $territoire,
            ))
        ;

        $result = $query->getSingleResult();

        return !empty($result) ? new VoteInfo(
            $result['inscrits'],
            $result['votants'],
            $result['exprimes']
        ) : new VoteInfo(null, null, null);
    }

    private function doVoteInfoRegionQuery(
        Echeance $echeance,
        Region $territoire
    ) {

        $query = $this
            ->em
            ->createQuery(
                'SELECT
                    SUM(voteInfo.voteInfoVO.exprimes) AS exprimes,
                    SUM(voteInfo.voteInfoVO.votants) AS votants,
                    SUM(voteInfo.voteInfoVO.inscrits) AS inscrits,
                    territoire.id
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement
                    departement,
                    PartiDeGauche\ElectionDomain\Entity\Election\VoteInfoAssignment
                    voteInfo
                JOIN voteInfo.election election
                JOIN voteInfo.territoire territoire
                WHERE departement.region  = :territoire
                AND voteInfo.territoire = departement
                AND election.echeance = :echeance'
            )
                        ->setParameters(array(
                'echeance' => $echeance,
                'territoire' => $territoire,
            ))
        ;

        $departementsAcResultats = $query->getResult();

        $result = $departementsAcResultats[0];

        $departementsAcResultats = array_map(function ($line) {
            return $line['id'];
        }, $departementsAcResultats);
        $departementsAcResultats = array_filter($departementsAcResultats, function ($element) {
            return ($element);
        });

        $query = $this
            ->em
            ->createQuery(
                'SELECT
                    SUM(voteInfo.voteInfoVO.exprimes) AS exprimes,
                    SUM(voteInfo.voteInfoVO.votants) AS votants,
                    SUM(voteInfo.voteInfoVO.inscrits) AS inscrits
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement
                    departement,
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune
                    commune,
                    PartiDeGauche\ElectionDomain\Entity\Election\VoteInfoAssignment
                    voteInfo
                JOIN voteInfo.election election
                JOIN voteInfo.territoire territoire
                WHERE departement.region  = :territoire
                    ' . (
                        empty($departementsAcResultats) ? ''
                        : 'AND departement NOT IN (:departementsAcResultats)'
                    ) . '
                    AND (
                        commune.departement = departement
                        AND voteInfo.territoire = commune
                    )
                    AND election.echeance = :echeance'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'territoire' => $territoire
            ))
        ;
        if (!empty($departementsAcResultats)) {
            $query->setParameter('departementsAcResultats', $departementsAcResultats);
        }

        $result2 = $query->getSingleResult();

        return !empty($result) || !empty($result2) ? new VoteInfo(
            $result['inscrits'] + $result2['inscrits'],
            $result['votants'] + $result2['votants'],
            $result['exprimes'] + $result2['exprimes']
        ) : new VoteInfo(null, null, null);
    }

    private function doVoteInfoQuery(
        Echeance $echeance,
        $territoire
    ) {
        $query = $this
            ->em
            ->createQuery(
                'SELECT
                    SUM(voteInfo.voteInfoVO.exprimes) AS exprimes,
                    SUM(voteInfo.voteInfoVO.votants) AS votants,
                    SUM(voteInfo.voteInfoVO.inscrits) AS inscrits
                FROM
                    PartiDeGauche\ElectionDomain\Entity\Election\VoteInfoAssignment
                    voteInfo
                JOIN voteInfo.election election
                WHERE  voteInfo.territoire  = :territoire
                    AND election.echeance = :echeance'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'territoire' => $territoire,
            ))
        ;

        $result = $query->getSingleResult();

        return !empty($result) ? new VoteInfo(
            $result['inscrits'],
            $result['votants'],
            $result['exprimes']
        ) : new VoteInfo(null, null, null);
    }

    private function getCandidatSubquery($candidat, $n = 0)
    {
        if ($candidat instanceof CandidatNuanceSpecification) {
            return $this
                ->em
                ->createQuery(
                    'SELECT candidat' . $n . '
                    FROM
                        PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                        candidat' . $n . '
                    WHERE candidat' . $n . '.nuance IN (:nuances)'
                )
                ->getDQL()
            ;
        }

        return $this
            ->em
            ->createQuery(
                'SELECT candidat' . $n . '
                FROM
                    PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                    candidat' . $n . '
                WHERE candidat' . $n . ' IN (:candidat)'
            )
            ->getDQL()
        ;
    }
}
