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

use Doctrine\DBAL\Exception\UniqueConstraintViolationException
    as DoctrineException;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;
use PartiDeGauche\TerritoireDomain\Entity\Territoire
    \TerritoireRepositoryInterface;
use PartiDeGauche\TerritoireDomain\Entity\Territoire
    \UniqueConstraintViolationException;

class DoctrineTerritoireRepository implements TerritoireRepositoryInterface
{
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function add(AbstractTerritoire $element)
    {
        $this->em->persist($element);
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
     * @param Election  $element L'élection à retirer.
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
            $this->em->flush();
            $this->em->clear();
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
}
