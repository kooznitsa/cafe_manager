<?php

namespace App\Controller;

use App\Manager\{CategoryManager};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MenuController extends AbstractController
{
    public function __construct(private readonly CategoryManager $categoryManager)
    {
    }

    #[Route('/menu', name: 'menu_list')]
    public function list(): Response
    {
        $categories = $this->categoryManager->listCategories();

        return $this->render('menu.html.twig', [
            'categories' => $categories,
        ]);
    }
}
