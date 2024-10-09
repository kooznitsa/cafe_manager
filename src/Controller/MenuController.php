<?php

namespace App\Controller;

use App\Manager\{CategoryManager, DishManager};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MenuController extends AbstractController
{
    public function __construct(
        private readonly CategoryManager $categoryManager,
        private readonly DishManager $dishManager,
    ) {
    }

    #[Route('/menu', name: 'menu_list')]
    public function list(): Response
    {
        $categories = $this->categoryManager->listCategories();
        $dishes = [];

        foreach ($categories as $category) {
            $dishes[] = $category->getDishes();
        }

        return $this->render('menu.html.twig', [
            'dishes' => $dishes,
        ]);
    }
}
