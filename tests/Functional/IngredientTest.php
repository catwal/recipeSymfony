<?php

namespace App\Tests\Functional;

use App\Entity\Ingredient;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IngredientTest extends WebTestCase
{
    public function testIfCreateIngredientIsSuccessfull(): void
    {
        $client = static::createClient();

        // Recup urlGenerator
        $urlGenerator = $client->getContainer()->get('router');

        //recup entity manager
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        //recup user
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        // se rendre sur page creation ingrédient
        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate('ingredient.new'));

        // gerer le formulaire

        $form = $crawler->filter('form[name=ingredient]')->form([
            'ingredient[name]'=>'un ingrédient',
            'ingredient[price]'=>floatval(33)
        ]);

        $client->submit($form);
        //gerer la redirection
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);


        $client->followRedirect();

        //gerer l'alerte et la route
        $this->assertSelectorTextContains(
            'div.alert-success',
            'Votre ingrédient à bien été créé !'
        );

        $this->assertRouteSame('ingredient');
    }

    public function testIfListIngredientIsSuccessfull()
    {
        $client = static::createClient();
        // Recup urlGenerator
        $urlGenerator = $client->getContainer()->get('router');

        //recup entity manager
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        //recup user
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $client->request(Request::METHOD_GET, $urlGenerator->generate('ingredient'));

        $this->assertResponseIsSuccessful();

        $this->assertRouteSame('ingredient');

    }


    public function testIfIngredientUpdatedIsSuccessfull(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');


        $user = $entityManager->find(User::class, 1);
        $ingredient = $entityManager->getRepository(Ingredient::class)->findOneBy([
            'user' => $user
        ]);

        $client->loginUser($user);

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('ingredient.edit', ['id'=> $ingredient->getId()])
        );

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter("form[name=ingredient]")->form([
            'ingredient[name]'=>'un ingrédient 2',
            'ingredient[price]'=>floatval(34)
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains(
            'div.alert-success',
            'Votre ingrédient à bien été modifié !'
        );

        $this->assertRouteSame('ingredient');
    }

    public function testIfDeleteAnIngredientIsSuccessfull(): void
    {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');


        $user = $entityManager->find(User::class, 1);
        $ingredient = $entityManager->getRepository(Ingredient::class)->findOneBy([
            'user' => $user
        ]);

        $client->loginUser($user);

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('ingredient.delete', ['id'=> $ingredient->getId()])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains(
            'div.alert-success',
            'Votre ingrédient à bien été supprimé !'
        );

        $this->assertRouteSame('ingredient');
    }

}
