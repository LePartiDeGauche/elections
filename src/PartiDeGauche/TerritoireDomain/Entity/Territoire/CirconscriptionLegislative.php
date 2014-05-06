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

class CirconscriptionLegislative extends AbstractTerritoire
{
    /**
     * Le numéro de la circonscription.
     * @var integer
     */
    private $code;

    /**
     * Le département de la circonscription.
     * @var Departement
     */
    private $departement;

    /**
     * Créer une nouvelle circonscription législative.
     * @param Departement $departement Le département de la circonscription.
     * @param integer     $code        Le numéro de la circonscrption.
     */
    public function __construct(Departement $departement, $code)
    {
        $this->code = (int) $code;
        $this->departement = $departement;
        $departement->addCirconscriptionLegislative($this);
    }

    /**
     * Récupérer le département de la circonscription.
     * @return Departement Le département de la circonscription.
     */
    public function getDepartement()
    {
        return $this->departement;
    }

    /**
     * Récupérer le code de la circonscription.
     * @return integer Le code de la circonscription.
     */
    public function getCode()
    {
        return $this->code;
    }

    public function getNom()
    {
        return 'Circonscription ' . $this->code . ' - ' . $this->departement;
    }
}
