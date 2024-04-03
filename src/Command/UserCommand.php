<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user-role',
    description: 'Change user role',

)]
class UserCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'User email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $roles = $io->choice('Select roles', User::ROLES, multiSelect: true);

        $user = $this->userRepository->findOneByEmail($email);
        if (empty($user)) {
            throw new UserNotFoundException("User [$email] not found");
        }


        $user
            ->setEmail($email)
            ->setRoles($roles)
        ;

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getMessage());
            }
            return Command::FAILURE;
        }

        $this->entityManager->flush();
        $io->success('Roles were changed: '.implode(', ', $user->getRoles()));
        return Command::SUCCESS;
    }
}
