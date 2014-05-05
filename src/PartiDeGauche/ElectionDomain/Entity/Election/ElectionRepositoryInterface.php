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

namespace PartiDeGauche\ElectionDomain\Entity\Election;

use PartiDeGauche\ElectionDomain\Entity\Candidat\Candidat;
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;

/**
 * Voir PartiDeGauche\ElectionDomain\Tests\Entity\Election
 * \ElectionRepositoryTestTrait pour les contraintes à respecter lors d'une
 * implémentation de cette interface.
 */
interface ElectionRepositoryInterface
{
    /**
     * Ajoute une élection dans le repository.
     * @param Election $election L'élection à ajouter.
     */
    public function add(Election $election);

    /**
     * Retourne l'élection avec cette échéance et cette circonscription.
     * @param  Echeance           $echeance        L'échéance.
     * @param  AbstractTerritoire $circonscription La circonscription.
     * @return Election           L'élection ou NULL.
     */
    public function get(
        Echeance $echeance,
        AbstractTerritoire $circonscription
    );

    /**
     * Retire l'élection du repository si elle existe.
     * @param Election $election L'élection à retirer.
     */
    public function remove(Election $election);

    /**
     * Sauvegarde les éléments écrits dans le repository.
     */
    public function save();
}
