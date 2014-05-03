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

class Departement extends AbstractTerritoire
{
    /**
     * Le code du département. Peut-être composé de lettres pour les
     * départements d'outre-mer.
     * @var string
     */
    private $code;

    /**
     * Les circonscriptions législatives présentent dans le département.
     * @var ArrayCollection
     */
    private $circonscriptionsLegislatives;

    /**
     * Les communes présentent dans le département.
     * @var ArrayCollection
     */
    private $communes;

    /**
     * La région du département
     * @var Region
     */
    private $region;

    /**
     * Constructeur d'objet département.
     * @param Region $region La région du département.
     * @param string $code   Le code du département.
     * @param string $nom    Le nom du département.
     */
    public function __construct(Region $region, $code, $nom)
    {
        \Assert\that((string) $code)
            ->string()
            ->maxLength(
                4,
                'Le code du département ne peut dépasser 4 caractères.'
            )
        ;

        \Assert\that($nom)
            ->string()
            ->maxLength(
                255,
                'Le nom du déparement de peut dépasser 255 caractères.'
            )
        ;

        $this->code = (string) $code;
        $this->nom = $nom;
        $this->region = $region;
        $this->communes = new ArrayCollection();
        $this->circonscriptionsLegislatives = new ArrayCollection();
    }

    /**
     * Récupérer le code du département.
     * @return string Le code du département.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Récupérer les circonscriptions législatives.
     * @return ArrayCollection Les circonscriptions législatives du département.
     */
    public function getCirconscriptionsLegislatives()
    {
        return $this->circonscriptionsLegislatives;
    }

    /**
     * Récupérer les communes du département.
     * @return ArrayCollection Les communes du département.
     */
    public function getCommunes()
    {
        return $this->communes;
    }

    /**
     * Récupérer la région du départemet.
     * @return Region La région du département.
     */
    public function getRegion()
    {
        return $this->region;
    }
}
