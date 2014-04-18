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

use PHPUnit_Framework_TestCase;
use PartiDeGauche\ElectionDomain\CirconscriptionInterface;
use PartiDeGauche\ElectionDomain\Entity\Election\ElectionRepositoryInterface;
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\Entity\Election\Election as BaseElection;

abstract class AbstractElectionRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testAddAndGetByEcheanceAndCirconscription()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $election = new ElectionMock($echeance, $circonscription);

        $this->repository->add($election);
        $this->assertNull(
            $this->repository->get($echeance, $circonscription)
        );

        $this->repository->save();

        $this->assertEquals(
            $election,
            $this->repository->get($echeance, $circonscription)
        );
    }

    public function testRemove()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $circonscription2 = new CirconscriptionMock();
        $election = new ElectionMock($echeance, $circonscription);
        $election2 = new ElectionMock($echeance, $circonscription2);
        $election3 = new ElectionMock($echeance, $circonscription);

        $this->repository->add($election);
        $this->repository->add($election2);
        $this->repository->save();

        $this->repository->remove($election);
        $this->repository->save();

        $this->assertNull(
            $this->repository->get($echeance, $circonscription)
        );
    }

    public function testRemoveAndAdd()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $circonscription2 = new CirconscriptionMock();
        $election = new ElectionMock($echeance, $circonscription);
        $election2 = new ElectionMock($echeance, $circonscription2);
        $election3 = new ElectionMock($echeance, $circonscription);

        $this->repository->add($election);
        $this->repository->add($election2);
        $this->repository->save();

        $this->repository->remove($election);
        $this->repository->add($election3);
        $this->repository->save();
    }

    public function testViolateUniqueCondition()
    {
        $echeance = new Echeance(new \DateTime, 'Nom de l\'échéance');
        $circonscription = new CirconscriptionMock();
        $election = new ElectionMock($echeance, $circonscription);
        $election2 = new ElectionMock($echeance, $circonscription);

        $this->repository->add($election);

        $this->repository->save();

        $this->assertEquals(
            $election,
            $this->repository->get($echeance, $circonscription)
        );

        $this->repository->add($election2);

        $this->setExpectedException(
            'PartiDeGauche\ElectionDomain\Entity\Election'
            . '\UniqueConstraintViolation'
        );
        $this->repository->save();
    }
}

class CirconscriptionMock implements CirconscriptionInterface
{
}

class ElectionMock extends BaseElection
{
}
