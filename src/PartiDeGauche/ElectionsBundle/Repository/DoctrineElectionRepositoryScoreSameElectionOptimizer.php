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

use PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat;
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\Entity\Election\Election;
use PartiDeGauche\ElectionDomain\VO\Score;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\CirconscriptionEuropeenne;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Region;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Pays;

class DoctrineElectionRepositoryScoreSameElectionOptimizer
{
    private $cache = array();

    public function __construct($doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->cache['Score'] = array();
    }

    public function getScore(
        Echeance $echeance,
        $territoire,
        Candidat $candidat
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
                $score = $this->doScoreCircoEuroQuery($echeance, $territoire, $candidat);
            }
            if ($territoire instanceof Pays) {
                $score = $this->getScore(
                    $echeance,
                    $territoire->getCirconscriptionsEuropeennes(),
                    $candidat
                );
            }
        }

        return $score ? $score : new Score();
    }

    public function reset()
    {
        $this->cache['Score'] = array();
    }

    private function cacheScoreResult(
        Echeance $echeance,
        AbstractTerritoire $territoire,
        $results,
        $cacheId
    ) {
        if (!isset($this->cache['Score'][$cacheId])) {
            $this->cache['Score'][$cacheId] = new \SplObjectStorage();
        }

        $cache = $this->cache['Score'][$cacheId];

        if (!isset($cache[$echeance])) {
            $cache[$echeance] = new \SplObjectStorage();
        }

        $cache[$echeance][$territoire] = $results;

        $this->cache['Score'][$cacheId] = $cache;
    }

    private function fetchScoreResult(
        Echeance $echeance,
        AbstractTerritoire $territoire,
        Candidat $candidat,
        $cacheId
    ) {
        if (!isset($this->cache['Score'][$cacheId])) {
            return null;
        }

        $cache = $this->cache['Score'][$cacheId];

        if (
            !isset($cache[$echeance])
            || !isset(
                $cache[$echeance][$territoire]
            )
        ) {
                return null;
        }

        $results = $cache[$echeance][$territoire];

        if (0 === count($results)) {
            return false;
        }

        $total = 0;
        foreach ($results as $result) {
            if ($result['candidat'] === $candidat) {
                $total += $result['voix'];
            }
        }

        return $total;
    }

    private function doScoreCircoEuroQuery(
        Echeance $echeance,
        CirconscriptionEuropeenne $circo,
        $nuance
    ) {
        $result = $this->fetchScoreResult(
            $echeance,
            $circo,
            $nuance,
            'doScoreCircoEuroQuery'
        );
        if (is_integer($result)) {
            return Score::fromVoix($result);
        }

        if (false === $result) {
            return null;
        }

        $query = $this
            ->em
            ->createQuery(
                'SELECT territoire.id AS region
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Region
                    region_,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                JOIN score.territoire territoire
                WHERE region_.circonscriptionEuropeenne = :circo
                    AND score.territoire = region_
                    AND election.echeance = :echeance'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'circo' => $circo,
            ))
        ;
        $regionsAcResultats = $query->getResult();
        $regionsAcResultats = array_map(function ($line) {
            return $line['region'];
        }, $regionsAcResultats);

        $regCondition = '';
        if (count($regionsAcResultats) > 0) {
            $regCondition = 'AND region NOT IN (:regionsAcResultats)';
        }

        $query = $this
            ->em
            ->createQuery(
                'SELECT territoire.id AS departement
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement
                    departement_,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                JOIN score.territoire territoire
                WHERE departement_.region = :circo
                    AND score.territoire = departement_
                    AND election.echeance = :echeance'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'circo' => $circo,
            ))
        ;
        $departementsAcResultats = $query->getResult();
        $departementsAcResultats = array_map(function ($line) {
            return $line['departement'];
        }, $departementsAcResultats);

        $depCondition = '';
        if (count($departementsAcResultats) > 0) {
            $depCondition = 'AND departement NOT IN (:departementsAcResultats)';
        }

        $query = $this
            ->em
            ->createQuery(
                'SELECT candidat_ AS candidat, SUM(score.scoreVO.voix) AS voix
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Region
                    region,
                    PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                    candidat_,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                WHERE region.circonscriptionEuropeenne  = :circo
                    AND score.territoire = region
                    AND score.candidat = candidat_
                    AND election.echeance = :echeance
                GROUP BY candidat_'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'circo' => $circo
            ))
        ;
        $results0 = $query->getResult();

        $query = $this
            ->em
            ->createQuery(
                'SELECT candidat_ AS candidat, SUM(score.scoreVO.voix) AS voix
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Region
                    region,
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement
                    departement,
                    PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                    candidat_,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                WHERE region.circonscriptionEuropeenne = :circo
                    '. $regCondition . '
                    AND departement.region  = region
                    AND score.territoire = departement
                    AND score.candidat = candidat_
                    AND election.echeance = :echeance
                GROUP BY candidat_'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'circo' => $circo
            ))
        ;
        if (count($regionsAcResultats) > 0) {
            $query->setParameter(
                'regionsAcResultats',
                $regionsAcResultats
            );
        }
        $results1 = $query->getResult();

        $query = $this
            ->em
            ->createQuery(
                'SELECT candidat_ AS candidat, SUM(score.scoreVO.voix) AS voix
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Region
                    region,
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement
                    departement,
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune
                    commune,
                    PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                    candidat_,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                WHERE region.circonscriptionEuropeenne = :circo
                    '. $regCondition . '
                    AND departement.region  = region
                    '. $depCondition . '
                    AND commune.departement = departement
                    AND score.territoire = commune
                    AND score.candidat = candidat_
                    AND election.echeance = :echeance
                GROUP BY candidat_'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'circo' => $circo
            ))
        ;
        if (count($regionsAcResultats) > 0) {
            $query->setParameter(
                'regionsAcResultats',
                $regionsAcResultats
            );
        }
        if (count($departementsAcResultats) > 0) {
            $query->setParameter(
                'departementsAcResultats',
                $departementsAcResultats
            );
        }
        $results2 = $query->getResult();

        $results = array_merge($results0, $results1, $results2);

        $this->cacheScoreResult(
            $echeance,
            $circo,
            $results,
            'doScoreCircoEuroQuery'
        );

        $result = $this->fetchScoreResult(
            $echeance,
            $circo,
            $nuance,
            'doScoreCircoEuroQuery'
        );

        return is_integer($result )? Score::fromVoix($result) : null;
    }

    private function doScoreDepartementQuery(
        Echeance $echeance,
        Departement $departement,
        $candidat
    ) {
        $result = $this->fetchScoreResult(
            $echeance,
            $departement,
            $candidat,
            'doScoreDepartementQuery'
        );
        if (is_integer($result)) {
            return Score::fromVoix($result);
        }

        if (false === $result) {
            return null;
        }

        $query = $this
            ->em
            ->createQuery(
                'SELECT candidat_ AS candidat, SUM(score.scoreVO.voix) as voix
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune
                    commune,
                    PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                    candidat_,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                WHERE commune.departement  = :departement
                    AND score.territoire = commune
                    AND score.candidat = candidat_
                    AND election.echeance = :echeance
                GROUP BY candidat_'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'departement' => $departement,
            ))
        ;

        $results = $query->getResult();
        $this->cacheScoreResult(
            $echeance,
            $departement,
            $results,
            'doScoreDepartementQuery'
        );

        $result = $this->fetchScoreResult(
            $echeance,
            $departement,
            $candidat,
            'doScoreDepartementQuery'
        );

        return is_integer($result )? Score::fromVoix($result) : null;
    }

    private function doScoreRegionQuery(
        Echeance $echeance,
        Region $region,
        $candidat
    ) {
        $result = $this->fetchScoreResult(
            $echeance,
            $region,
            $candidat,
            'doScoreRegionQuery'
        );
        if (is_integer($result)) {
            return Score::fromVoix($result);
        }

        if (false === $result) {
            return null;
        }

        $query = $this
            ->em
            ->createQuery(
                'SELECT territoire.id AS departement
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement
                    departement_,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                JOIN score.territoire territoire
                WHERE departement_.region = :region
                    AND score.territoire = departement_
                    AND election.echeance = :echeance'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'region' => $region,
            ))
        ;
        $departementsAcResultats = $query->getResult();
        $departementsAcResultats = array_map(function ($line) {
            return $line['departement'];
        }, $departementsAcResultats);

        $depCondition = '';
        if (count($departementsAcResultats) > 0) {
            $depCondition = 'AND departement NOT IN (:departementsAcResultats)';
        }

        $query = $this
            ->em
            ->createQuery(
                'SELECT candidat_ AS candidat, SUM(score.scoreVO.voix) AS voix
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement
                    departement,
                    PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                    candidat_,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                WHERE departement.region  = :region
                    AND score.territoire = departement
                    AND score.candidat = candidat_
                    AND election.echeance = :echeance
                GROUP BY candidat_'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'region' => $region
            ))
        ;
        $results1 = $query->getResult();

        $query = $this
            ->em
            ->createQuery(
                'SELECT candidat_ AS candidat, SUM(score.scoreVO.voix) AS voix
                FROM
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement
                    departement,
                    PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune
                    commune,
                    PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                    candidat_,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                WHERE departement.region  = :region
                    '. $depCondition . '
                    AND commune.departement = departement
                    AND score.territoire = commune
                    AND score.candidat = candidat_
                    AND election.echeance = :echeance
                GROUP BY candidat_'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'region' => $region
            ))
        ;
        if (count($departementsAcResultats) > 0) {
            $query->setParameter(
                'departementsAcResultats',
                $departementsAcResultats
            );
        }
        $results2 = $query->getResult();

        $results = array_merge($results1, $results2);

        $this->cacheScoreResult(
            $echeance,
            $region,
            $results,
            'doScoreRegionQuery'
        );

        $result = $this->fetchScoreResult(
            $echeance,
            $region,
            $candidat,
            'doScoreRegionQuery'
        );

        return is_integer($result )? Score::fromVoix($result) : null;
    }

    private function doScoreQuery(
        Echeance $echeance,
        $territoire,
        $candidat
    ) {
        $result = $this->fetchScoreResult(
            $echeance,
            $territoire,
            $candidat,
            'doScoreQuery'
        );
        if (is_integer($result)) {
            return Score::fromVoix($result);
        }

        if (false === $result) {
            return null;
        }

        $query = $this
            ->em
            ->createQuery(
                'SELECT candidat_ AS candidat, score.scoreVO.voix AS voix
                FROM
                    PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat
                    candidat_,
                    PartiDeGauche\ElectionDomain\Entity\Election\ScoreAssignment
                    score
                JOIN score.election election
                WHERE score.territoire  = :territoire
                    AND score.candidat = candidat_
                    AND election.echeance = :echeance
                GROUP BY candidat_'
            )
            ->setParameters(array(
                'echeance' => $echeance,
                'territoire' => $territoire,
            ))
        ;

        $results = $query->getResult();
        $this->cacheScoreResult(
            $echeance,
            $territoire,
            $results,
            'doScoreQuery'
        );

        $result = $this->fetchScoreResult(
            $echeance,
            $territoire,
            $candidat,
            'doScoreQuery'
        );

        return is_integer($result )? Score::fromVoix($result) : null;
    }
}
