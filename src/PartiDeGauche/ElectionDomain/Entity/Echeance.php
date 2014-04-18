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

namespace PartiDeGauche\ElectionDomain\Entity;

class Echeance
{
    /**
     * @var integer
     */
    private $id;

    /**
     * La date de l'échéance.
     * @var DateTime
     */
    private $date;

    /**
     * Le nom de l'échéance.
     * @var string
     */
    private $nom;

    /**
     * Constructeur d'objet Echeance.
     * @param DateTime $date La date de l'échance.
     * @param string   $nom  Le nom de l'échance.
     */
    public function __construct(\DateTime $date, $nom)
    {
        \Assert\that($nom)
            ->string('Le nom de la commune doit être en toutes lettres.');

        $this->date = $date;
        $this->nom = $nom;
    }

    /**
     * Récupérer la date de l'échéance.
     * @return DateTime La date de l'échéance.
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Récupérer le nom de l'échéance.
     * @return string Le nom de l'échéance.
     */
    public function getNom()
    {
        return $this->nom;
    }
}
