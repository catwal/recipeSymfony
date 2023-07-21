<?php

namespace App\Tests\Functional\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ContactTest extends WebTestCase
{
    public function testCrudIsHere(): void
    {
        $client = static::createClient();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/admin');

        $this->assertResponseIsSuccessful();

        $crawler = $client->clickLink('Demandes de Contact');

        $this->assertResponseIsSuccessful();

        $client->click($crawler->filter('.action-new')->link());

        $this->assertResponseIsSuccessful();

        $client->click($crawler->filter('.action-edit')->link());

        $this->assertResponseIsSuccessful();


    }
}