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
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\Entity\Candidat\ListeCandidate;
use PartiDeGauche\TerritoireDomain\Tests\Entity\Territoire\TerritoireMock;
use PartiDeGauche\ElectionDomain\Tests\Entity\Election\ElectionMock;

class ListeCandidateTest extends \PHPUnit_Framework_TestCase
{
    public function testHasNomAndElection()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $listeCandidate = new ListeCandidate($election, 'Liste FdG');

        $this->assertEquals('Liste FdG', (string) $listeCandidate);
        $this->assertEquals($election, $listeCandidate->getElection());
    }

    public function testIsCandidat()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $listeCandidate = new ListeCandidate($election, 'Liste FdG');

        $this->assertTrue($listeCandidate instanceof CandidatInterface);
    }

    public function testNomIsString()
    {
        $this->setExpectedException(
            '\InvalidArgumentException'
        );

        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $listeCandidate = new ListeCandidate($election, 12);
    }
}
