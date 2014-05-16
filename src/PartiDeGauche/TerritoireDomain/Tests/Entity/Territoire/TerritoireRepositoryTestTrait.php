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

namespace PartiDeGauche\TerritoireDomain\Tests\Entity\Territoire;

use PartiDeGauche\TerritoireDomain\Entity\Territoire\ArrondissementCommunal;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\CirconscriptionEuropeenne;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\CirconscriptionLegislative;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Departement;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Region;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\TerritoireRepositoryInterface;

/**
 * Le repository doit être vidé avant chaque test, avec une méthode setUp()
 */
trait TerritoireRepositoryTestTrait
{
    /**
     * Le repository que l'on teste. Doit être configuré par le test qui
     * utilise le Trait.
     * @var TerritoireRepositoryInterface
     */
    private $repository;

    public function testAddGetRemove()
    {
        $pays = $this->repository->getPays();
        $region = new Region($pays, 82, 'Rhône-Alpes');
        $departement = new Departement($region, 38, 'Isère');
        $commune = new Commune($departement, 'ZE', 'Grenoble');
        $arrondissementCommunal = new ArrondissementCommunal(
            $commune,
            'ZE',
            'Test'
        );
        $circonscriptionLeg = new CirconscriptionLegislative($departement, 2);
        $circonscriptionEur = new CirconscriptionEuropeenne(
            $pays,
            1,
            'Sud-Ouest'
        );

        $this->repository->add($arrondissementCommunal);
        $this->repository->add($circonscriptionLeg);
        $this->repository->add($circonscriptionEur);
        $this->repository->save();

        // L'id peut avoir changer donc on teste juste le nom.
        $this->assertEquals(
            $region->getNom(),
            $this->repository->getRegion(82)->getNom()
        );
        $this->assertEquals(
            $departement->getNom(),
            $this->repository->getDepartement(38)->getNom()
        );
        $this->assertEquals(
            $commune->getNom(),
            $this->repository->getCommune(38, 'ZE')->getNom()
        );
        $this->assertEquals(
            $arrondissementCommunal->getNom(),
            $this->repository->getArrondissementCommunal($commune, 'ZE')
                ->getNom()
        );
        $this->assertEquals(
            $circonscriptionLeg->getCode(),
            $this->repository->getCirconscriptionLegislative(38, 2)
                ->getCode()
        );
        $this->assertEquals(
            $circonscriptionEur->getNom(),
            $this->repository->getCirconscriptionEuropeenne(
                'Sud-Ouest'
            )->getNom()
        );
        $this->assertEquals(
            $circonscriptionEur->getNom(),
            $this->repository->getCirconscriptionEuropeenne(
                1
            )->getNom()
        );
        $this->assertEquals(
            'France',
            $this->repository->getPays()->getNom()
        );

        // On teste remove
        $this->repository->remove($this->repository->getRegion(82));
        $this->repository->save();
        // Si on utilise assertNull, en cas d'échec du test, phpUnit fait un
        // var_dump ce qui pose problème lorsqu'il y a des associations
        // d'entités cycliques.
        $this->assertTrue(
            null == $this->repository->getRegion(82)
        );
        $this->assertTrue(
            null == $this->repository->getDepartement(38)
        );
        $this->assertTrue(
            null == $this->repository->getCommune(38, 'ZE')
        );
        $this->assertTrue(
            null == $this->repository->getArrondissementCommunal($commune, 'ZE')
        );
    }

    public function testDoNotViolateUniqueConstraintIfTypeDifferent()
    {
        $pays = $this->repository->getPays();
        $region = new Region($pays, 38, 'Nimportequoi');
        $departement = new Departement($region, 38, 'Isère');
        $commune = new Commune($departement, 'ZE', 'Grenoble');
        $arrondissementCommunal = new ArrondissementCommunal(
            $commune,
            'ZE',
            'Test'
        );

        $this->repository->add($arrondissementCommunal);
        $this->repository->save();

        $departement2 = new Departement(
            $this->repository->getRegion(38),
            22,
            'Nimp'
        );
        $commune2 = new Commune($departement2, 'ZE', 'Grenoble');
        $this->repository->add($commune2);
        $this->repository->save();

        $arrondissementCommunal2 = new ArrondissementCommunal(
            $this->repository->getCommune(22, 'ZE'),
            'ZE',
            'Test'
        );
        $this->repository->add($arrondissementCommunal2);
        $this->repository->save();
    }

    // Les codes des régions, des départements et des communes doivent être
    // uniques.
    public function testViolateUniqueCondition()
    {
        $pays = $this->repository->getPays();
        $region = new Region($pays, 82, 'Rhône-Alpes');
        $departement = new Departement($region, 38, 'Isère');
        $commune = new Commune($departement, 'ZE', 'Grenoble');
        $arrondissementCommunal = new ArrondissementCommunal(
            $commune,
            'ZE',
            'Test'
        );

        // On ajoute les 3 territoires dans le repository.
        $this->repository->add($arrondissementCommunal);
        $this->repository->save();

        // On test les contraintes d'unicité une à une.
        $this->repository->add(new Region($pays, 82, 'Rhône-Alpes'));
        $this->setExpectedException(
            'PartiDeGauche\TerritoireDomain\Entity\Territoire'
            . '\UniqueConstraintViolationException'
        );
        $this->repository->save();

        $this->repository->add(new Departement($region, 38, 'Isère'));
        $this->setExpectedException(
            'PartiDeGauche\TerritoireDomain\Entity\Territoire'
            . '\UniqueConstraintViolationException'
        );
        $this->repository->save();

        $this->repository->add(new Commune($departement, 'ZE', 'Grenoble'));
        $this->setExpectedException(
            'PartiDeGauche\TerritoireDomain\Entity\Territoire'
            . '\UniqueConstraintViolationException'
        );
        $this->repository->save();

        $this->repository->add(new ArrondissementCommunal(
            $commune,
            'ZE',
            'Test'
        ));
        $this->setExpectedException(
            'PartiDeGauche\TerritoireDomain\Entity\Territoire'
            . '\UniqueConstraintViolationException'
        );
        $this->repository->save();
    }
}
