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

use PartiDeGauche\TerritoireDomain\Entity\Territoire\TerritoireComposite;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;

class TerritoireCompositeTest extends \PHPUnit_Framework_TestCase
{
    public function testHasTwoTerritoire()
    {
        $territoire1 = new TerritoireMock();
        $territoire2 = new TerritoireMock();

        $territoire = new TerritoireComposite(
            TerritoireComposite::INTERSECTION,
            $territoire1,
            $territoire2
        );

        $this->assertContains($territoire1, $territoire->getTerritoires());
        $this->assertContains($territoire2, $territoire->getTerritoires());
    }

    public function testIsTerritoire()
    {
        $territoire1 = new TerritoireMock();
        $territoire2 = new TerritoireMock();

        $territoire = new TerritoireComposite(
            TerritoireComposite::INTERSECTION,
            $territoire1,
            $territoire2
        );

        $this->assertTrue($territoire instanceof AbstractTerritoire);
    }

    public function testUnionAndIntersection()
    {
        $territoire1 = new TerritoireMock();
        $territoire2 = new TerritoireMock();

        $territoireA = new TerritoireComposite(
            TerritoireComposite::UNION,
            $territoire1,
            $territoire2
        );

        $territoireB = new TerritoireComposite(
            TerritoireComposite::INTERSECTION,
            $territoire1,
            $territoire2
        );

        $this->assertNotEquals($territoireA, $territoireB);
    }
}
