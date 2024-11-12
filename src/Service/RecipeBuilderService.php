<?php

namespace App\Service;

use App\DTO\Request\RecipeRequestDTO;
use App\Entity\Recipe;
use App\Manager\{DishManager, ProductManager, RecipeManager};

class RecipeBuilderService
{
    public function __construct(
        private readonly DishManager $dishManager,
        private readonly ProductManager $productManager,
        private readonly RecipeManager $recipeManager,
    ) {
    }

    public function createRecipeWithDishAndProduct(RecipeRequestDTO $dto): Recipe
    {
        [$dish, $product, $amount] = $this->getRecipeParams($dto);

        return $this->recipeManager->saveRecipe($dish, $product, $amount);
    }

    public function updateRecipeWithDishAndProduct(Recipe $recipe, RecipeRequestDTO $dto): ?Recipe
    {
        [$dish, $product, $amount] = $this->getRecipeParams($dto);

        return $this->recipeManager->updateRecipe($recipe, $dish, $product, $amount);
    }

    public function getRecipeParams(RecipeRequestDTO $dto): array
    {
        $dish = $dto->dishId ? $this->dishManager->getDishById($dto->dishId) : null;
        $product = $dto->productId ? $this->productManager->getProductById($dto->productId) : null;
        $amount = $dto->amount;

        return [$dish, $product, $amount];
    }
}
