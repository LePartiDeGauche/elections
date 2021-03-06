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

namespace PartiDeGauche\ElectionsBundle\Repository\ModificationSignature;

use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;

class TerritoireModificationSignature
{
    /**
     * L'échéance
     * @var Echeance
     */
    private $echeance;

    /**
     * Le territoire dont on enregistre la dernière modifs.
     * @var AbstractTerritoire
     */
    private $territoire;

    /**
     * La signature.
     * @var string
     */
    private $signature;

    public function __construct(AbstractTerritoire $territoire, Echeance $echeance)
    {
        $this->territoire = $territoire;
        $this->echeance = $echeance;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function setSignature($signature)
    {
        $this->signature = $signature;
    }
}
