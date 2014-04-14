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

use PartiDeGauche\ElectionDomain\CirconscriptionInterface;
use PartiDeGauche\ElectionDomain\Entity\Echeance;
use PartiDeGauche\ElectionDomain\Entity\Election;
use PartiDeGauche\ElectionDomain\Entity\PersonneCandidate;
use PartiDeGauche\ElectionDomain\TerritoireInterface;
use PartiDeGauche\ElectionDomain\VO\VoteInfo;

class ElectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCanHaveCandidat()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $election = new Election($echeance, $circonscription);

        $candidat = new PersonneCandidate('Naël', 'Ferret');
        $election->addCandidat($candidat);

        $this->assertContains($candidat, $election->getCandidats());
    }

    public function testHasEcheanceAndCirconscription()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $election = new Election($echeance, $circonscription);

        $this->assertEquals($echeance, $election->getEcheance());
        $this->assertEquals($circonscription, $election->getCirconscription());
    }

    public function testPourcentageByCandidatAndTerritoire()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $territoire = new TerritoireMock();
        $candidat = new PersonneCandidate('Naël', 'Ferret');
        $election = new Election($echeance, $circonscription);

        $election->setPourcentageCandidat(33.33, $candidat);
        $score = $election->getScoreCandidat($candidat);
        $this->assertEquals(33.33, $score->toPourcentage());
        $this->assertNull($score->toVoix());

        $election->setVoteInfo(new VoteInfo(4000, 3000, 3000));
        $election->setPourcentageCandidat(33.33, $candidat);

        $score = $election->getScoreCandidat($candidat);
        $this->assertEquals(33.33, $score->toPourcentage());
        $this->assertEquals(1000, $score->toVoix());

        $election->setPourcentageCandidat(66.66, $candidat, $territoire);
        $score = $election->getScoreCandidat($candidat);
        $this->assertEquals(33.33, $score->toPourcentage());
        $this->assertEquals(1000, $score->toVoix());
        $scoreTerritoire = $election->getScoreCandidat($candidat, $territoire);
        $this->assertEquals(66.66, $scoreTerritoire->toPourcentage());
        $this->assertNull($scoreTerritoire->toVoix());
    }

    public function testVoixByCandidatAndTerritoire()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $territoire = new TerritoireMock();
        $candidat = new PersonneCandidate('Naël', 'Ferret');
        $election = new Election($echeance, $circonscription);

        $election->setVoixCandidat(1000, $candidat);
        $score = $election->getScoreCandidat($candidat);
        $this->assertEquals(1000, $score->toVoix());
        $this->assertNull($score->toPourcentage());

        $election->setVoteInfo(new VoteInfo(4000, 3000, 2000));
        $election->setVoixCandidat(1000, $candidat);

        $score = $election->getScoreCandidat($candidat);
        $this->assertEquals(1000, $score->toVoix());
        $this->assertLessThan(50.01, $score->toPourcentage());
        $this->assertGreaterThan(49.09, $score->toPourcentage());

        $election->setVoixCandidat(2000, $candidat, $territoire);
        $score = $election->getScoreCandidat($candidat);
        $this->assertEquals(1000, $score->toVoix());
        $scoreTerritoire = $election->getScoreCandidat($candidat, $territoire);
        $this->assertEquals(2000, $scoreTerritoire->toVoix());
        $this->assertNull($scoreTerritoire->toPourcentage());
    }

    public function testVoteInfoByTerritoire()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $election = new Election($echeance, $circonscription);

        $voteInfo1 = new VoteInfo(1000, 500, 499);
        $voteInfo2 = new VoteInfo(500, 500, 499);
        $territoire1 = new TerritoireMock();
        $territoire2 = new TerritoireMock();
        $election->setVoteInfo($voteInfo1, $territoire1);
        $election->setVoteInfo($voteInfo2, $territoire2);

        $this->assertEquals($voteInfo1, $election->getVoteInfo($territoire1));
        $this->assertEquals($voteInfo2, $election->getVoteInfo($territoire2));
    }

    public function testVoteInfoDefault()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $election = new Election($echeance, $circonscription);

        $voteInfo = new VoteInfo(1000, 500, 499);
        $election->setVoteInfo($voteInfo);
        $this->assertEquals($voteInfo, $election->getVoteInfo());
    }

    public function testVoteInfoDefaultParamIsCirconscription()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $election = new Election($echeance, $circonscription);

        $voteInfo = new VoteInfo(1000, 500, 499);
        $election->setVoteInfo($voteInfo);

        $this->assertEquals($voteInfo, $election->getVoteInfo());

        $actual = $election->getVoteInfo($circonscription);
        $this->assertEquals($voteInfo, $actual);
    }
}

class TerritoireMock implements TerritoireInterface
{
}
