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

    public function save(Dish $dish): void
    {
        $this->entityManager->persist($dish);
        $this->entityManager->flush();
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

    public function deleteDish(?Dish $dish): bool
    {
        if (!$dish) {
            return false;
        }

        $this->entityManager->remove($dish);
        $dish->getCategory()->removeDish($dish);
        $this->entityManager->flush();

        return true;
    }
}
