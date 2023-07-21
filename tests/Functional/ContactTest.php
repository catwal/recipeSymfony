<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class  ContactTest extends WebTestCase
{
    public function testIfContactFormIsSuccessfull(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Formulaire de contact');

        //Récupérer le formulaire
        $submitButton = $crawler->selectButton('Envoyer mon formulaire de contact');
        $form = $submitButton->form();

        $form['contact[fullName]'] = 'Jean Dupont';
        $form['contact[email]'] = 'jds@contact.fr';
        $form['contact[subject]'] = 'test';
        $form['contact[message]'] = 'test de message';

        //Soumettre le formulaire
        $client->submit($form);

        // Vérifier le statut HTTP
        //$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        //Vérifier l'envoie du mail
        //$this->assertEmailCount(1);

        //Vérifier la presence du message success
        /*$this->assertSelectorTextContains(
            'div.alert.alert-success',
            'votre demande a bien été envoyé'
        );*/
    }
}
