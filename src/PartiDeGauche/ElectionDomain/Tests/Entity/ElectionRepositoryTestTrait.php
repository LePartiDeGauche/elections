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

use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\Entity\Echeance\EcheanceRepositoryInterface;
use PartiDeGauche\ElectionDomain\Entity\Election\ElectionRepositoryInterface;
use PartiDeGauche\ElectionDomain\Entity\Election\ElectionUninominale;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Region;

/**
 * Le repository doit être vidé au moyen d'une fonction setUp avant chaque
 * méthode de test.
 */
trait ElectionRepositoryTestTrait
{
    /**
     * Le repository echeance que l'on teste. Doit être configuré par la classe
     * de test utilisant le trait.
     * @var EcheanceRepositoryInterface
     */
    protected $echeanceRepository;

    /**
     * Le repository election que l'on teste. Doit être configuré par la classe
     * de test utilisant le trait.
     * @var ElectionRepositoryInterface
     */
    protected $electionRepository;

    public function testAddAndGet()
    {
        $date = new \DateTime();
        $echeance = new Echeance($date, Echeance::CANTONALES);
        $circonscription = new Region(11, 'Île-de-France');
        $election = new ElectionUninominale($echeance, $circonscription);

        $this->electionRepository->add($election);
        // On ne doit rien trouver dans le repository tant que l'on a pas appelé
        // save()
        $this->assertNull(
            $this->electionRepository->get($echeance, $circonscription)
        );

        $this->electionRepository->save();

        $this->assertEquals(
            $election,
            $this->electionRepository->get($echeance, $circonscription)
        );

        // L'échéance doit être automatiquement enregistrée dans le repository
        // échéance
        $this->assertEquals(
            $echeance,
            $this->echeanceRepository->get($date, Echeance::CANTONALES)
        );

        // La circonscription doit être automatiquement enregistrée et
        // accessible par getCirconscription()
        $this->assertEquals(
            $circonscription,
            $this->electionRepository->get($echeance, $circonscription)
                ->getCirconscription()
        );
    }

    public function testRemove()
    {
        $date = new \DateTime();
        $echeance = new Echeance($date, Echeance::CANTONALES);
        $circonscription = new Region(11, 'Île-de-France');
        $circonscription2 = new Region(38, 'Jesaisplus');
        $election = new ElectionUninominale($echeance, $circonscription);
        $election2 = new ElectionUninominale($echeance, $circonscription2);
        $election3 = new ElectionUninominale($echeance, $circonscription);

        $this->electionRepository->add($election);
        $this->electionRepository->add($election2);
        $this->electionRepository->save();

        $this->electionRepository->remove($election);
        $this->electionRepository->save();

        $this->assertNull(
            $this->electionRepository->get($echeance, $circonscription)
        );

        $this->electionRepository->remove($election2);
        $this->electionRepository->remove($election3);
        $this->echeanceRepository->remove($echeance);
        $this->electionRepository->save();
        $this->echeanceRepository->save();

        $this->assertNull(
            $this->echeanceRepository->get($date, Echeance::CANTONALES)
        );
    }

    // Il ne peut y avoir qu'une élection par échéance et par circonscription.
    public function testViolateUniqueCondition()
    {
        $date = new \DateTime();
        $echeance = new Echeance($date, Echeance::CANTONALES);
        $echeance2 = new Echeance($date, Echeance::CANTONALES);
        $circonscription = new Region(11, 'Île-de-France');
        $election = new ElectionUninominale($echeance, $circonscription);
        $election2 = new ElectionUninominale($echeance, $circonscription);

        $this->electionRepository->add($election);

        $this->electionRepository->save();

        $this->assertEquals(
            $election,
            $this->electionRepository->get($echeance, $circonscription)
        );

        $this->electionRepository->add($election2);

        $this->setExpectedException(
            'PartiDeGauche\ElectionDomain\Entity\Election'
            . '\UniqueConstraintViolationException'
        );
        $this->electionRepository->save();
        $this->electionRepository->remove($election2);

        $this->echeanceRepository->add($echeance2);

        $this->setExpectedException(
            'PartiDeGauche\ElectionDomain\Entity\Echeance'
            . '\UniqueConstraintViolationException'
        );
        $this->echeanceRepository->save();
    }
}
