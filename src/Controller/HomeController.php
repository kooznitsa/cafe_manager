<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Manager\{CategoryManager, DishManager, UserManager};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly DishManager $dishManager,
        private readonly CategoryManager $categoryManager,
    ) {
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        $category = $this->categoryManager->getCategoryById(1);
        $dishes = $this->dishManager->getCategoryDishes($category);

        $json = $this->json(['dishes' => array_map(static fn(Dish $dish) => $dish->toArray(), $dishes)]);

        return $this->render("home.html.twig", [
            "dishes" => $dishes,
        ]);
    }
}
