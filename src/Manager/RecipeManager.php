<?php

namespace App\Manager;

use App\Entity\{Dish, Product, Recipe};
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecipeManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RecipeRepository $recipeRepository,
    ) {
    }

    public function saveRecipe(Dish $dish, Product $product, float $amount): ?int
    {
        $recipe = $this->getRecipeByDishAndProduct($dish, $product);

        if (!$recipe) {
            $recipe = new Recipe();
            $this->setRecipeParams($recipe, $dish, $product, $amount);
            $this->entityManager->persist($recipe);
            $this->entityManager->flush();
        }

        return $recipe->getId();
    }

    /**
     * @return Recipe[]
     */
    public function getDishRecipe(Dish $dish): array
    {
        return $this->recipeRepository->findBy(['dish' => $dish]);
    }

    public function updateRecipe(
        int $recipeId,
        ?Dish $dish = null,
        ?Product $product = null,
        ?float $amount = null,
    ): ?Recipe {
        /** @var Recipe $recipe */
        $recipe = $this->recipeRepository->find($recipeId);
        if (!$recipe) {
            return null;
        }
        $recipeDish = $recipe->getDish();
        $recipeDish->removeRecipe($recipe);
        $this->setRecipeParams($recipe, $dish, $product, $amount);
        $this->entityManager->flush();

        return $recipe;
    }

    public function deleteRecipe(Recipe $recipe): bool
    {
        $this->entityManager->remove($recipe);
        $recipe->getDish()->removeRecipe($recipe);
        $this->entityManager->flush();

        return true;
    }

    public function deleteRecipeById(int $recipeId): bool
    {
        /** @var Recipe $recipe */
        $recipe = $this->recipeRepository->find($recipeId);
        if (!$recipe) {
            return false;
        }
        return $this->deleteRecipe($recipe);
    }

    private function getRecipeByDishAndProduct(Dish $dish, Product $product): ?Recipe
    {
        return $this->recipeRepository->findOneBy(['dish' => $dish, 'product' => $product]);
    }

    private function setRecipeParams(Recipe $recipe, ?Dish $dish, ?Product $product, ?float $amount): void
    {
        $recipe->setDish($dish);
        $recipe->setProduct($product);
        $recipe->setAmount($amount);
        $dish?->addRecipe($recipe);
    }
}
