<?php

namespace PartiDeGauche\ElectionsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testAccueil()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue(
            $crawler->filter('title:contains("Accueil")')->count() > 0
        );

        $this->assertTrue(
            $crawler->filter('.navbar:contains("Accueil")')->count() > 0
        );
    }
}
