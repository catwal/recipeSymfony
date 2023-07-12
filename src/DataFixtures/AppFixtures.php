<?php

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\Mark;
use App\Entity\Recipe;
use App\Entity\User;
use Faker\Factory;
use Faker\Generator;
use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
     private Generator $faker;

     private UserPasswordHasherInterface $hasher;

     public function __construct(UserPasswordHasherInterface $hasher)
     {
         $this->faker = Factory::create('fr_FR');
         $this->hasher = $hasher;
     }

    public function load(ObjectManager $manager): void
    {
        //for users
        $users = [];
        $admin = new User();
        $admin->setFullName('Administrateur de SymRecipe')
            ->setPseudo(null)
        ->setEmail('admin@symrecipe.fr')
        ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
        ->setPlainPassword('password');
        $user[] = $admin;
        $manager->persist($admin);
        for($u = 0; $u <10; $u++){
            $user = new User();
            $user->setFullName($this->faker->name())
                ->setPseudo(mt_rand(0, 1) === 1 ? $this->faker->firstName : null)
                ->setEmail($this->faker->email())
                ->setRoles(["ROLE_USER"])
                ->setPlainPassword("password");

            $users[] = $user;
            $manager->persist($user);
        }

        //fixtures for ingredients
        $ingredients = [];
        for ($i = 0; $i < 50; $i++){
            $ingredient = new Ingredient();
            $ingredient->setName('ingredient' . $i)
                ->setPrice(mt_rand(0, 100))
                ->setUser($users[mt_rand(0, count($users) - 1)])
            ;

            $ingredients[] = $ingredient;
            $manager->persist($ingredient);
        }

        //fixtures for recipes
        $recipes = [];
        for($j = 0; $j < 25; $j++){
            $recipe = new Recipe();
            $recipe->setName($this->faker->word())
                ->setTime(mt_rand(0, 1) == 1 ? mt_rand(1, 1440) : null)
                ->setNbPeople(mt_rand(0, 1)  == 1 ? mt_rand(1, 50) : null)
                ->setDifficulty(mt_rand(0, 1) == 1 ? mt_rand(1, 5) : null)
                ->setDescription($this->faker->text(300))
                ->setPrice(mt_rand(0, 1) == 1 ? mt_rand(1, 1000) : null)
                ->setIsFavorite(mt_rand(0, 1) == 1 ? true : false)
                ->setIsPublic(mt_rand(0, 1) == 1 ? true : false)
                ->setUser($users[mt_rand(0, count($users) - 1)])
            ;

            for($k = 0; $k < mt_rand(5, 15); $k++){
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1)]);
            }
            $recipes[] = $recipe;
            $manager->persist($recipe);
        }


        //fixtures for marks
        foreach ($recipes as $recipe){
           for($i = 0; $i < mt_rand(0, 4); $i++){
               $mark = new Mark();
               $mark->setMark(mt_rand(1, 5))
                   ->setUser($users[mt_rand(0, count($users) - 1)])
                   ->setRecipe($recipe);

               $manager->persist($mark);
           }
        }

        //fixtures for contact
        for($i = 0; $i < mt_rand(0, 10); $i++){
            $contact = new Contact();
            $contact->setFullName($this->faker->name())
                ->setEmail($this->faker->email())
                ->setSubject('Demande N° ' . ($i +1))
                ->setMessage($this->faker->text(100));
            $manager->persist($contact);
        }

        $manager->flush();
    }
}
