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
//        $user = $this->userManager->createUser('Cat', 'try', 'cat@example.com', 'UK');
//        sleep(1);
//        $user = $this->userManager->updateUserName($user->getId(), 'Shark');
//
//        return $this->json($user->toArray());

        $users = $this->userManager->findUsersByAddress('Санкт-Петербург');

        return $this->render("home.html.twig", [
            "users" => $users,
        ]);
    }
}
