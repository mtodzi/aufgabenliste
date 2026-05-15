<?php

namespace App\Controller;

use App\Attribute\AppPermission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[AppPermission('home_view', 'Просмотр главной страницы')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
}