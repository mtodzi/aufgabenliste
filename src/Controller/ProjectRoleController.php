<?php

namespace App\Controller;

use App\Attribute\AppPermission;
use App\Entity\ProjectRole;
use App\Form\ProjectRoleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/project-roles')]
#[IsGranted('ROLE_ORG_ADMIN')]
class ProjectRoleController extends AbstractController
{
    #[Route('/', name: 'app_admin_project_role_index', methods: ['GET'])]
    #[AppPermission('project_role_view', 'Просмотр списка ролей проекта')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $roles = $entityManager->getRepository(ProjectRole::class)->findBy([
            'organization' => $user->getOrganization()
        ], ['name' => 'ASC']);

        return $this->render('admin/project_role/index.html.twig', [
            'project_roles' => $roles,
            'has_roles' => count($roles) > 0, // Передаем флаг, чтобы скрыть кнопку
        ]);
    }

    #[Route('/new', name: 'app_admin_project_role_new', methods: ['GET', 'POST'])]
    #[AppPermission('project_role_create', 'Создание новых ролей проекта')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projectRole = new ProjectRole();
        $form = $this->createForm(ProjectRoleType::class, $projectRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $projectRole->setOrganization($user->getOrganization());

            $entityManager->persist($projectRole);
            $entityManager->flush();

            $this->addFlash('success', 'Роль проекта успешно создана.');
            return $this->redirectToRoute('app_admin_project_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_role/new.html.twig', [
            'project_role' => $projectRole,
            'form' => $form,
        ]);
    }

    #[Route('/defaults', name: 'app_admin_project_role_defaults', methods: ['GET', 'POST'])]
    #[AppPermission('project_role_create', 'Создание новых ролей проекта')]
    public function defaults(Request $request, EntityManagerInterface $entityManager): Response
    {
        $defaultRoles = [
            'manager'   => ['name' => 'Руководитель проекта', 'description' => 'Управляет процессом и контролирует выполнение задач'],
            'developer' => ['name' => 'Разработчик', 'description' => 'Занимается написанием кода и реализацией функционала'],
            'designer'  => ['name' => 'Дизайнер', 'description' => 'Проектирует UI/UX и визуальные материалы'],
            'analyst'   => ['name' => 'Аналитик', 'description' => 'Собирает требования и пишет спецификации'],
            'tester'    => ['name' => 'QA / Тестировщик', 'description' => 'Проверяет качество продукта и ищет ошибки'],
            'observer'  => ['name' => 'Наблюдатель', 'description' => 'Имеет доступ к проекту только на чтение'],
        ];

        if ($request->isMethod('POST')) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            if ($this->isCsrfTokenValid('add_defaults', $request->request->get('_token'))) {
                $selectedKeys = $request->request->all('roles');

                if (!empty($selectedKeys) && is_array($selectedKeys)) {
                    foreach ($selectedKeys as $key) {
                        if (isset($defaultRoles[$key])) {
                            $role = new ProjectRole();
                            $role->setName($defaultRoles[$key]['name']);
                            $role->setDescription($defaultRoles[$key]['description']);
                            $role->setOrganization($user->getOrganization());
                            
                            $entityManager->persist($role);
                        }
                    }
                    $entityManager->flush();
                    $this->addFlash('success', 'Роли по умолчанию успешно добавлены.');
                }
            }

            return $this->redirectToRoute('app_admin_project_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_role/defaults.html.twig', [
            'default_roles' => $defaultRoles,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_project_role_edit', methods: ['GET', 'POST'])]
    #[AppPermission('project_role_edit', 'Редактирование ролей проекта')]
    public function edit(Request $request, ProjectRole $projectRole, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($projectRole->getOrganization() !== $user->getOrganization()) {
            throw $this->createAccessDeniedException('Доступ запрещен. Эта роль принадлежит другой организации.');
        }

        $form = $this->createForm(ProjectRoleType::class, $projectRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Роль проекта обновлена.');
            return $this->redirectToRoute('app_admin_project_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_role/edit.html.twig', [
            'project_role' => $projectRole,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_project_role_delete', methods: ['POST'])]
    #[AppPermission('project_role_delete', 'Удаление ролей проекта')]
    public function delete(Request $request, ProjectRole $projectRole, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($projectRole->getOrganization() === $user->getOrganization()) {
            if ($this->isCsrfTokenValid('delete'.$projectRole->getId(), $request->request->get('_token'))) {
                $entityManager->remove($projectRole);
                $entityManager->flush();
                $this->addFlash('success', 'Роль проекта удалена.');
            }
        }
        return $this->redirectToRoute('app_admin_project_role_index', [], Response::HTTP_SEE_OTHER);
    }
}