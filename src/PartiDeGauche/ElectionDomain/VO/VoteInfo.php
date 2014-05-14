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

class VoteInfo
{
    /**
     * Le nombre d'exprimes.
     * @var integer
     */
    private $exprimes;

    /**
     * Le nombre d'inscrits.
     * @var integer
     */
    private $inscrits;

    /**
     * Le nombre de votants
     * @var integer
     */
    private $votants;

    /**
     * Instancie un objet VoteInfo.
     * @param integer $inscrits Le nombre d'inscrits.
     * @param integer $votants  Le nombre de votants.
     * @param integer $exprimes Le nombre d'exprimes.
     */
    public function __construct($inscrits, $votants, $exprimes)
    {
        \Assert\that((integer) $inscrits)->nullOr()
            ->integer()
            ->min($votants)
            ->min($exprimes)
        ;

        \Assert\that((integer) $votants)->nullOr()
            ->integer()
            ->max($inscrits)
            ->min($exprimes)
        ;

        \Assert\that((integer) $exprimes)->nullOr()
            ->integer()
            ->max($votants)
            ->max($inscrits)
        ;

        $this->inscrits = $inscrits;
        $this->votants = $votants;
        $this->exprimes = $exprimes;
    }

    /**
     * Récupérer le nombre d'exprimes.
     * @return integer Le nombre d'exprimes.
     */
    public function getExprimes()
    {
        return $this->exprimes;
    }

    /**
     * Récupérer le nombre d'inscrits.
     * @return integer Le nombre d'inscrits.
     */
    public function getInscrits()
    {
        return $this->inscrits;
    }

    /**
     * Récupérer le nombre de votants.
     * @return integer Le nombre de votants.
     */
    public function getVotants()
    {
        return $this->votants;
    }
}
