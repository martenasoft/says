<?php

namespace App\Command;

use App\Entity\Role;
use App\Repository\RoleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Symfony\Component\String\s;

#[AsCommand(
    name: 'app:role_admin',
    description: 'Add a short description for your command',
)]
class RoleCommand extends Command
{
    public function __construct(private RoleRepository $roleRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::OPTIONAL, 'add|list')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');

        switch ($action) {
            case 'add':
                return $this->create($input, $output);
        }
        
        return Command::FAILURE;
    }

    private function create(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $question = new Question('Insert role_admin name (ROLE_YOUR_ROLE_NAME): ');
        $question->setValidator(function ($roleName) {
            if (empty($roleName)) {
                throw new \RuntimeException('name is required');
            }
            return $roleName;
        });

        $question->setMaxAttempts(3);
        $userInput = s($helper->ask($input, $output, $question))->trim()->upper()->toString();
        if (substr($userInput, 0, 5) !== "ROLE_") {
            $userInput = "ROLE_$userInput";
        }

        $question = new ConfirmationQuestion("Save $userInput? (Y/n) ", false); // 'false' означает ответ по умолчанию - "нет"

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $role = $this->roleRepository->findByName($userInput);

        if (!empty($role)) {
            throw new \RuntimeException("Role [ {$userInput} ] already exists!");
        }

        try {
            $role = new Role();
            $role->setName($userInput);
            $this->roleRepository->save($role, true);
        } catch (\Throwable $throwable) {
            throw new \RuntimeException("Role [ {$role->getName()} ] already exists!");
        }

        $io->success("Role [ {$role->getName()} ] was created!");
        return Command::SUCCESS;
    }
}
