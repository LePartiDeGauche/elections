<?php

namespace PartiDeGauche\ElectionsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EditionControllerTest extends WebTestCase
{
    public function testCircoeuropeenne()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/circo-europeenne/{code}/{nom}/edit/{echeance}/{echeanceNom}');
    }
}
