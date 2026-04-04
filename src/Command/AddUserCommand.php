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
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:add-user',
    description: 'Créer un nouvel utilisateur',
)]
class AddUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            ->addArgument('pseudo', InputArgument::REQUIRED, 'Pseudo')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Créer un administrateur')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = new User();
        $user->setEmail($input->getArgument('email'));
        $user->setPseudo($input->getArgument('pseudo'));
        $user->setFirstName('');
        $user->setLastName('');
        $user->setTheme('light');
        $user->setLocale('fr');
        $user->setPassword(
            $this->hasher->hashPassword($user, $input->getArgument('password'))
        );

        if ($input->getOption('admin')) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf(
            '%s créé : %s',
            $input->getOption('admin') ? 'Administrateur' : 'Utilisateur',
            $user->getEmail()
        ));

        return Command::SUCCESS;
    }
}