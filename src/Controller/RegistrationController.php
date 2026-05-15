<?php

namespace App\Controller;

use App\Attribute\AppPermission;
use App\Entity\Organization;
use App\Entity\User;
use App\Entity\Role; // Импортируем сущность Role
use App\Form\RegistrationFormType; // Предполагается, что у вас есть такая форма
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    #[AppPermission('register', 'Регистрация пользователей')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        // Предполагается, что у вас есть RegistrationFormType для обработки полей формы
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // --- СОЗДАНИЕ ОРГАНИЗАЦИИ ---
            $organization = new Organization();
            $organization->setName($form->get('organizationName')->getData());
            $organization->setIsActive(true); // Активируем организацию сразу
            $entityManager->persist($organization);

            // Привязываем пользователя к новой организации
            $user->setOrganization($organization);

            // --- ОБРАБОТКА ПАРОЛЯ ---
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            // Устанавливаем другие необходимые свойства, например, верификацию
            $user->setIsVerified(true); // Или false, если требуется подтверждение по email
            $user->setIsActive(true); // Делаем пользователя активным для входа
            // $user->setFullName($form->get('fullName')->getData()); // Если есть поле полного имени

            // --- НАЗНАЧЕНИЕ РОЛИ ROLE_ORG_ADMIN ---
            $roleRepository = $entityManager->getRepository(Role::class);
            $adminRole = $roleRepository->findOneBy(['name' => 'ROLE_ORG_ADMIN']);

            if (!$adminRole) {
                // Если роль ROLE_ORG_ADMIN не найдена, это указывает на проблему с данными.
                // Убедитесь, что вы запустили команду app:add-roles-and-permissions.
                throw new \RuntimeException('Роль "ROLE_ORG_ADMIN" не найдена в базе данных. Пожалуйста, убедитесь, что она существует.');
            }

            $user->addUserRole($adminRole); // Добавляем роль ROLE_ORG_ADMIN пользователю
            // --- КОНЕЦ НАЗНАЧЕНИЯ РОЛИ ---

            $entityManager->persist($user);
            $entityManager->flush();

            // После успешной регистрации можно перенаправить пользователя
            return $this->redirectToRoute('app_home'); // Или на другую страницу
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}