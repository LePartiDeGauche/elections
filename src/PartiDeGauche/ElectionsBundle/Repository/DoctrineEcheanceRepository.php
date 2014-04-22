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

use PartiDeGauche\ElectionDomain\CirconscriptionInterface;
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\Entity\Echeance\EcheanceRepositoryInterface;
use PartiDeGauche\ElectionDomain\Entity\Echeance\UniqueConstraintViolationException;

class DoctrineEcheanceRepository implements EcheanceRepositoryInterface
{
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function add(Echeance $element)
    {
        $this->em->persist($element);
    }

    public function get($nom)
    {
        return $this
            ->em
            ->getRepository(
                '\PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance'
            )
            ->findOneByNom($nom)
        ;
    }

    /**
     * Retire l'élection du repository si elle existe.
     * @param Election  $element L'élection à retirer.
     */
    public function remove(Echeance $element)
    {
        $this->em->remove($element);
    }

    /**
     * Enregistrer les changements dans le repository.
     */
    public function save()
    {
        $this->em->flush();
    }
}
