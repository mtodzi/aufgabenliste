<?php

namespace App\Controller;

use App\Attribute\AppPermission;
use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/organizations')] // Prefix all routes in this controller with /admin/organizations
#[IsGranted('ROLE_SYSTEM_ADMIN')] // Ensure only SYSTEM_ADMIN can access these routes
class OrganizationController extends AbstractController
{
    #[Route('/', name: 'app_admin_organization_index', methods: ['GET'])]
    #[AppPermission('organization_view', 'Просмотр списка организаций')]
    public function index(OrganizationRepository $organizationRepository): Response
    {
        return $this->render('organization/index.html.twig', [
            'organizations' => $organizationRepository->findAll(),
        ]);
    }

    #[Route('/{id}/activate', name: 'app_admin_organization_activate', methods: ['POST'])]
    #[AppPermission('organization_activate', 'Активация организации')]
    public function activate(Organization $organization, EntityManagerInterface $entityManager): Response
    {
        $organization->setIsActive(true);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Организация "%s" успешно активирована.', $organization->getName()));

        return $this->redirectToRoute('app_admin_organization_index');
    }

    #[Route('/{id}/deactivate', name: 'app_admin_organization_deactivate', methods: ['POST'])]
    #[AppPermission('organization_deactivate', 'Деактивация организации')]
    public function deactivate(Organization $organization, EntityManagerInterface $entityManager): Response
    {
        $organization->setIsActive(false);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Организация "%s" успешно деактивирована.', $organization->getName()));

        return $this->redirectToRoute('app_admin_organization_index');
    }
}