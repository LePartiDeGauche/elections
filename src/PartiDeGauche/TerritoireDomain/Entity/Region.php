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

namespace PartiDeGauche\TerritoireDomain\Entity;

use PartiDeGauche\TerritoireDomain\AbstractTerritoire;

class Region extends AbstractTerritoire
{
    /**
     * Le code de la région. Peut-être composé de lettres pour les
     * région d'outre-mer.
     * @var string
     */
    private $code;

    /**
     * Le nom de la région.
     * @var string
     */
    private $nom;

    /**
     * Constructeur d'objet département.
     * @param string $code Le code de la région.
     * @param string $nom  Le nom de la région.
     */
    public function __construct($code, $nom)
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

        $this->code = (string) $code;
        $this->nom = $nom;
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
     * Récupérer le nom de la région.
     * @return string Le nom de la région.
     */
    public function getNom()
    {
        return $this->nom;
    }
}
