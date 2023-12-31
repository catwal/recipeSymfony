<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Security;


class IngredientController extends AbstractController
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param IngredientRepository $ingredientRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/ingredient', name: 'ingredient', methods: ['GET'])]
    public function index(IngredientRepository $ingredientRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Expression(
            '"ROLE_USER" in role_names'
        ));

        $ingredients = $paginator->paginate(
            $ingredientRepository->findBy(['user' => $this->getUser()]), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/creation', name: 'ingredient.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted(new Expression(
            '"ROLE_USER" in role_names'
        ));

        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient à bien été créé !'
            );

            return $this->redirectToRoute('ingredient');
        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param IngredientRepository $repository
     * @param int $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/edition/{id}', name: 'ingredient.edit', methods: ['GET', 'POST'])]
    public function edit(IngredientRepository $repository, int $id, Request $request, EntityManagerInterface $manager): Response
    {
        $ingredient = $repository->findOneBy(["id" => $id]);
        $this->denyAccessUnlessGranted(new Expression(
            '"ROLE_USER" in role_names'
        ));

        $user= $this->getUser();
        $userIngredient = $ingredient->getUser();
        if($user !== $userIngredient){
            return $this->redirectToRoute('security.login');
        }

        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient à bien été modifié !'
            );

            return $this->redirectToRoute('ingredient');
        }

        return $this->render('pages/ingredient/edit.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @param EntityManagerInterface $manager
     * @param int $id
     * @return Response
     */
    #[Route('/ingredient/suppression/{id}', name: 'ingredient.delete', methods: ['GET'])]
    public function delete(EntityManagerInterface $manager, int $id): Response
    {
        $ingredient = $manager->getRepository(Ingredient::class)->findOneBy(['id'=>$id]);
        $user = $this->getUser();
        $userIngredient = $ingredient->getUser();
        if($user === $userIngredient){
            $manager->remove($ingredient);
            $manager->flush();
            $this->addFlash(
                'success',
                'Votre ingrédient à bien été supprimé !'
            );
        }


        return $this->redirectToRoute('ingredient');
    }
}
