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

namespace PartiDeGauche\ElectionsBundle\Tests\Repository;

use PartiDeGauche\TerritoireDomain\Tests\Entity\Territoire
    \TerritoireRepositoryTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DoctrineTerritoireRepositoryTest extends WebTestCase
{
    use TerritoireRepositoryTestTrait;

    public function setUp()
    {
        $c = $this->container->get('doctrine.dbal.default_connection');
        $c->transactional(function ($c) {
            $sm = $c->getSchemaManager();
            $tables = $sm->listTables();

            foreach ($tables as $table) {
                $c->query('DELETE FROM ' . $table->getName());
            }
        });
    }

    public function __construct()
    {
        $client = static::createClient();
        $this->container = $client->getContainer();
        $this->repository = $this->container
            ->get('repository.territoire.doctrine');
    }
}
