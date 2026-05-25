<?php

namespace App\Controller;

use App\Attribute\AppPermission;
use App\Entity\ProjectStatus;
use App\Form\ProjectStatusType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/project-statuses')]
#[IsGranted('ROLE_ORG_ADMIN')]
class ProjectStatusController extends AbstractController
{
    #[Route('/', name: 'app_admin_project_status_index', methods: ['GET'])]
    #[AppPermission('project_status_view', 'Просмотр списка статусов проекта')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $statuses = $entityManager->getRepository(ProjectStatus::class)->findBy([
            'organization' => $user->getOrganization()
        ], ['sortOrder' => 'ASC']);

        return $this->render('admin/project_status/index.html.twig', [
            'project_statuses' => $statuses,
        ]);
    }

    #[Route('/new', name: 'app_admin_project_status_new', methods: ['GET', 'POST'])]
    #[AppPermission('project_status_create', 'Создание новых статусов проекта')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projectStatus = new ProjectStatus();
        $form = $this->createForm(ProjectStatusType::class, $projectStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $projectStatus->setOrganization($user->getOrganization());

            $entityManager->persist($projectStatus);
            $entityManager->flush();

            $this->addFlash('success', 'Статус проекта успешно создан.');
            return $this->redirectToRoute('app_admin_project_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_status/new.html.twig', [
            'project_status' => $projectStatus,
            'form' => $form,
        ]);
    }

    #[Route('/defaults', name: 'app_admin_project_status_defaults', methods: ['GET', 'POST'])]
    #[AppPermission('project_status_create', 'Создание новых статусов проекта')]
    public function defaults(Request $request, EntityManagerInterface $entityManager): Response
    {
        $defaultStatuses = [
            'new'         => ['name' => 'Новый', 'color' => '#0dcaf0', 'sort' => 10], // Cyan
            'in_progress' => ['name' => 'В работе', 'color' => '#0d6efd', 'sort' => 20], // Blue
            'on_hold'     => ['name' => 'На паузе', 'color' => '#ffc107', 'sort' => 30], // Yellow
            'completed'   => ['name' => 'Завершен', 'color' => '#198754', 'sort' => 40], // Green
            'cancelled'   => ['name' => 'Отменен', 'color' => '#dc3545', 'sort' => 50], // Red
        ];

        if ($request->isMethod('POST')) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            if ($this->isCsrfTokenValid('add_defaults', $request->request->get('_token'))) {
                $selectedKeys = $request->request->all('statuses');

                if (!empty($selectedKeys) && is_array($selectedKeys)) {
                    foreach ($selectedKeys as $key) {
                        if (isset($defaultStatuses[$key])) {
                            $status = new ProjectStatus();
                            $status->setName($defaultStatuses[$key]['name']);
                            $status->setColor($defaultStatuses[$key]['color']);
                            $status->setSortOrder($defaultStatuses[$key]['sort']);
                            $status->setOrganization($user->getOrganization());
                            
                            $entityManager->persist($status);
                        }
                    }
                    $entityManager->flush();
                    $this->addFlash('success', 'Статусы по умолчанию успешно добавлены.');
                }
            }

            return $this->redirectToRoute('app_admin_project_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_status/defaults.html.twig', [
            'default_statuses' => $defaultStatuses,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_project_status_edit', methods: ['GET', 'POST'])]
    #[AppPermission('project_status_edit', 'Редактирование статусов проекта')]
    public function edit(Request $request, ProjectStatus $projectStatus, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($projectStatus->getOrganization() !== $user->getOrganization()) {
            throw $this->createAccessDeniedException('Доступ запрещен. Этот статус принадлежит другой организации.');
        }

        $form = $this->createForm(ProjectStatusType::class, $projectStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Статус проекта обновлен.');
            return $this->redirectToRoute('app_admin_project_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_status/edit.html.twig', [
            'project_status' => $projectStatus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_project_status_delete', methods: ['POST'])]
    #[AppPermission('project_status_delete', 'Удаление статусов проекта')]
    public function delete(Request $request, ProjectStatus $projectStatus, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($projectStatus->getOrganization() !== $user->getOrganization()) {
            throw $this->createAccessDeniedException('Доступ запрещен. Этот статус принадлежит другой организации.');
        }

        if ($this->isCsrfTokenValid('delete'.$projectStatus->getId(), $request->request->get('_token'))) {
            $entityManager->remove($projectStatus);
            $entityManager->flush();
            $this->addFlash('success', 'Статус проекта удален.');
        }

        return $this->redirectToRoute('app_admin_project_status_index', [], Response::HTTP_SEE_OTHER);
    }
}