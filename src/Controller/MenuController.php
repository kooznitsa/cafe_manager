<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MenuController extends AbstractController
{
    #[Route("/menu", name: "menu_list")]
    public function list(): Response
    {
        return $this->render("menu.html.twig", [

        ]);
    }
}
