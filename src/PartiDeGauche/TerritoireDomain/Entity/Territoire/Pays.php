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

class Pays extends AbstractTerritoire
{
    /**
     * Les regions du Pays.
     * @var ArrayCollection
     */
    private $regions;

    /**
     * Les circonscriptions européeennes du pays.
     * @var ArrayCollection
     */
    private $circonscriptionsEuropeennes;

    /**
     * Créer un nouvel objet Pays.
     * @param string $nom Le nom du Pays.
     */
    public function __construct($nom = 'France')
    {
        $this->nom = $nom;
    }

    /**
     * Récupérer les régions du pays.
     * @return ArrayCollection Les régions de France.
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * Récupérer les circos européennes du pays.
     * @return ArrayCollection Les circos de France.
     */
    public function getCirconscriptionsEuropeennes()
    {
        return $this->circonscriptionsEuropeennes;
    }
}
