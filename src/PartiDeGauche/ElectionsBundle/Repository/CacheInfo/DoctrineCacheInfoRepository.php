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

namespace PartiDeGauche\ElectionsBundle\Repository\CacheInfo;

use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;

class DoctrineCacheInfoRepository
{
    public function __construct($doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    public function getLastModified(AbstractTerritoire $territoire)
    {
        $timestamp = $this
            ->em
            ->getRepository(
                'PartiDeGauche\ElectionsBundle\Repository\CacheInfo\TerritoireTimestamp'
            )
            ->findOneByTerritoire($territoire)
        ;

        if ($timestamp) {
            return $timestamp->getTimestamp();
        }

        return new \DateTime('04/15/2014');
    }

    public function invalidate(AbstractTerritoire $territoire)
    {
        $timestamp = $this
            ->em
            ->getRepository(
                'PartiDeGauche\ElectionsBundle\Repository\CacheInfo\TerritoireTimestamp'
            )
            ->findOneByTerritoire($territoire)
        ;

        if (!$timestamp) {
            $timestamp = new TerritoireTimestamp($territoire);
            $this->em->persist($timestamp);
        }

        if ($timestamp) {
            $timestamp->setNow();
        }
    }
}
