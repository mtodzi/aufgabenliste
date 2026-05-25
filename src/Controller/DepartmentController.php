<?php

namespace App\Controller;

use App\Attribute\AppPermission;
use App\Entity\Department;
use App\Form\DepartmentType;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/departments')]
#[IsGranted('ROLE_ORG_ADMIN')]
class DepartmentController extends AbstractController
{
    #[Route('/', name: 'app_admin_department_index', methods: ['GET'])]
    #[AppPermission('department_view', 'Просмотр списка подразделений')]
    public function index(DepartmentRepository $departmentRepository): Response
    {
        $user = $this->getUser();
        
        $departments = $departmentRepository->findBy(['organization' => $user->getOrganization()]);

        return $this->render('admin/department/index.html.twig', [
            'departments' => $departments,
        ]);
    }

    #[Route('/new', name: 'app_admin_department_new', methods: ['GET', 'POST'])]
    #[AppPermission('department_create', 'Создание новых подразделений')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $department = new Department();
        $organization = $this->getUser()->getOrganization();
        $department->setOrganization($organization);

        $form = $this->createForm(DepartmentType::class, $department, [
            'current_organization' => $organization
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($department);
            $entityManager->flush();

            $this->addFlash('success', 'Подразделение создано успешно.');
            return $this->redirectToRoute('app_admin_department_index');
        }

        return $this->render('admin/department/new.html.twig', [
            'department' => $department,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_department_edit', methods: ['GET', 'POST'])]
    #[AppPermission('department_edit', 'Редактирование подразделений')]
    public function edit(Request $request, Department $department, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess($department);

        $form = $this->createForm(DepartmentType::class, $department, [
            'current_organization' => $this->getUser()->getOrganization()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Подразделение обновлено.');
            return $this->redirectToRoute('app_admin_department_index');
        }

        return $this->render('admin/department/edit.html.twig', [
            'department' => $department,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_department_delete', methods: ['POST'])]
    #[AppPermission('department_delete', 'Удаление подразделений')]
    public function delete(Request $request, Department $department, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess($department);

        if ($this->isCsrfTokenValid('delete'.$department->getId(), $request->request->get('_token'))) {
            $entityManager->remove($department);
            $entityManager->flush();
            $this->addFlash('success', 'Подразделение удалено.');
        }

        return $this->redirectToRoute('app_admin_department_index');
    }

    private function checkAccess(Department $department): void
    {
        if ($department->getOrganization() !== $this->getUser()->getOrganization()) {
            throw $this->createAccessDeniedException('Вы не можете управлять подразделениями других организаций.');
        }
    }
}