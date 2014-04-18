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

namespace PartiDeGauche\ElectionDomain\VO;

use PartiDeGauche\ElectionDomain\Entity\Candidat;
use PartiDeGauche\ElectionDomain\Entity\Election;
use PartiDeGauche\ElectionDomain\TerritoireInterface;

class Score
{
    /**
     * Le score nombre de voix.
     * @var integer
     */
    private $voix;

    /**
     * Le score en pourcentage des exprimés.
     * @var float
     */
    private $pourcentage;

    /**
     * Créer un objet Score.
     * @param  integer $pourcentage Le score en pourcentage des exprimés.
     * @return Score               Un nouvel object Score.
     */
    public static function fromPourcentage($pourcentage)
    {
        $score = new self();

        $score->pourcentage = $pourcentage;

        return $score;
    }

    /**
     * Créer un objet Score.
     * @param  integer $pourcentage Le score en pourcentage des exprimés.
     * @param  integer $exprimes    Le nombre de suffrages exprimés.
     * @return Score               Un nouvel object Score.
     */
    public static function fromPourcentageAndExprimes($pourcentage, $exprimes)
    {
        $score = new self();

        $score->pourcentage = $pourcentage;
        $score->voix = round($exprimes*$pourcentage/100);

        return $score;
    }

    /**
     * Créer un objet Score contenant juste un nombre de voix.
     * @param  integer $voix Le nombre de voix.
     * @return Score         Un nouvel object Score.
     */
    public static function fromVoix($voix)
    {
        $score = new self();

        $score->voix = $voix;
        $score->pourcentage = null;

        return $score;
    }

    /**
     * Créer un objets Score.
     * @param  integer $voix     Le nombre de voix fait à l'élection.
     * @param  integer $exprimes Le nombre de suffrage exprimés à l'élection.
     * @return Score            Un nouvel object Score.
     */
    public static function fromVoixAndExprimes($voix, $exprimes)
    {
        $score = new self();

        $score->voix = $voix;
        $score->pourcentage = ($voix / $exprimes) * 100;

        return $score;
    }

    /**
     * Récupérer le score en pourcentage.
     * @return float Le score en pourcentage.
     */
    public function toPourcentage()
    {
        return $this->pourcentage;
    }

    /**
     * Récupérer le score en nombre de voix.
     * @return integer Le scre en nombre de voix.
     */
    public function toVoix()
    {
        return $this->voix;
    }

}
