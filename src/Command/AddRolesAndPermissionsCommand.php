<?php

namespace App\Command;

use App\Attribute\AppPermission;
use App\Entity\Permission;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(
    name: 'app:add-roles-and-permissions',
    description: 'Синхронизирует роли и права доступа из БД и атрибутов #[AppPermission].',
)]
class AddRolesAndPermissionsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RouterInterface $router
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        // No arguments or options needed for this interactive command
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $permissionRepository = $this->entityManager->getRepository(Permission::class);
        $roleRepository = $this->entityManager->getRepository(Role::class);

        $io->title('Синхронизация ролей и прав доступа (Permissions & Roles)');

        // 1. Собираем права
        $gatheredPermissions = [];

        $io->info('Сканирование контроллеров на наличие атрибута #[AppPermission]...');

        $routes = $this->router->getRouteCollection();
        foreach ($routes as $route) {
            $controller = $route->getDefault('_controller');
            if (!$controller || !is_string($controller)) {
                continue;
            }

            $parts = explode('::', $controller);
            $className = $parts[0];
            $methodName = $parts[1] ?? '__invoke';

            if (!class_exists($className)) {
                continue;
            }

            try {
                $reflectionClass = new \ReflectionClass($className);
                
                // Проверяем атрибуты на уровне класса
                foreach ($reflectionClass->getAttributes(AppPermission::class) as $attribute) {
                    $appPerm = $attribute->newInstance();
                    $gatheredPermissions[$appPerm->name] = $appPerm->description;
                }

                // Проверяем атрибуты на уровне метода
                if ($reflectionClass->hasMethod($methodName)) {
                    $reflectionMethod = $reflectionClass->getMethod($methodName);
                    foreach ($reflectionMethod->getAttributes(AppPermission::class) as $attribute) {
                        $appPerm = $attribute->newInstance();
                        $gatheredPermissions[$appPerm->name] = $appPerm->description;
                    }
                }
            } catch (\ReflectionException $e) {
                continue;
            }
        }

        $io->section('Синхронизация прав доступа (Permissions)');
        $allPermissions = [];
        foreach ($gatheredPermissions as $name => $description) {
            $permission = $permissionRepository->findOneBy(['name' => $name]);
            if (!$permission) {
                $permission = new Permission();
                $permission->setName($name);
                $permission->setDescription($description);
                $this->entityManager->persist($permission);
                $io->text(sprintf('  <info>Добавлено право:</info> %s (%s)', $name, $description));
            }
            $allPermissions[$name] = $permission; // Store for later use
        }
        $this->entityManager->flush();
        $io->success('Права доступа успешно синхронизированы.');

        // 2. Define and add Roles
        $defaultRoles = [
            'ROLE_SUPER_ADMIN' => [
                'title' => 'Разработчик',
                'description' => 'Полный доступ ко всем функциям системы (Техподдержка)',
                'permissions' => array_keys($gatheredPermissions), // Все собранные права (в т.ч. из атрибутов)
            ],
            'ROLE_ORG_ADMIN' => [
                'title' => 'Администратор организации',
                'description' => 'Управление сотрудниками и настройками организации',
                'permissions' => [], // Права теперь назначаются через админ-панель (или впишите сюда ключи атрибутов)
            ],
        ];

        $io->section('Добавление ролей (Roles)');
        foreach ($defaultRoles as $roleName => $roleData) {
            $role = $roleRepository->findOneBy(['name' => $roleName]);
            if (!$role) {
                $role = new Role();
                $role->setName($roleName);
                $this->entityManager->persist($role);
                $io->text(sprintf('  <info>Добавлена роль:</info> %s (%s)', $roleName, $roleData['title']));
            } else {
                $io->text(sprintf('  <comment>Роль уже существует:</comment> %s', $roleName));
            }

            // Обновляем название и описание, если методы существуют в сущности
            if (method_exists($role, 'setTitle')) {
                $role->setTitle($roleData['title']);
            }
            if (method_exists($role, 'setDescription')) {
                $role->setDescription($roleData['description']);
            }

            // Clear existing permissions for the role to ensure it matches the default definition
            foreach ($role->getPermissions() as $existingPermission) {
                $role->removePermission($existingPermission);
            }

            // Add defined permissions to the role
            foreach ($roleData['permissions'] as $permissionKey) {
                if (isset($allPermissions[$permissionKey])) {
                    $role->addPermission($allPermissions[$permissionKey]);
                } else {
                    $io->warning(sprintf('  Право "%s" для роли "%s" не найдено. Пропущено.', $permissionKey, $roleName));
                }
            }
        }
        $this->entityManager->flush();
        $io->success('Роли и их права доступа успешно добавлены/обновлены.');

        // 3. Optional: Assign roles to an existing user (example)
        if ($io->confirm('Хотите назначить роль существующему пользователю?', false)) {
            $userRepository = $this->entityManager->getRepository(\App\Entity\User::class);
            $emailQuestion = new Question('Введите email пользователя, которому хотите назначить роль:');
            $userEmail = $io->askQuestion($emailQuestion);

            $user = $userRepository->findOneBy(['email' => $userEmail]);

            if ($user) {
                $availableRoles = $roleRepository->findAll();
                $roleChoices = array_map(fn(Role $r) => $r->getName(), $availableRoles);

                $roleQuestion = new ChoiceQuestion(
                    'Выберите роль для назначения (можно выбрать несколько, через запятую):',
                    $roleChoices,
                    null // No default
                );
                $roleQuestion->setMultiselect(true);
                $selectedRoleNames = $io->askQuestion($roleQuestion);

                foreach ($user->getUserRoles() as $existingUserRole) {
                    $user->removeUserRole($existingUserRole);
                }

                foreach ($selectedRoleNames as $selectedRoleName) {
                    $selectedRole = $roleRepository->findOneBy(['name' => $selectedRoleName]);
                    if ($selectedRole) {
                        $user->addUserRole($selectedRole);
                    }
                }
                $this->entityManager->flush();
                $io->success(sprintf('Роли успешно назначены пользователю %s.', $userEmail));
            } else {
                $io->error(sprintf('Пользователь с email "%s" не найден.', $userEmail));
            }
        }

        $io->success('Команда выполнена.');

        return Command::SUCCESS;
    }
}