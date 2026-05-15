<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-user-roles',
    description: 'Выводит список всех пользователей и назначенных им ролей.',
)]
class ListUserRolesCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $users = $this->userRepository->findAll();

        if (!$users) {
            $io->warning('В базе данных пока нет пользователей.');
            return Command::SUCCESS;
        }

        $io->title('Список пользователей и их ролей');

        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                $user->getId(),
                $user->getEmail(),
                implode(', ', $user->getRoles()),
            ];
        }

        $io->table(['ID', 'Email', 'Роли (включая иерархию и ROLE_USER)'], $rows);

        return Command::SUCCESS;
    }
}