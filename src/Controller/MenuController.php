<?php

namespace App\Controller;

use App\Manager\{CategoryManager, DishManager, RecipeManager};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MenuController extends AbstractController
{
    public function __construct(
        private readonly CategoryManager $categoryManager,
        private readonly DishManager $dishManager,
        private readonly RecipeManager $recipeManager,
    ) {
    }

    #[Route('/', name: 'menu_list')]
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

    #[Route('/recipe/{dishId}', name: 'recipe', requirements: ['dishId' => '\d+'])]
    public function recipe(int $dishId): Response
    {
        $dish = $this->dishManager->getDishById($dishId);
        $recipeItems = $this->recipeManager->getDishRecipe($dish);

        return $this->render('recipe.html.twig', [
            'recipe' => $recipeItems,
        ]);
    }
}
