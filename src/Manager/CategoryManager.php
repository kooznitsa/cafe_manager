<?php

namespace App\Manager;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function create(string $name): Category
    {
        $category = new Category();

        $category->setName($name);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    public function listCategories(): array
    {
        return $this->categoryRepository->listCategories();
    }
}
