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

namespace PartiDeGauche\ElectionDomain\Tests\Entity\Echeance;

use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;

class EcheanceTest extends \PHPUnit_Framework_TestCase
{
    public function testHasDateAndTypeAndTour()
    {
        $date = new \DateTime();
        $echeance = new Echeance($date, Echeance::CANTONALES, true);

        $this->assertEquals($date, $echeance->getDate());
        $this->assertEquals(
            'Cantonales ' . $date->format('Y') . ' (second tour)',
            $echeance->getNom()
        );
        $this->assertEquals(
            'Cantonales ' . $date->format('Y') . ' (second tour)',
            $echeance->__toString()
        );
        $this->assertTrue($echeance->isSecondTour());
    }

    public function testNomIsString()
    {
        $this->setExpectedException(
            '\InvalidArgumentException'
        );

        $date = new \DateTime();
        $echeance = new Echeance($date, 12);
    }
}
