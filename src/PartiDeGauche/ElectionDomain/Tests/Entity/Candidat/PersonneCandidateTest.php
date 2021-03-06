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

namespace PartiDeGauche\ElectionDomain\Tests\Entity\Candidat;

use PartiDeGauche\ElectionDomain\CandidatInterface;
use PartiDeGauche\ElectionDomain\Entity\Candidat\PersonneCandidate;
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\Tests\Entity\Election\ElectionMock;
use PartiDeGauche\TerritoireDomain\Tests\Entity\Territoire\TerritoireMock;

class PersonneCandidateTest extends \PHPUnit_Framework_TestCase
{
    public function testHasNomAndPrenomAndElection()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $personneCandidate = new PersonneCandidate(
            $election,
            'FG',
            'Naël',
            'Ferret'
        );

        $this->assertEquals('FG', $personneCandidate->getNuance());
        $this->assertEquals('Naël Ferret', (string) $personneCandidate);
        $this->assertEquals($election, $personneCandidate->getElection());
    }

    public function testIsCandidat()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $personneCandidate = new PersonneCandidate(
            $election,
            'FG',
            'Naël',
            'Ferret'
        );

        $this->assertTrue($personneCandidate instanceof CandidatInterface);
    }

    public function testNomIsString()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $this->setExpectedException(
            '\InvalidArgumentException'
        );

        $personneCandidate = new PersonneCandidate($election,'FG', 'Naël', 42);
    }

    public function testPrenomIsString()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $this->setExpectedException(
            '\InvalidArgumentException'
        );

        $personneCandidate = new PersonneCandidate(
            $election,
            'FG',
            42,
            'Ferret'
        );
    }
}
