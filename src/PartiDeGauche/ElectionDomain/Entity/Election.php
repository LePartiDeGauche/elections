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

namespace PartiDeGauche\ElectionDomain\Entity;

use PartiDeGauche\ElectionDomain\CandidatInterface;
use PartiDeGauche\ElectionDomain\CirconscriptionInterface;
use PartiDeGauche\ElectionDomain\TerritoireInterface;
use PartiDeGauche\ElectionDomain\VO\Score;
use PartiDeGauche\ElectionDomain\VO\VoteInfo;

class Election
{
    /**
     * Les candidats à l'élection.
     * @var array
     */
    private $candidats = array();

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
     * Le nombre d'inscrits.
     * @var integer
     */
    private $inscrits;

    /**
     * Les socres des candidats.
     * @var \SplObjectStorage
     */
    private $scores;

    /**
     * Les éventuelles informations sur le vote.
     * @var \SplObjectStorage
     */
    private $voteInfos;

    /**
     * Constructeur d'objet élections.
     * @param Echeance $echeance L'échéance de l'élection.
     */
    public function __construct(Echeance $echeance,
        CirconscriptionInterface $circonscription)
    {
        $this->echeance = $echeance;
        $this->circonscription = $circonscription;
        $this->voteInfos = new \SplObjectStorage();
        $this->scores = new \SplObjectStorage();
    }

    /**
     * Ajouter un candidat à l'élection.
     * @param CandidatInterface $candidat Le candidat à ajouter.
     */
    public function addCandidat(CandidatInterface $candidat)
    {
        $this->candidats[] = $candidat;
        $this->scores[$candidat] = new \SplObjectStorage();
    }

    /**
     * Récupérer les candidats à l'élection.
     * @return array<Candidats> Les candidats à l'élection.
     */
    public function getCandidats()
    {
        return $this->candidats;
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

        return $this->scores[$candidat][$territoire];
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

        return $this->voteInfos[$territoire];
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

        if (isset($this->voteInfos[$territoire])) {
            $exprimes = $this->voteInfos[$territoire]->getExprimes();
        }

        if (isset($exprimes)) {
            $score =
                Score::fromPourcentageAndExprimes($pourcentage, $exprimes);
            $this->scores[$candidat][$territoire] = $score;

            return;
        }

        $this->scores[$candidat][$territoire] = Score::fromPourcentage($pourcentage);
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

        if (isset($this->voteInfos[$territoire])) {
            $exprimes = $this->voteInfos[$territoire]->getExprimes();
        }

        if (isset($exprimes)) {
            $score = Score::fromVoixAndExprimes($voix, $exprimes);
            $this->scores[$candidat][$territoire] = $score;

            return;
        }

        $this->scores[$candidat][$territoire] = Score::fromVoix($voix);
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

        $this->voteInfos[$territoire] = $voteInfo;
    }
}
