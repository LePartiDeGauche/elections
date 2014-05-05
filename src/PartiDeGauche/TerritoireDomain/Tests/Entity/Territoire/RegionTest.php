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
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Region;

class RegionTest extends \PHPUnit_Framework_TestCase
{
    public function testCodeIsStringMax4()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $region = new Region('ZEEEE', 'Île-de-France');
    }

    public function testHasCodeAndNom()
    {
        $region = new Region(11, 'Île-de-France');

        $this->assertEquals(11, $region->getCode());
        $this->assertEquals('Île-de-France', $region->getNom());
    }

    public function testIsTerritoire()
    {
        $region = new Region(11, 'Île-de-France');

        $this->assertTrue($region instanceof AbstractTerritoire);
    }

    public function testNomIsStringMax255()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $region = new Region(
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
