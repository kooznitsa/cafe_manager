<?php

namespace App\Manager;

use App\Entity\{Category, Dish};
use App\Repository\DishRepository;
use Doctrine\ORM\EntityManagerInterface;

class DishManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DishRepository $dishRepository,
    ) {
    }

    public function saveDish(string $name, Category $category, float $price, ?string $image): ?int
    {
        $dish = $this->getDishByNameAndCategory($name, $category);

        if (!$dish) {
            $dish = new Dish();
            $this->setDishParams($dish, $name, $category, $price, $image);
            $category->addDish($dish);
            $this->entityManager->persist($dish);
            $this->entityManager->flush();
        }

        return $dish->getId();
    }

    /**
     * @return Dish[]
     */
    public function getDishes(): array
    {
        return $this->dishRepository->findAll();
    }

    public function getDishById(int $id): ?Dish
    {
        return $this->dishRepository->find($id);
    }

    public function getDishByNameAndCategory(string $name, Category $category): ?Dish
    {
        return $this->dishRepository->findOneBy(['name' => $name, 'category' => $category]);
    }

    /**
     * @return Dish[]
     */
    public function getCategoryDishes(Category $category): array
    {
        return $this->dishRepository->findBy(['category' => $category]);
    }

    public function updateDish(
        int $dishId,
        ?string $name = null,
        ?Category $category = null,
        ?float $price = null,
        ?string $image = null,
    ): ?Dish {
        /** @var Dish $dish */
        $dish = $this->getDishById($dishId);
        if (!$dish) {
            return null;
        }
        $categoryDish = $dish->getCategory();
        $categoryDish->removeDish($dish);
        $this->setDishParams($dish, $name, $category, $price, $image);
        $category?->addDish($dish);
        $this->entityManager->flush();

        return $dish;
    }

    public function deleteDish(Dish $dish): bool
    {
        $this->entityManager->remove($dish);
        $dish->getCategory()->removeDish($dish);
        $this->entityManager->flush();

        return true;
    }

    public function deleteDishById(int $dishId): bool
    {
        /** @var Dish $dish */
        $dish = $this->getDishById($dishId);
        if (!$dish) {
            return false;
        }
        return $this->deleteDish($dish);
    }

    private function setDishParams(
        Dish $dish,
        ?string $name,
        ?Category $category,
        ?float $price,
        ?string $image,
    ): void {
        $dish->setName($name);
        $dish->setCategory($category);
        $dish->setPrice($price);
        $dish->setImage($image);
    }
}
