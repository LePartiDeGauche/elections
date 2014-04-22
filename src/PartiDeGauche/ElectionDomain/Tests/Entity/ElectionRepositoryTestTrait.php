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
     * Pour ne pas introduire de dépendance avec PartiDeGauche\TerritoireDomain
     * la class de test utilisant ce trait doit fournir elle-même les objets
     * utilisés comme paramêtre CirconscriptionInterface des élections testées.
     * @var CirconscriptionInterface
     */
    protected $circonscription1;

    /**
     * Pour ne pas introduire de dépendance avec PartiDeGauche\TerritoireDomain
     * la class de test utilisant ce trait doit fournir elle-même les objets
     * utilisés comme paramêtre CirconscriptionInterface des élections testées.
     * @var CirconscriptionInterface
     */
    protected $circonscription2;

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
        $echeance = new Echeance(new \DateTime, $this->getEcheanceNom());
        $circonscription = $this->circonscription1;
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
            $this->echeanceRepository->get($this->getEcheanceNom())
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
        $echeance = new Echeance(new \DateTime, $this->getEcheanceNom());
        $circonscription = $this->circonscription1;
        $circonscription2 = $this->circonscription2;
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
            $this->echeanceRepository->get($this->getEcheanceNom())
        );
    }

    // Il ne peut y avoir qu'une élection par échéance et par circonscription.
    public function testViolateUniqueCondition()
    {
        $echeance = new Echeance(new \DateTime, $this->getEcheanceNom());
        $echeance2 = new Echeance(new \DateTime, $this->getEcheanceNom());
        $circonscription = $this->circonscription1;
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

    // générer un nom d'échéance aléatoire
    private function getEcheanceNom()
    {
        if (!isset($this->echeanceNom)) {
            $this->echeanceNom = 'test' . uniqid();
        }

        return $this->echeanceNom;
    }
}
