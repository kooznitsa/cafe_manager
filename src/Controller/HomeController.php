<?php

namespace App\Controller;

use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

        $users = $this->userManager->getUsers(0, 3);

        return $this->render("home.html.twig", [
            "users" => $users,
        ]);
    }
}
