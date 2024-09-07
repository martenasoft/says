<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\UserRoleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user-role_admin',
    description: 'Change user role_admin',

)]
class UserCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private UserRoleService $roleService
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

        $user = $this->userRepository->findOneByEmail($email);
        if (empty($user)) {
            throw new UserNotFoundException("User [$email] not found");
        }

        $rolesFromDb = array_map(fn(Role $role) => $role->getName(), $this->roleRepository->findAll() ?? []);
        $roles = array_merge($rolesFromDb, User::ROLES, $user->getRoles());
        $roles = array_unique($roles);
        array_unshift($roles, 'Cancel');

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select role_admin/s',
            $roles,
            0
        );
        $question->setErrorMessage('Опция %s недопустима.');
        $question->setMultiselect(true);

        $selectedOption = $helper->ask($input, $output, $question);
        if (in_array('Cancel', $selectedOption)) {
            return Command::SUCCESS;
        }

        $user
            ->setEmail($email)
            ->setStatus(User::STATUS_ACTIVE)
        ;


        $this->roleService->addUserRoles($user, $selectedOption);

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
