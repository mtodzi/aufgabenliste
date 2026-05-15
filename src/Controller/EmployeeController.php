<?php

namespace App\Controller;

use App\Attribute\AppPermission;
use App\Entity\User;
use App\Form\EmployeeType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route('/admin/employees')]
#[IsGranted('ROLE_ORG_ADMIN')]
class EmployeeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/', name: 'app_admin_employee_index', methods: ['GET'])]
    #[AppPermission('employee_view', 'Просмотр списка сотрудников')]
    public function index(UserRepository $userRepository): Response
    {
        /** @var User $admin */
        $admin = $this->getUser();
        
        if (!$admin->getOrganization()) {
            throw $this->createAccessDeniedException('Вы не привязаны к организации.');
        }

        $employees = $userRepository->findBy([
            'organization' => $admin->getOrganization()
        ]);

        return $this->render('admin/employee/index.html.twig', [
            'employees' => $employees,
        ]);
    }

    #[Route('/new', name: 'app_admin_employee_new', methods: ['GET', 'POST'])]
    #[AppPermission('employee_create', 'Добавление новых сотрудников')]
    public function new(Request $request): Response
    {
        /** @var User $admin */
        $admin = $this->getUser();

        if (!$admin->getOrganization()) {
            throw $this->createAccessDeniedException('Вы не привязаны к организации.');
        }
        
        $employee = new User();
        $employee->setOrganization($admin->getOrganization());
        $employee->setIsVerified(true);
        $employee->setIsActive(true);

        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // --- ОБРАБОТКА ПАРОЛЯ ---
            $generatePassword = $form->get('generatePassword')->getData();
            $plainPassword = $form->has('plainPassword') ? $form->get('plainPassword')->getData() : null;

            if ($generatePassword) {
                $generatedPass = bin2hex(random_bytes(8)); // 16 символов
                $employee->setPassword($this->passwordHasher->hashPassword($employee, $generatedPass));
                $this->addFlash('success', "Сотрудник добавлен. Сгенерированный пароль: <strong class='text-primary'>$generatedPass</strong>. Пожалуйста, сохраните его.");
            } elseif ($plainPassword) {
                $employee->setPassword($this->passwordHasher->hashPassword($employee, $plainPassword));
                $this->addFlash('success', 'Сотрудник добавлен.');
            } else {
                throw new \LogicException('Пароль не был предоставлен и не был сгенерирован.');
            }
            // --- КОНЕЦ ОБРАБОТКИ ПАРОЛЯ ---

            // --- ОБРАБОТКА ТЕЛЕФОНОВ ---
            foreach ($form->get('phones')->getData() as $phone) {
                if ($phone->getNumber()) {
                    $phone->setUser($employee);
                    $this->entityManager->persist($phone);
                }
            }
            // --- КОНЕЦ ОБРАБОТКИ ТЕЛЕФОНОВ ---

            $this->entityManager->persist($employee);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_employee_index');
        }

        return $this->render('admin/employee/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_employee_edit', methods: ['GET', 'POST'])]
    #[AppPermission('employee_edit', 'Редактирование данных сотрудника')]
    public function edit(Request $request, User $employee): Response
    {
        /** @var User $admin */
        $admin = $this->getUser();

        if ($employee->getOrganization() !== $admin->getOrganization()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(EmployeeType::class, $employee, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Данные сотрудника обновлены.');
            return $this->redirectToRoute('app_admin_employee_index');
        }

        return $this->render('admin/employee/edit.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee,
        ]);
    }

    #[Route('/{id}/change-password', name: 'app_admin_employee_change_password', methods: ['GET', 'POST'])]
    #[AppPermission('employee_change_password', 'Изменение пароля сотрудника')]
    public function changePassword(Request $request, User $employee): Response
    {
        /** @var User $admin */
        $admin = $this->getUser();

        if ($employee->getOrganization() !== $admin->getOrganization()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createFormBuilder()
            ->add('generatePassword', CheckboxType::class, [
                'required' => false,
                'label' => 'Сгенерировать пароль автоматически',
                'attr' => ['class' => 'form-check-input'],
                'row_attr' => ['class' => 'form-check form-switch mb-3'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Новый пароль', 'attr' => ['placeholder' => '••••••••']],
                'second_options' => ['label' => 'Повторите новый пароль', 'attr' => ['placeholder' => '••••••••']],
                'invalid_message' => 'Пароли должны совпадать.',
                'required' => false,
                'constraints' => [
                    new Length(min: 6, minMessage: 'Пароль должен быть не менее {{ limit }} символов'),
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['generatePassword']) {
                $newPass = bin2hex(random_bytes(8));
                $employee->setPassword($this->passwordHasher->hashPassword($employee, $newPass));
                $this->addFlash('success', "Пароль изменен. Новый пароль: <strong class='text-primary'>$newPass</strong>. Обязательно передайте его сотруднику.");
            } else {
                if (empty($data['plainPassword'])) {
                    $this->addFlash('danger', 'Пожалуйста, введите пароль или выберите автоматическую генерацию.');
                    return $this->render('admin/employee/change_password.html.twig', ['form' => $form->createView(), 'employee' => $employee]);
                }
                $employee->setPassword($this->passwordHasher->hashPassword($employee, $data['plainPassword']));
                $this->addFlash('success', 'Пароль успешно обновлен.');
            }
            $this->entityManager->flush();
            return $this->redirectToRoute('app_admin_employee_index');
        }

        return $this->render('admin/employee/change_password.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee,
        ]);
    }

    #[Route('/{id}/toggle-access', name: 'app_admin_employee_toggle_access', methods: ['POST'])]
    #[AppPermission('employee_toggle_access', 'Изменение статуса доступа сотрудника')]
    public function toggleAccess(User $employee): Response
    {
        /** @var User $admin */
        $admin = $this->getUser();

        if ($employee->getOrganization() !== $admin->getOrganization() || $employee === $admin) {
            throw $this->createAccessDeniedException('Вы не можете изменить доступ этому пользователю.');
        }

        $employee->setIsActive(!$employee->isActive());
        $this->entityManager->flush();

        $this->addFlash('success', 'Статус доступа изменен.');
        return $this->redirectToRoute('app_admin_employee_index');
    }
}