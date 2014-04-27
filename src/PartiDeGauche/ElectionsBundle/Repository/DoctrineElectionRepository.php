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
use PartiDeGauche\ElectionDomain\CirconscriptionInterface;
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\Entity\Election\Election;
use PartiDeGauche\ElectionDomain\Entity\Election\ElectionRepositoryInterface;
use PartiDeGauche\ElectionDomain\Entity\Election
    \UniqueConstraintViolationException;

class DoctrineElectionRepository implements ElectionRepositoryInterface
{
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function add(Election $element)
    {
        $this
            ->em
            ->persist($element);
    }

    public function get(Echeance $echeance,
        CirconscriptionInterface $circonscription)
    {
        return $this
            ->em
            ->getRepository(
                'PartiDeGauche\ElectionDomain\Entity\Election\Election'
            )
            ->findOneBy(array(
                'echeance' => $echeance,
                'circonscription' => $circonscription
            ))
        ;
    }

    public function remove(Election $element)
    {
        $this->em->remove($element);
    }

    public function save()
    {
        try {
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
}
