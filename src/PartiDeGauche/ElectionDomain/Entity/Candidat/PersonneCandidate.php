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

namespace PartiDeGauche\ElectionDomain\Entity\Candidat;

use PartiDeGauche\ElectionDomain\Entity\Election\Election;

class PersonneCandidate extends Candidat
{
    /**
     * Le nom de famille de la personne
     * @var string
     */
    private $nom;

    /**
     * Le prénom de la personne.
     * @var string
     */
    private $prenom;

    /**
     * Constructeur d'objet personne.
     * @param string $prenom Le prénom de la personne.
     * @param string $nom    Le nom de la personne.
     */
    public function __construct(Election $election, $nuance, $prenom, $nom)
    {
        \Assert\that($prenom)->nullOr()->string();
        \Assert\that($nom)->string();

        $this->election = $election;
        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->nuance = (string) $nuance;
    }

    public function getNom()
    {
        return $this->prenom 
            . ($this->prenom && $this->nom ? ' ' : '')
            . $this->nom;
    }

    public function __toString()
    {
        return $this->getNom();
    }
}
