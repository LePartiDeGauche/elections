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
use PartiDeGauche\ElectionDomain\CandidatInterface;
use PartiDeGauche\ElectionDomain\CirconscriptionInterface;
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\TerritoireInterface;
use PartiDeGauche\ElectionDomain\VO\Score;
use PartiDeGauche\ElectionDomain\VO\VoteInfo;

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
     * @var CirconscriptionInterface
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
     * Les éventuelles informations sur le vote.
     * @var ArrayCollection
     */
    private $voteInfos;

    /**
     * Constructeur d'objet élections.
     * @param Echeance $echeance L'échéance de l'élection.
     */
    public function __construct(Echeance $echeance,
        CirconscriptionInterface $circonscription)
    {
        $this->candidats = new ArrayCollection();

        $this->echeance = $echeance;
        $this->circonscription = $circonscription;
        $this->voteInfos = new ArrayCollection();
        $this->scores = new ArrayCollection();
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
     * @return CirconscriptionInterface La circonscription de l'élection.
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
     * @param  CandidatInterface   $candidat   Le candidat.
     * @param  TerritoireInterface $territoire Le territoire.
     * @return Score                           Le score du candidat.
     */
    public function getScoreCandidat(CandidatInterface $candidat,
        TerritoireInterface $territoire = null)
    {
        if (null === $territoire) {
            $territoire = $this->circonscription;
        }

        return $this
            ->getScoreAssignmentCandidat($candidat, $territoire)
            ->getScoreVO();
    }

    /**
     * Récupérer les informations sur le vote.
     * @return VoteInfo Les informations sur le vote.
     */
    public function getVoteInfo(TerritoireInterface $territoire = null)
    {
        if (null === $territoire) {
            $territoire = $this->circonscription;
        }

        return $this->getVoteInfoAssignment($territoire)->getVoteInfoVO();
    }

    /**
     * Mettre à jour le pourcentage de voix d'un candidat par rapport au nombre
     * de suffrages exprimés sur un territoire donné, ou par défaut sur la
     * circonscription de l'élection. Si le nombre de suffrages exprimés est
     * déjà réglé dans l'élection, le nombre de voix est mis à jour
     * automatiquement. Sinon, pourcentage et voix sont effacés et remplacés
     * par cette donnée.
     * @param float               $pourcentage Le nombre de voix du candidat.
     * @param CandidatInterface   $candidat    Le candidat dont il s'agit.
     * @param TerritoireInterface $territoire  Le territoire du score.
     */
    public function setPourcentageCandidat($pourcentage,
        CandidatInterface $candidat, TerritoireInterface $territoire = null)
    {
        if (!in_array($candidat, $this->getCandidats())) {
            $this->addCandidat($candidat);
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

        $scoreAssignment = $this->getScoreAssignmentCandidat($candidat,
            $territoire);
        $scoreAssignment->setScoreVO($score);

        return;
    }

    /**
     * Mettre à jour le nombre de voix d'un candidat sur un territoire donné,
     * ou par défaut sur la circonscription de l'élection. Si le nombre de
     * suffrages exprimés est déjà réglé dans l'élection, le pourcentage est
     * mis à jour automatiquement. Sinon, pourcentage et voix sont effacés et
     * remplacés par cette donnée.
     * @param integer             $voix        Le nombre de voix du candidat.
     * @param CandidatInterface   $candidat    Le candidat dont il s'agit.
     * @param TerritoireInterface $territoire  Le territoire du score.
     */
    public function setVoixCandidat($voix, CandidatInterface $candidat,
        TerritoireInterface $territoire = null)
    {
        if (!in_array($candidat, $this->getCandidats())) {
            $this->addCandidat($candidat);
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

        $scoreAssignment = $this->getScoreAssignmentCandidat($candidat,
            $territoire);
        $scoreAssignment->setScoreVO($score);
    }

    /**
     * Mettre à jour les informations sur le vote.
     * @param VoteInfo $voteInfo Les informations sur le vote.
     */
    public function setVoteInfo(VoteInfo $voteInfo,
        TerritoireInterface $territoire = null)
    {
        if (null === $territoire) {
            $territoire = $this->circonscription;
        }

        $voteAssigment = $this->getVoteInfoAssignment($territoire);
        $voteAssigment->setVoteInfoVO($voteInfo);
    }

    private function getScoreAssignmentCandidat(CandidatInterface $candidat,
        TerritoireInterface $territoire)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('candidat', $candidat))
            ->andWhere(Criteria::expr()->eq('territoire', $territoire))
        ;
        $array = array_values($this->scores->matching($criteria)->toArray());
        if (array_key_exists(0, $array)) {
            return $array[0];
        }

        $scoreAssignment =
            new ScoreAssignment(null, $this, $candidat, $territoire);
        $this->scores[] = $scoreAssignment;

        return $scoreAssignment;
    }

    private function getVoteInfoAssignment(TerritoireInterface $territoire)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('territoire', $territoire))
        ;
        $array = array_values($this->voteInfos->matching($criteria)->toArray());
        if (array_key_exists(0, $array)) {
            return $array[0];
        }

        $voteInfoAssignment = new VoteInfoAssignment(null, $this, $territoire);
        $this->voteInfos[] = $voteInfoAssignment;

        return $voteInfoAssignment;
    }
}
