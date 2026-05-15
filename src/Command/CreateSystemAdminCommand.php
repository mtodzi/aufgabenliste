<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-system-admin',
    description: 'Creates the initial system administrator user',
)]
class CreateSystemAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = 'mtodzi@gmail.com';
        $password = '123456';

        $userRepository = $this->entityManager->getRepository(User::class);
        $existingUser = $userRepository->findOneBy(['email' => $email]);

        $roleName = 'ROLE_SUPER_ADMIN';
        $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $roleName]);

        if (!$role) {
            $role = new Role();
            $role->setName($roleName);
            $this->entityManager->persist($role);
        }

        if ($existingUser) {
            $user = $existingUser;
            $io->note(sprintf('Пользователь "%s" уже существует, обновляем его права...', $email));
        } else {
            $user = new User();
            $user->setEmail($email);
            $user->setIsVerified(true);
            $user->setFullName('System Administrator');
            
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $io->note(sprintf('Создаем нового пользователя "%s"...', $email));
        }

        $user->addUserRole($role);
        $this->entityManager->flush();

        $io->success(sprintf('Пользователю "%s" успешно назначена роль "%s".', $email, $roleName));

        return Command::SUCCESS;
    }
}