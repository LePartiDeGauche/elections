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
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Region;

class DoctrineElectionRepository implements ElectionRepositoryInterface
{
    public function __construct($em)
    {
        $this->em = $em;
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
        return $election = $this
            ->em
            ->getRepository(
                'PartiDeGauche\ElectionDomain\Entity\Election\Election'
            )
            ->findOneBy(array(
                'echeance' => $echeance,
                'circonscription' => $circonscription
            ))
        ;
    }

    public function getScore(
        Echeance $echeance,
        $territoire,
        $candidat
    ) {

        if (is_array($territoire) || $territoire instanceof \ArrayAccess
            || $territoire instanceof \IteratorAggregate) {
            $score = 0;
            foreach ($territoire as $division) {
                $scoreVO = $this->getScore($echeance, $division, $candidat);
                if ($scoreVO) {
                    $score += $scoreVO->toVoix();
                }
            }
            if ($score) {
                return Score::fromVoix($score);
            }

            return null;
        }

        $score = $this->doScoreQuery($echeance, $territoire, $candidat);
        if ($score) {
            return $score->getScoreVo();
        }

        if ($territoire instanceof Region) {
            $departements = $territoire->getDepartements()->toArray();

            return $this->getScore($echeance, $departements, $candidat);
        }
        if ($territoire instanceof Departement) {
            $communes = $territoire->getCommunes()->toArray();

            return $this->getScore($echeance, $communes, $candidat);
        }
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
    }

    private function doScoreQuery(
        Echeance $echeance,
        $territoire,
        $candidat
    ) {
        $query = $this
            ->em
            ->createQuery(
                'SELECT score
                FROM
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                WHERE election.echeance = :echeance
                    AND score.territoire = :territoire
                    AND
                        score.candidat
                        IN (' . $this->getCandidatSubquery($candidat) . ')'
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

        return $query->getOneOrNullResult();
    }

    private function getCandidatSubquery($candidat)
    {
        if ($candidat instanceof CandidatNuanceSpecification) {
            return $this
                ->em
                ->createQuery(
                    'SELECT candidat
                    FROM
                        PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                        candidat
                    WHERE candidat.nuance IN (:nuances)'
                )
                ->getDQL()
            ;
        }

        return $this
            ->em
            ->createQuery(
                'SELECT candidat
                FROM
                    PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                    candidat
                WHERE candidat IN (:candidat)'
            )
            ->getDQL()
        ;
    }
}
