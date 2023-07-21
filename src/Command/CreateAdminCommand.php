<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an administrator',
)]
class CreateAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, string $name = null)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('full_name', InputArgument::OPTIONAL, 'Full name')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('password', InputArgument::OPTIONAL, 'plain_password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);
        $fullName = $input->getArgument('full_name');
        if(!$fullName){
            $question = new Question('Quel est le nom de l\'admin ?');
            $fullName = $helper->ask($input, $output, $question);
        }

        $email = $input->getArgument('email');
        if(!$email){
            $question = new Question('Quel est l\'email de '. $fullName .'?');
            $email = $helper->ask($input, $output, $question);
        }
        $plainPassword = $input->getArgument('password');
         if(!$plainPassword){
             $question = new Question('Quel est le mot de passe de ' . $fullName .' ?');
             $plainPassword = $helper->ask($input, $output, $question);
         }

        $user = (new User())->setFullName($fullName)
            ->setEmail($email)
            //->setPlainPassword($plainPassword)
            ->setPassword($plainPassword)
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Création du nouvel admin avec succès');

        return Command::SUCCESS;
    }
}
