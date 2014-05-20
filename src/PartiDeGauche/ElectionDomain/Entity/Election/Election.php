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

namespace PartiDeGauche\ElectionDomain\Entity\Election;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\PersistentCollection;
use PartiDeGauche\ElectionDomain\CandidatInterface;
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\VO\Score;
use PartiDeGauche\ElectionDomain\VO\VoteInfo;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;

abstract class Election
{
    /**
     * @var integer
     */
    private $id;

    /**
     * Les candidats à l'élection.
     * @var ArrayCollection
     */
    protected $candidats;

    /**
     * La circonscription de l'élection.
     * @var AbstractTerritoire
     */
    private $circonscription;

    /**
     * L'échéance de l'élection
     * @var Echeance
     */
    private $echeance;

    /**
     * Les scores des candidats.
     * @var ArrayCollection
     */
    private $scores;

    /**
     * Le nombre de sièges de l'élection
     * @var integer
     */
    private $sieges;

    /**
     * Les éventuelles informations sur le vote.
     * @var ArrayCollection
     */
    private $voteInfos;

    /**
     * Constructeur d'objet élections.
     * @param Echeance $echeance L'échéance de l'élection.
     */
    public function __construct(
        Echeance $echeance,
        AbstractTerritoire $circonscription
    ) {
        $this->candidats = new ArrayCollection();

        $this->echeance = $echeance;
        $this->circonscription = $circonscription;
        $this->voteInfos = new ArrayCollection();
        $this->scores = new ArrayCollection();
        $this->cache = array(
            'score' => array(),
            'voteInfo' => array()
        );
    }

    /**
     * Ajouter un candidat à l'élection.
     * @param CandidatInterface $candidat Le candidat à ajouter.
     */
    public function addCandidat(CandidatInterface $candidat)
    {
        $this->candidats[] = $candidat;
    }

    /**
     * Récupérer les candidats à l'élection.
     * @return array<Candidats> Les candidats à l'élection.
     */
    public function getCandidats()
    {
        return $this->candidats->toArray();
    }

    /**
     * Récupérer la circonscription de l'élection.
     * @return AbstractTerritoire La circonscription de l'élection.
     */
    public function getCirconscription()
    {
        return $this->circonscription;
    }

    /**
     * Récupérer l'échéance de l'élection.
     * @return Echeance L'échéance de l'élection.
     */
    public function getEcheance()
    {
        return $this->echeance;
    }

    /**
     * Récupérer le score d'un candidat sur un territoire ou par défaut
     * sur la circonscription.
     * @param  CandidatInterface  $candidat   Le candidat.
     * @param  AbstractTerritoire $territoire Le territoire.
     * @return Score              Le score du candidat.
     */
    public function getScoreCandidat(
        CandidatInterface $candidat,
        AbstractTerritoire $territoire = null
    ) {
        if (null === $territoire) {
            $territoire = $this->circonscription;
        }

        return $this
            ->getScoreAssignmentCandidat($candidat, $territoire)
            ->getScoreVO();
    }

    /**
     * Récupérer le nombre de sièges d'un candidat à l'élection. Retourne
     * null si le calcul est impossible ou le nombre total de sièges est
     * inconnu.
     * @param  CandidatInterface $candidat Le candidat.
     * @return integer           Le nombre de sièges.
     */
    public function getSiegesCandidat(CandidatInterface $candidat)
    {
        if (!$this->sieges) {
            return;
        }

        if (
            Echeance::EUROPEENNES === $this->echeance->getType()
        ) {
            $scores = array();
            $candidats = array();
            foreach ($this->getCandidats() as $c) {
                $score = $this->getScoreCandidat($c);
                for ($i = 1; $i <= $this->sieges; $i++) {
                    if (
                        $score instanceof Score &&
                        (
                            null == $score->toPourcentage()
                            || 5 < $score->toPourcentage()
                        )
                    ) {
                        $scores[] = $score->toVoix()/$i;
                        $candidats[] = $c;
                    }
                }
            }
            arsort($scores);
            $listeSieges = array_slice($scores, 0, $this->sieges, true);
            $sieges = 0;
            foreach ($listeSieges as $key => $score) {
                if (
                    $candidats[$key] === $candidat
                ) {
                    $sieges++;
                }
            }

            return $sieges;
        }
    }

    /**
     * Récupérer les informations sur le vote.
     * @return VoteInfo Les informations sur le vote.
     */
    public function getVoteInfo(AbstractTerritoire $territoire = null)
    {
        if (null === $territoire) {
            $territoire = $this->circonscription;
        }

        return $this->getVoteInfoAssignment($territoire)->getVoteInfoVO();
    }

    /**
     * Récupérer le nombre de sièges disponibles à cette élection.
     * @return integer Le nombre de sièges.
     */
    public function getSieges()
    {
        return $this->sieges;
    }

    /**
     * Mettre à jour le pourcentage de voix d'un candidat par rapport au nombre
     * de suffrages exprimés sur un territoire donné, ou par défaut sur la
     * circonscription de l'élection. Si le nombre de suffrages exprimés est
     * déjà réglé dans l'élection, le nombre de voix est mis à jour
     * automatiquement. Sinon, pourcentage et voix sont effacés et remplacés
     * par cette donnée.
     * @param float              $pourcentage Le nombre de voix du candidat.
     * @param CandidatInterface  $candidat    Le candidat dont il s'agit.
     * @param AbstractTerritoire $territoire  Le territoire du score.
     */
    public function setPourcentageCandidat(
        $pourcentage,
        CandidatInterface $candidat,
        AbstractTerritoire $territoire = null
    ) {
        if (!in_array($candidat, $this->getCandidats())) {
            throw new \Exception(
                'Le candidat doit déjà participer à l\'élection'
                . 'avant d\'avoir un score'
            );
        }

        if (null === $territoire) {
            $territoire = $this->circonscription;
        }

        $voteInfo = $this->getVoteInfo($territoire);

        if ($voteInfo) {
            $exprimes = $voteInfo->getExprimes();
        }

        if (isset($exprimes)) {
            $score =
                Score::fromPourcentageAndExprimes($pourcentage, $exprimes);
        } else {
            $score = Score::fromPourcentage($pourcentage);
        }

        $scoreAssignment = $this->getScoreAssignmentCandidat(
            $candidat,
            $territoire
        );
        $scoreAssignment->setScoreVO($score);
    }

