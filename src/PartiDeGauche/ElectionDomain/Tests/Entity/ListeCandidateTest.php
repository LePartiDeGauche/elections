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

namespace PartiDeGauche\ElectionDomain\Tests\Entity;

use PartiDeGauche\ElectionDomain\CandidatInterface;
use PartiDeGauche\ElectionDomain\Entity\Echeance;
use PartiDeGauche\ElectionDomain\CirconscriptionInterface;
use PartiDeGauche\ElectionDomain\Entity\Election;
use PartiDeGauche\ElectionDomain\Entity\ListeCandidate;

class ListeCandidateTest extends \PHPUnit_Framework_TestCase
{
    public function testHasNomAndElection()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $election = new Election($echeance, $circonscription);
        $listeCandidate = new ListeCandidate($election, 'Liste FdG');

        $this->assertEquals('Liste FdG', (string) $listeCandidate);
        $this->assertEquals($election, $listeCandidate->getElection());
    }

    public function testIsCandidat()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $election = new Election($echeance, $circonscription);
        $listeCandidate = new ListeCandidate($election, 'Liste FdG');

        $this->assertTrue($listeCandidate instanceof CandidatInterface);
    }

    public function testNomIsString()
    {
        $this->setExpectedException(
            '\InvalidArgumentException'
        );

        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $election = new Election($echeance, $circonscription);
        $listeCandidate = new ListeCandidate($election, 12);
    }
}
