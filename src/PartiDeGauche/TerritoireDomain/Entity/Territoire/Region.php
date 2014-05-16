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

class Region extends AbstractTerritoire
{
    /**
     * La circonscription européenne de la région.
     * @var CirconscriptionEuropeenne
     */
    private $circonscriptionEuropeenne;

    /**
     * Le code de la région. Peut-être composé de lettres pour les
     * région d'outre-mer.
     * @var string
     */
    private $code;

    /**
     * Les départements présents dans la région.
     * @var ArrayCollection
     */
    private $departements;

    /**
     * La France, le pays de la région.
     * @var Region
     */
    private $pays;

    /**
     * Constructeur d'objet département.
     * @param string $code Le code de la région.
     * @param string $nom  Le nom de la région.
     */
    public function __construct(Pays $pays, $code, $nom)
    {
        \Assert\that((string) $code)
            ->string()
            ->maxLength(
                4,
                'Le code de la région ne peut dépasser 4 caractères.'
            )
        ;

        \Assert\that($nom)
            ->string()
            ->maxLength(
                255,
                'Le nom de la région ne peut dépasser 255 caractères.'
            )
        ;

        $this->pays = $pays;
        $this->code = (string) $code;
        $this->nom = $nom;
        $this->departements = new ArrayCollection();
    }

    /**
     * @internal
     * Pour mettre à jour les relations entre les entités.
     * @param Departement $departement Le département à ajouter
     */
    public function addDepartement(Departement $departement)
    {
        if (!$this->departements->contains($departement)) {
            $this->departements[] = $departement;
        }
    }

    /**
     * Récupérer la circonscriptions européennes de la réion.
     * @return CirconscriptionEuropeenne La circonscription européenne.
     */
    public function getCirconscriptionEuropeenne()
    {
        return $this->circonscriptionEuropeenne;
    }

    /**
     * Récupérer le code de la région.
     * @return string Le code de la région.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Récupérer les départements de la région.
     * @return ArrayCollection
     */
    public function getDepartements()
    {
        return $this->departements;
    }

    /**
     * Récupérer la France, le pays de la région.
     * @return Pays La France.
     */
    public function getPays()
    {
        return $this->pays;
    }

    /**
     * Régler la circonscription européenne.
     * @param CirconscriptionEuropeenne $circonscription La circonscription
     */
    public function setCirconscriptionEuropeenne(
        CirconscriptionEuropeenne $circonscription
    ) {
        if (!$this->circonscriptionEuropeenne) {
            $this->circonscriptionEuropeenne = $circonscription;
            $circonscription->addRegion($this);

            return $this;
        }

        throw new Exception(
            'On ne peut pas changer la circonscription européenne d\'une région'
        );
    }
}
