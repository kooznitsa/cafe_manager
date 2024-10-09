<?php

namespace App\Controller;

use App\Manager\UserManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function __construct(private readonly UserManager $userManager)
    {
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        $users = $this->userManager->findUsersByAddress('Санкт-Петербург');

        return $this->render("home.html.twig", [
            "users" => $users,
        ]);
    }
}
