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

namespace PartiDeGauche\ElectionsBundle\Repository;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException as DoctrineException;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\Pays;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\TerritoireRepositoryInterface;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\UniqueConstraintViolationException;

class DoctrineTerritoireRepository implements TerritoireRepositoryInterface
{
    public function __construct($doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
    }

    public function add(AbstractTerritoire $element)
    {
        $this->em->persist($element);
    }

    public function getArrondissementCommunal($commune, $codeArrondissement)
    {
        return $this
            ->em
            ->getRepository(
                '\PartiDeGauche\TerritoireDomain\Entity' .
                '\Territoire\ArrondissementCommunal'
            )
            ->findOneBy(array(
                'commune' => $commune,
                'code' => $codeArrondissement,
            ));
    }

    public function getCirconscriptionEuropeenne($nom)
    {
        return $this
            ->em
            ->getRepository(
                '\PartiDeGauche\TerritoireDomain\Entity' .
                '\Territoire\CirconscriptionEuropeenne'
            )
            ->findOneByNom($nom);
    }

    public function getCirconscriptionLegislative($codeDepartement, $code)
    {
        $query = $this
            ->em
            ->createQuery(
                'SELECT circo
                FROM \PartiDeGauche\TerritoireDomain\Entity\Territoire\CirconscriptionLegislative
                circo
                JOIN circo.departement departement
                WHERE departement.code = :codeDepartement
                AND circo.code = :code'
            )
            ->setParameter('codeDepartement', $codeDepartement)
            ->setParameter('code', $code)
        ;

        return $query->getOneOrNullResult();
    }

    public function getCommune($codeDepartement, $codeCommune)
    {
        $query = $this
            ->em
            ->createQuery(
                'SELECT commune
                FROM \PartiDeGauche\TerritoireDomain\Entity\Territoire\Commune
                commune
                JOIN commune.departement departement
                WHERE departement.code = :codeDepartement
                AND commune.code = :codeCommune'
            )
            ->setParameter('codeDepartement', $codeDepartement)
            ->setParameter('codeCommune', $codeCommune)
        ;

        return $query->getOneOrNullResult();
    }

    public function getDepartement($code)
    {
        return $this
            ->em
            ->getRepository(
                '\PartiDeGauche\TerritoireDomain\Entity\Territoire'
                . '\Departement'
            )
            ->findOneByCode($code)
        ;
    }

    public function getPays()
    {
        $pays = $this
            ->em
            ->getRepository(
                '\PartiDeGauche\TerritoireDomain\Entity\Territoire'
                . '\Pays'
            )
            ->findOneByNom('France')
        ;

        if (!$pays) {
            $entities = $this->em->getUnitOfWork()->getScheduledEntityInsertions();

            foreach ($entities as $entity) {
                if ($entity instanceof Pays) {
                    $pays = $entity;
                    break;
                }
            }
        }

        if (!$pays) {
            $pays = new Pays();
            $this->add($pays);
        }

        return $pays;

    }

    public function getRegion($code)
    {
        return $this
            ->em
            ->getRepository(
                '\PartiDeGauche\TerritoireDomain\Entity\Territoire'
                . '\Region'
            )
            ->findOneByCode($code)
        ;
    }

    /**
     * Retire l'élection du repository si elle existe.
     * @param Election $element L'élection à retirer.
     */
    public function remove(AbstractTerritoire $element)
    {
        $this->em->remove($element);
    }

    /**
     * Enregistrer les changements dans le repository.
     */
    public function save()
    {
        try {
            $this->checkUniqueRules();
            $this->em->flush();
        } catch (DoctrineException $exception) {
            throw new UniqueConstraintViolationException(
                $exception->getMessage()
            );
        } catch (\Doctrine\DBAL\Exception\DriverException $exception) {
            throw new UniqueConstraintViolationException(
                $exception->getMessage()
            );
        }
    }

    private function checkUniqueRules()
    {
        $entities = $this->em->getUnitOfWork()->getScheduledEntityInsertions();

        foreach ($entities as $entity) {
            $repo = $this->em->getRepository(get_class($entity));
            switch (get_class($entity)) {
                case 'PartiDeGauche\TerritoireDomain\Entity' .
                '\Territoire\Commune':
                    $exist = $this->getCommune(
                        $entity->getDepartement()->getCode(),
                        $entity->getCode()
                    );
                    break;
                case 'PartiDeGauche\TerritoireDomain\Entity' .
                '\Territoire\CirconscriptionLegislative':
                    $exist = $this->getCirconscriptionLegislative(
                        $entity->getDepartement()->getCode(),
                        $entity->getCode()
                    );
                    break;
                case 'PartiDeGauche\TerritoireDomain\Entity' .
                '\Territoire\ArrondissementCommunal':
                    $exist = $this->getArrondissementCommunal(
                        $entity->getCommune(),
                        $entity->getCode()
                    );
                    break;
                case 'PartiDeGauche\TerritoireDomain\Entity' .
                '\Territoire\CirconscriptionEuropeenne':
                    $exist = $this->getCirconscriptionEuropeenne(
                        $entity->getNom()
                    );
                    break;
                case 'PartiDeGauche\TerritoireDomain\Entity' .
                '\Territoire\Pays':
                    $exist = $repo->findOneByNom('France');
                    break;
                default:
                    $exist = $repo->findOneByCode($entity->getCode());
            }

            if (null !== $exist) {
                throw new UniqueConstraintViolationException(
                    'Les communes doivent être unique par code et département' .
                    ', et les départements et régions doivent être uniques ' .
                    'par code. Il existe déjà un territoire ' .
                    $exist->getNom() . ', impossible de le remplacer par ' .
                    $entity->getNom()
                );
            }
        }

        return true;
    }
}
