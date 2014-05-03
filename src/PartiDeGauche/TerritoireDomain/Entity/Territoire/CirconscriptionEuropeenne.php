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

namespace PartiDeGauche\TerritoireDomain\Entity\Territoire;

use Doctrine\Common\Collections\ArrayCollection;

class CirconscriptionEuropeenne extends AbstractTerritoire
{
    /**
     * Les régions composant la circonscription.
     * @var ArrayCollection
     */
    protected $regions;

    public function __construct($nom)
    {
        parent::__construct($nom);

        $this->regions = new ArrayCollection();
    }

    /**
     * Récupérer les régions composant la circonscription européenne.
     * @return ArrayCollection Les régions composant la circonscription/
     */
    public function getRegions()
    {
        return $this->regions;
    }
}
