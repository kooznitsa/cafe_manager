<?php

namespace App\Manager;

use App\Entity\{Dish, Product, Recipe};
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RecipeManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RecipeRepository $recipeRepository,
    ) {
    }

    public function save(Recipe $recipe): void
    {
        $this->entityManager->persist($recipe);
        $this->entityManager->flush();
    }

    public function saveRecipe(Dish $dish, Product $product, float $amount): Recipe
    {
        $recipe = $this->getRecipeByDishAndProduct($dish, $product);

        if (!$recipe) {
            $recipe = new Recipe();
            $this->setRecipeParams($recipe, $dish, $product, $amount);
            $this->save($recipe);
        }

        return $recipe;
    }

    /**
     * @return Recipe[]
     */
    public function getDishRecipe(Dish $dish): array
    {
        return $this->recipeRepository->findBy(['dish' => $dish]);
    }

    public function getRecipeById(int $id): ?Recipe
    {
        return $this->recipeRepository->find($id);
    }

    public function updateRecipe(
        ?Recipe $recipe,
        ?Dish $dish = null,
        ?Product $product = null,
        ?float $amount = null,
    ): ?Recipe {
        if (!$recipe) {
            throw new UnprocessableEntityHttpException('Recipe does not exist');
        }
        $recipeDish = $recipe->getDish();
        $recipeDish->removeRecipe($recipe);
        $this->setRecipeParams($recipe, $dish, $product, $amount);
        $this->entityManager->flush();

        return $recipe;
    }

    public function deleteRecipe(?Recipe $recipe): bool
    {
        if (!$recipe) {
            return false;
        }
        $this->entityManager->remove($recipe);
        $recipe->getDish()->removeRecipe($recipe);
        $this->entityManager->flush();

        return true;
    }

    private function getRecipeByDishAndProduct(Dish $dish, Product $product): ?Recipe
    {
        return $this->recipeRepository->findOneBy(['dish' => $dish, 'product' => $product]);
    }

    private function setRecipeParams(Recipe $recipe, ?Dish $dish, ?Product $product, ?float $amount): void
    {
        $recipe->setDish($dish)->setProduct($product)->setAmount($amount);
        $dish?->addRecipe($recipe);
    }
}
