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

class UniqueConstraintViolationException extends \Exception { }

/**
 * Voir PartiDeGauche\TerritoireDomain\Tests\Entity\Territoire
 * \TerritoireRepositoryTestTrait pour les contraintes à respecter lors de
 * l'implémentation de cette interface.
 */
interface TerritoireRepositoryInterface
{
    /**
     * Ajoute un territoire au repository.
     * @param AbstractTerritoire $territoire Le territoire à ajouter au repo.
     */
    public function add(AbstractTerritoire $territoire);

    /**
     * Récupérer un arrondissement communal en fonction de sa commune et de son
     * code.
     * @param  Commune                $commune            La commune de l'arrondissement.
     * @param  string                 $codeArrondissement Le code de l'arrondissement
     * @return ArrondissementCommunal L'arrondissement.
     */
    public function getArrondissementCommunal($commune, $codeArrondissement);

    /**
     * Récupérer une commune en fonction de son code département et son code
     * commune (INSEE).
     * @param  integer $codeDepartement Le code du département.
     * @param  integer $codeCommune     Le code INSEE de la commune.
     * @return Commune La commune avec ces attributs.
     */
    public function getCommune($codeDepartement, $codeCommune);

    /**
     * Récupérer un département en fonction de son code.
     * @param  integer     $code Le code du département.
     * @return Departement Le département avec ce code.
     */
    public function getDepartement($code);

    /**
     * Récupérer une région en fonction de son code.
     * @param  integer $code Le code de la région.
     * @return Region  La région avec ce département.
     */
    public function getRegion($code);

    /**
     * Retirer un territoire donné du epository.
     * @param AbstractTerritoire $territoire Le territoire à retirer.
     */
    public function remove(AbstractTerritoire $territoire);

    /**
     * Sauvegarder les changements dans le repository.
     */
    public function save();
}
