<?php

namespace App\Controller;

use App\Attribute\AppPermission;
use App\Entity\User;
use App\Form\EmployeeRoleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/employees')]
class EmployeeRoleController extends AbstractController
{
    #[Route('/{id}/roles', name: 'app_admin_employee_roles', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ORG_ADMIN')]
    #[AppPermission('employee_role_manage', 'Управление ролями сотрудников')]
    public function manageRoles(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Проверка: админ организации может редактировать только своих сотрудников. 
        // Системный администратор может редактировать всех.
        if (!$this->isGranted('ROLE_SYSTEM_ADMIN') && $user->getOrganization() !== $currentUser->getOrganization()) {
            throw $this->createAccessDeniedException('Вы не можете управлять ролями сотрудников другой организации.');
        }

        $form = $this->createForm(EmployeeRoleType::class, $user, [
            'is_system_admin' => $this->isGranted('ROLE_SYSTEM_ADMIN'),
            'current_organization' => $currentUser->getOrganization(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', sprintf('Роли пользователя %s успешно обновлены.', $user->getFullName() ?? $user->getEmail()));

            return $this->redirectToRoute('app_admin_employee_index');
        }

        return $this->render('admin/employee/roles.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}