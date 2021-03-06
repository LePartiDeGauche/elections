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

namespace PartiDeGauche\TerritoireDomain\Tests\Entity\Territoire;

use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Pays;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Region;

class DepartementTest extends \PHPUnit_Framework_TestCase
{
    public function testCodeIsStringMax4()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $pays = new Pays('France');
        $region = new Region($pays, 11, 'Île-de-France');
        $departement = new Departement(
            $region,
            'ZEEEE',
            'Hauts-de-Seine'
        );
    }

    public function testHasRegionAndCodeAndNom()
    {
        $pays = new Pays('France');
        $region = new Region($pays, 11, 'Île-de-France');
        $departement = new Departement(
            $region,
            92,
            'Hauts-de-Seine'
        );

        $this->assertEquals(92, $departement->getCode());
        $this->assertEquals('Hauts-de-Seine', $departement->getNom());
        $this->assertEquals($region, $departement->getRegion());
        $this->assertContains($departement, $region->getDepartements());
    }

    public function testIsTerritoire()
    {
        $pays = new Pays('France');
        $region = new Region($pays, 11, 'Île-de-France');
        $departement = new Departement(
            $region,
            92,
            'Hauts-de-Seine'
        );

        $this->assertTrue($departement instanceof AbstractTerritoire);
    }

    public function testNomIsStringMax255()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $pays = new Pays('France');
        $region = new Region($pays, 11, 'Île-de-France');
        $departement = new Departement(
            $region,
            'ZEEE',
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            . 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            . 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            . 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            . 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            . 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            . 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
            . 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
        );
    }
}
