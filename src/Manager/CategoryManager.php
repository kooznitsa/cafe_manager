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

    public function saveCategory(string $name): ?int
    {
        $category = new Category();
        $category->setName($name);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category->getId();
    }

    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function updateCategory(int $categoryId, string $name): ?Category
    {
        /** @var Category $category */
        $category = $this->categoryRepository->find($categoryId);
        if (!$category) {
            return null;
        }

        $category->setName($name);
        $this->entityManager->flush();

        return $category;
    }

    public function deleteCategory(Category $category): bool
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return true;
    }

    public function deleteCategoryById(int $categoryId): bool
    {
        /** @var Category $category */
        $category = $this->categoryRepository->find($categoryId);
        if (!$category) {
            return false;
        }
        return $this->deleteCategory($category);
    }
}