    /**
     * Mettre à jour le nombre de sièges à gagner dans cette élection.
     * @param integer $sieges Le nombre de sieges.
     */
    public function setSieges($sieges)
    {
        $this->sieges = $sieges;
    }

    /**
     * Mettre à jour le nombre de voix d'un candidat sur un territoire donné,
     * ou par défaut sur la circonscription de l'élection. Si le nombre de
     * suffrages exprimés est déjà réglé dans l'élection, le pourcentage est
     * mis à jour automatiquement. Sinon, pourcentage et voix sont effacés et
     * remplacés par cette donnée.
     * @param integer            $voix       Le nombre de voix du candidat.
     * @param CandidatInterface  $candidat   Le candidat dont il s'agit.
     * @param AbstractTerritoire $territoire Le territoire du score.
     */
    public function setVoixCandidat(
        $voix,
        CandidatInterface $candidat,
        AbstractTerritoire $territoire = null
    ) {
        if (!in_array($candidat, $this->getCandidats())) {
            throw new \Exception(
                'Le candidat doit déjà participer à l\'élection'
                . 'avant d\'avoir un score'
            );
        }

        if (null === $territoire) {
            $territoire = $this->circonscription;
        }

        $voteInfo = $this->getVoteInfo($territoire);

        if ($voteInfo) {
            $exprimes = $voteInfo->getExprimes();
        }

        if (isset($exprimes)) {
            $score = Score::fromVoixAndExprimes($voix, $exprimes);
        } else {
            $score = Score::fromVoix($voix);
        }

        $scoreAssignment = $this->getScoreAssignmentCandidat(
            $candidat,
            $territoire
        );
        $scoreAssignment->setScoreVO($score);
    }

    /**
     * Mettre à jour les informations sur le vote.
     * @param VoteInfo $voteInfo Les informations sur le vote.
     */
    public function setVoteInfo(
        VoteInfo $voteInfo,
        AbstractTerritoire $territoire = null
    ) {
        if (null === $territoire) {
            $territoire = $this->circonscription;
        }

        $voteAssigment = $this->getVoteInfoAssignment($territoire);
        $voteAssigment->setVoteInfoVO($voteInfo);
    }

    private function getScoreAssignmentCandidat(
        CandidatInterface $candidat,
        AbstractTerritoire $territoire
    ) {
        // try to fetch from cache
        if (isset(
            $this->cache['score'][spl_object_hash($candidat)],
            $this->cache['score'][spl_object_hash($candidat)][spl_object_hash($territoire)]
        )) {
            return $this->cache['score'][spl_object_hash($candidat)][spl_object_hash($territoire)];
        }

        // if it is not in cache, it has not been changed so we can fetch from database
        if ($this->scores instanceof PersistentCollection) {
            $this->scores->setDirty(false);
        }
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('territoire_id', $territoire->getId()))
        ;
        $collection = $this->scores->matching($criteria);
        if ($this->scores instanceof PersistentCollection) {
            $this->scores->setDirty(true);
        }

        // filter by candidat
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('candidat', $candidat))
            ->andWhere(Criteria::expr()->eq('territoire', $territoire))
        ;
        $array = array_values($collection->matching($criteria)->toArray());
        if (array_key_exists(0, $array)) {
            $scoreAssignment = $array[0];
        }

        // new one if no was found
        if (!isset($scoreAssignment)) {
            $scoreAssignment =
                new ScoreAssignment($this, $candidat, $territoire);
            $this->scores[] = $scoreAssignment;
        }

        // put to cache
        if (!isset($this->cache['score'][spl_object_hash($candidat)])) {
            $this->cache['score'][spl_object_hash($candidat)] = array();
        }
        $this->cache['score'][spl_object_hash($candidat)][spl_object_hash($territoire)] = $scoreAssignment;

        return $scoreAssignment;
    }

    private function getVoteInfoAssignment(AbstractTerritoire $territoire)
    {
        // try to fetch from cache
        if (isset($this->cache['voteInfo'][spl_object_hash($territoire)])) {
            return $this->cache['voteInfo'][spl_object_hash($territoire)];
        }

        // if not in cache, not changed, so we dont mind fetching from db
        if ($this->voteInfos instanceof PersistentCollection) {
            $this->voteInfos->setDirty(false);
        }
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('territoire_id', $territoire->getId()))
        ;
        $collection = $this->voteInfos->matching($criteria);
        if ($this->voteInfos instanceof PersistentCollection) {
            $this->voteInfos->setDirty(true);
        }

        // filter again if territoire_id were not initialized
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('territoire', $territoire))
        ;
        $array = array_values($collection->matching($criteria)->toArray());
        if (array_key_exists(0, $array)) {
            $voteInfoAssignment = $array[0];
        }

        // new one if not found
        if (!isset($voteInfoAssignment)) {
            $voteInfoAssignment = new VoteInfoAssignment($this, $territoire);
            $this->voteInfos[] = $voteInfoAssignment;
        }

        // put to cache
        $this->cache['voteInfo'][spl_object_hash($territoire)] = $voteInfoAssignment;

        return $voteInfoAssignment;
    }
}
