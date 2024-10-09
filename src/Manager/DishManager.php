<?php

namespace App\Manager;

use App\Entity\{Category, Dish};
use Doctrine\ORM\EntityManagerInterface;

class DishManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
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

    public function listDishesByCategory(Category $category): array
    {
        return $this->entityManager->getRepository(Dish::class)->findByCategory($category);
    }

    public function listDishes(): array
    {
        return $this->entityManager->getRepository(Dish::class)->findAll();
    }
}
