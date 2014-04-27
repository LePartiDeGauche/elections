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

namespace PartiDeGauche\ElectionDomain\Tests\VO;

use PartiDeGauche\ElectionDomain\VO\Score;

class ScoreTest extends \PHPUnit_Framework_TestCase
{
    public function testFromPourcentage()
    {
        $score = Score::fromPourcentage(33.33);

        $this->assertEquals(33.33, $score->toPourcentage());
        $this->assertNull($score->toVoix());
    }

    public function testFromPourcentageAndExprimes()
    {
        $score = Score::fromPourcentageAndExprimes(33.33, 900);

        $this->assertEquals(33.33, $score->toPourcentage());
        $this->assertEquals(300, $score->toVoix());
    }

    public function testFromVoix()
    {
        $score = Score::fromVoix(500);

        $this->assertEquals(500, $score->toVoix());
        $this->assertNull($score->toPourcentage());
    }

    public function testFromVoixAndExprimes()
    {
        $score = Score::fromVoixAndExprimes(500, 1000);

        $this->assertEquals(500, $score->toVoix());
        $this->assertLessThanOrEqual(50.001, $score->toPourcentage());
        $this->assertGreaterThanOrEqual(49.999, $score->toPourcentage());
    }
}
