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

    public function create(string $name, Category $category, float $price, ?string $image): Dish
    {
        $dish = new Dish();

        $dish->setName($name);
        $dish->setCategory($category);
        $dish->setPrice($price);
        $dish->setImage($image);

        $this->entityManager->persist($dish);
        $this->entityManager->flush();

        return $dish;
    }

    /**
     * @return Dish[]
     */
    public function listDishes(): array
    {
        return $this->dishRepository->listDishes();
    }
}
