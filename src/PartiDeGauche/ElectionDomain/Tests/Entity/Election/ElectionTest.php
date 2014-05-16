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

namespace PartiDeGauche\ElectionDomain\Tests\Entity\Election;

use PartiDeGauche\ElectionDomain\Entity\Candidat\ListeCandidate;
use PartiDeGauche\ElectionDomain\Entity\Candidat\PersonneCandidate;
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\VO\VoteInfo;
use PartiDeGauche\TerritoireDomain\Tests\Entity\Territoire\TerritoireMock;

class ElectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCanHaveCandidat()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);

        $candidat = new PersonneCandidate($election, 'FG', 'Naël', 'Ferret');
        $election->addCandidat($candidat);

        $this->assertContains($candidat, $election->getCandidats());
    }

    public function testHasEcheanceAndCirconscription()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);

        $this->assertEquals($echeance, $election->getEcheance());
        $this->assertEquals($circonscription, $election->getCirconscription());
    }

    public function testPourcentageByCandidatAndTerritoire()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $territoire = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $candidat = new PersonneCandidate($election, 'FG', 'Naël', 'Ferret');

        $election->addCandidat($candidat);
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

    public function testSiegesEuropeennes()
    {
        $echeance = new Echeance(new \DateTime, Echeance::EUROPEENNES);
        $circonscription = new TerritoireMock();
        $territoire = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $candidat1 = new ListeCandidate($election, 'FG', 'L\'humain d\'abord');
        $candidat2 = new ListeCandidate($election, 'PS', 'Solfériniens');
        $candidat3 = new ListeCandidate($election, 'UMP', 'Droite');
        $candidat4 = new ListeCandidate($election, 'ECO', 'Les amis');

        $election->addCandidat($candidat1);
        $election->addCandidat($candidat2);
        $election->addCandidat($candidat3);
        $election->addCandidat($candidat4);

        $election->setVoteInfo(new VoteInfo(12000, 11000, 10000));
        $election->setVoixCandidat(5000, $candidat1);
        $election->setVoixCandidat(1600, $candidat2);
        $election->setVoixCandidat(400, $candidat3);
        $election->setVoixCandidat(3000, $candidat4);

        $election->setSieges(30);

        $this->assertEquals(
            16,
            $election->getSiegesCandidat($candidat1)
        );
        $this->assertEquals(
            5,
            $election->getSiegesCandidat($candidat2)
        );
        $this->assertEquals(
            0,
            $election->getSiegesCandidat($candidat3)
        );
        $this->assertEquals(
            9,
            $election->getSiegesCandidat($candidat4)
        );
    }

    public function testVoixByCandidatAndTerritoire()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $territoire = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);
        $candidat = new PersonneCandidate($election, 'FG', 'Naël', 'Ferret');

        $election->addCandidat($candidat);
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
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);

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
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);

        $voteInfo = new VoteInfo(1000, 500, 499);
        $election->setVoteInfo($voteInfo);
        $this->assertEquals($voteInfo, $election->getVoteInfo());
    }

    public function testVoteInfoDefaultParamIsCirconscription()
    {
        $echeance = new Echeance(new \DateTime, Echeance::CANTONALES);
        $circonscription = new TerritoireMock();
        $election = new ElectionMock($echeance, $circonscription);

        $voteInfo = new VoteInfo(1000, 500, 499);
        $election->setVoteInfo($voteInfo);

        $this->assertEquals($voteInfo, $election->getVoteInfo());

        $actual = $election->getVoteInfo($circonscription);
        $this->assertEquals($voteInfo, $actual);
    }
}
