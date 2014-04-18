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

class Commune extends AbstractTerritoire
{
    /**
     * Le code INSEE de la commune.
     * @var integer
     */
    private $code;

    /**
     * Le département de la commune.
     * @var Departement
     */
    private $departement;

    /**
     * Le nom de la commune.
     * @var string
     */
    private $nom;

    /**
     * Constructeur d'objet Commune.
     * @param Departement $departement Le département de la commune.
     * @param integer     $code        Le code INSEE de la commune.
     * @param string      $nom         Le nom de la commune.
     */
    public function __construct(Departement $departement, $code, $nom)
    {
        \Assert\that((string) $code)->maxLength(10);
        \Assert\that($nom)
            ->string()
            ->maxLength(
                255,
                'Le nom de la commune ne peut dépasser 255 caractères.'
            )
        ;

        $this->departement = $departement;
        $this->code = $code;
        $this->nom = $nom;
    }

    /**
     * Récupérer le code INSEE de la commune.
     * @return integer Le code INSEE de la commune.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Récupérer le département de la commune.
     * @return Departement Le département de la commune.
     */
    public function getDepartement()
    {
        return $this->departement;
    }

    /**
     * Récupérer le nom de la commune.
     * @return string Le nom de la commune.
     */
    public function getNom()
    {
        return $this->nom;
    }
}
