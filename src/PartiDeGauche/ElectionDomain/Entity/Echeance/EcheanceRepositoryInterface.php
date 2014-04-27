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

namespace PartiDeGauche\ElectionDomain\Entity\Echeance;

class UniqueConstraintViolationException extends \Exception { }

/**
 * Voir PartiDeGauche\ElectionDomain\Tests\Entity\Echeance
 * \EcheanceRepositoryTestTrait pour les contraintes que à respecter en
 * implémentant cette interface
 */
interface EcheanceRepositoryInterface
{
    /**
     * Ajouter une échéance au dépot.
     * @param Echeance $echeance L'échéance à ajouter au dépot.
     */
    public function add(Echeance $echeance);

    /**
     * Récupérer une échéance par son nom.
     * @param  string   $date   La date de l'échéance.
     * @param  integer  $type   Le type de l'échéance.
     * @return Ecehance         L'échéance portant ce nom.
     */
    public function get(\DateTime $date, $type);

    /**
     * Retire l'élection du repository si elle existe.
     * @param Election  $element L'élection à retirer.
     */
    public function remove(Echeance $element);

    /**
     * Enregistrer les changements dans le repository.
     */
    public function save();
}
