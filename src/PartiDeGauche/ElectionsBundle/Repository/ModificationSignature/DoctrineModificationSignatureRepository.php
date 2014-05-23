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

namespace PartiDeGauche\ElectionsBundle\Repository\ModificationSignature;

use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;

class DoctrineModificationSignatureRepository
{
    /**
     * On garde les timestamp à persister pour éviter
     * les erreurs sur la contrainte d'unicité si invalidate()
     * est appelée plusieurs fois pour le même territoire entre
     * deux flush.
     * @var \SplObjectStorage
     */
    private $toPersist;

    public function __construct($doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->toPersist = new \SplObjectStorage();
    }

    public function getLastModificationSignature(
        AbstractTerritoire $territoire,
        Echeance $echeance
    ) {
        $signature = $this
            ->em
            ->getRepository(
                'PartiDeGauche\ElectionsBundle\Repository\ModificationSignature\TerritoireModificationSignature'
            )
            ->findOneBy(array('territoire' => $territoire, 'echeance' => $echeance));
        ;

        if ($signature) {
            return $signature->getSignature();
        }

        return new \DateTime('04/23/2014');
    }

    public function sign(
        AbstractTerritoire $territoire,
        Echeance $echeance,
        $signer
    ) {
        $signature = $this
            ->em
            ->getRepository(
                'PartiDeGauche\ElectionsBundle\Repository\ModificationSignature\TerritoireModificationSignature'
            )
            ->findOneBy(array('territoire' => $territoire, 'echeance' => $echeance));
        ;

        if (!$signature && $this->toPersist->offsetExists($territoire)) {
            $signature = $this->toPersist[$territoire];
        }

        if ($signature) {
            $signature->setSignature($signer);
        }

        if (!$signature) {
            $signature = new TerritoireModificationSignature($territoire, $echeance);
            $signature->setSignature($signer);
            $this->em->persist($signature);
            $this->toPersist[$territoire] = $signature;
        }
    }
}
