<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/roles')]
#[IsGranted('ROLE_ORG_ADMIN')]
class RoleController extends AbstractController
{
    #[Route('/', name: 'app_admin_role_index', methods: ['GET'])]
    public function index(RoleRepository $roleRepository): Response
    {
        $user = $this->getUser();
        
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            // Разработчики видят только системные роли (где организация не указана)
            $roles = $roleRepository->findBy(['organization' => null]);
        } else {
            // Админы организаций видят только свои роли
            $roles = $roleRepository->findBy(['organization' => $user->getOrganization()]);
        }

        return $this->render('admin/role/index.html.twig', [
            'roles' => $roles,
        ]);
    }

    #[Route('/new', name: 'app_admin_role_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $role = new Role();
        
        // Если создает НЕ разработчик, автоматически привязываем роль к организации пользователя
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $role->setOrganization($this->getUser()->getOrganization());
        }

        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($role);
            $entityManager->flush();

            $this->addFlash('success', 'Роль создана успешно.');
            return $this->redirectToRoute('app_admin_role_index');
        }

        return $this->render('admin/role/new.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_role_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Role $role, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess($role);

        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Роль обновлена.');
            return $this->redirectToRoute('app_admin_role_index');
        }

        return $this->render('admin/role/edit.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_role_delete', methods: ['POST'])]
    public function delete(Request $request, Role $role, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess($role);

        if ($this->isCsrfTokenValid('delete'.$role->getId(), $request->request->get('_token'))) {
            $entityManager->remove($role);
            $entityManager->flush();
            $this->addFlash('success', 'Роль удалена.');
        }

        return $this->redirectToRoute('app_admin_role_index');
    }

    /**
     * Вспомогательный метод для проверки прав владения ролью
     */
    private function checkAccess(Role $role): void
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            if ($role->getOrganization() !== null) {
                throw $this->createAccessDeniedException('Разработчики могут управлять только системными ролями.');
            }
            return;
        }

        if ($role->getOrganization() !== $this->getUser()->getOrganization()) {
            throw $this->createAccessDeniedException('Вы не можете управлять ролями других организаций.');
        }
        
        if ($role->getOrganization() === null) {
            throw $this->createAccessDeniedException('Вы не можете изменять системные роли.');
        }
    }
}