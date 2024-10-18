<?php

namespace App\Service;

use App\Entity\Recipe;
use App\Manager\{DishManager, ProductManager, RecipeManager};
use Symfony\Component\HttpFoundation\Request;

class RecipeBuilderService
{
    public function __construct(
        private readonly DishManager $dishManager,
        private readonly ProductManager $productManager,
        private readonly RecipeManager $recipeManager,
    ) {
    }

    public function createRecipeWithDishAndProduct(Request $request): ?int
    {
        [$dish, $product, $amount] = $this->getRecipeParams($request);

        return $this->recipeManager->saveRecipe($dish, $product, $amount);
    }

    public function updateRecipeWithDishAndProduct(Request $request): ?Recipe
    {
        [$dish, $product, $amount] = $this->getRecipeParams($request, 'PATCH');
        $recipeId = $request->query->get('recipeId');

        return $this->recipeManager->updateRecipe($recipeId, $dish, $product, $amount);
    }

    public function getRecipeParams(Request $request, string $requestMethod = 'POST'): array
    {
        $inputBag = $requestMethod === 'POST' ? $request->request : $request->query;

        $dishId = $inputBag->get('dishId');
        $productId = $inputBag->get('productId');
        $amount = $inputBag->get('amount');
        $dish = $dishId ? $this->dishManager->getDishById($dishId) : null;
        $product = $productId ? $this->productManager->getProductById($productId) : null;

        return [$dish, $product, $amount];
    }
}
