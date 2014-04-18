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

use PartiDeGauche\ElectionDomain\VO\Score;
use PartiDeGauche\ElectionDomain\TerritoireInterface;

class ScoreAssignment
{
    private $id;
    private $election;
    private $candidat;
    private $territoire;
    private $scoreVO;

    public function getCandidat()
    {
        return $this->candidat;
    }

    public function __construct(Score $score = null, Election $election,
        Candidat $candidat, TerritoireInterface $territoire)
    {
        $this->scoreVO = $score;
        $this->candidat = $candidat;
        $this->election = $election;
        $this->territoire = $territoire;
    }

    public function getElection()
    {
        return $this->election;
    }

    public function getTerritoire()
    {
        return $this->territoire;
    }

    public function getScoreVO()
    {
        return $this->scoreVO;
    }

    public function setScoreVO(Score $score)
    {
        $this->scoreVO = $score;
    }
}
