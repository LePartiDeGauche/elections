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

class ListeCandidate extends Candidat
{
    /**
     * L'élection à laquelle la liste était candidate.
     * @var Election
     */
    private $election;

    /**
     * Le nom de la liste.
     * @var string
     */
    private $nom;

    /**
     * Constructeur d'objet personne.
     * @param string $nom    Le nom de la liste.
     */
    public function __construct(Election $election, $nom)
    {
        \Assert\that($nom)->string();

        $this->election = $election;
        $election->addCandidat($this);
        $this->nom = $nom;
    }

    /**
     * Retourne le nom de l'élection.
     * @return string Le nom de l'élection.
     */
    public function __toString()
    {
        return $this->nom;
    }

    /**
     * Récupérer l'élection à laquelle participait la liste.
     * @return Election L'élection à laquelle participait la liste.
     */
    public function getElection()
    {
        return $this->election;
    }
}
