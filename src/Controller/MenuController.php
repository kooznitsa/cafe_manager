<?php

namespace App\Controller;

use App\Manager\CategoryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MenuController extends AbstractController
{
    public function __construct(
        private readonly CategoryManager $categoryManager,
    ) {
    }

    #[Route('/menu', name: 'menu_list')]
    public function list(): Response
    {
        $categories = $this->categoryManager->getCategories();
        $dishes = [];

        foreach ($categories as $category) {
            $dishes[] = $category->getDishes();
        }

        return $this->render('menu.html.twig', [
            'dishes' => $dishes,
        ]);
    }
}
