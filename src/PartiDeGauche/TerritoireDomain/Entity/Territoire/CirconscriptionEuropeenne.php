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
     * Code de la circo.
     * @var integer
     */
    protected $code;

    /**
     * La France, le pays où sont les circonscription.
     * @var Pays
     */
    protected $pays;

    /**
     * Les régions composant la circonscription.
     * @var ArrayCollection
     */
    protected $regions;

    public function __construct(Pays $pays, $code, $nom)
    {
        $this->nom = $nom;
        $this->code = $code;
        $this->pays = $pays;
        $pays->addCirconscriptionEuropeenne($this);

        $this->regions = new ArrayCollection();
    }

    /**
     * @internal
     * @param Region $region La région à ajouter.
     */
    public function addRegion(Region $region)
    {
        if (!$this->regions->contains($region)) {
            $this->regions[] = $region;
        }
    }

    /**
     * Récupérer le code de la circo.
     * @return integer Le code.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Récupérer la France, le pays où sont les circos
     * @return Pays La France.
     */
    public function getPays()
    {
        return $this->pays;
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
