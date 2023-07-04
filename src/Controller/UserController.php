<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{

        private $security;

        public function __construct(Security $security)
        {
            $this->security = $security;
        }
    /**
     * @param int $id
     * @param UserRepository $repository
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit (int $id, UserRepository $repository, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $choosenUser = $repository->findOneBy(["id" => $id]);

        $this->denyAccessUnlessGranted(new Expression(
            '"ROLE_USER" in role_names'
        ));

        if($this->security->isGranted('user === choosenUser')){
            return $this->redirectToRoute('security.login');
        }

        if (!$choosenUser) {
            return $this->redirectToRoute('security.login');
        }

        $form = $this->createForm(UserType::class, $choosenUser);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($choosenUser, $form->getData()->getPlainPassword())){
                $choosenUser = $form->getData();
                $manager->persist($choosenUser);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Vos informations ont été mises à jour'
                );

                return $this->redirectToRoute('recipe.index');
            }else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe n\'est pas valide'
                );
            }

         }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/utilisateur/edition-mot-de-passe/{id}', name: 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword(int $id, UserRepository $repository, EntityManagerInterface $manager, Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $choosenUser = $repository->findOneBy(['id'=> $id]);
        $this->denyAccessUnlessGranted(new Expression(
            '"ROLE_USER" in role_names'
        ));

        if($this->security->isGranted('user === choosenUser')){
            return $this->redirectToRoute('security.login');
        }

        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if($hasher->isPasswordValid($choosenUser, $form->getData()['plainPassword'])){
                $choosenUser->setUpdatedAt(new \DateTimeImmutable());
                $choosenUser->setPlainPassword(
                    $form->getData()['newPassword']
                );
                $this->addFlash(
                    'success',
                    'Mot de passe mis à jour'
                );
                $manager->persist($choosenUser);
                $manager->flush();

                return $this->redirectToRoute('recipe.index');

            } else {
                $this->addFlash(
                    'warning',
                    'une erreur est survenue lors de la mise à jour du mot de passe'
                );
            }
        }

        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
