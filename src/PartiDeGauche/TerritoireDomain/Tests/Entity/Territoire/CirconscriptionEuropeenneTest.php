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

use PartiDeGauche\TerritoireDomain\Entity\Territoire\CirconscriptionEuropeenne;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Pays;

class CirconscriptionEuropeenneTest extends \PHPUnit_Framework_TestCase
{

    public function testPays()
    {
        $pays = new Pays('France');
        $circo = new CirconscriptionEuropeenne($pays, 1, 'Nom');

        $this->assertEquals($pays, $circo->getPays());
        $this->assertContains($circo, $pays->getCirconscriptionsEuropeennes());
    }

    public function testHasNom()
    {
        $pays = new Pays('France');
        $circo = new CirconscriptionEuropeenne($pays, 1, 'Nom');

        $this->assertEquals('Nom', $circo->getNom());
        $this->assertEquals(1, $circo->getCode());
        $this->assertEquals($pays, $circo->getPays());
    }
}
