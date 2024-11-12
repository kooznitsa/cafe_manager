<?php

namespace App\Manager;

use App\DTO\Request\CategoryRequestDTO;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CategoryManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function saveCategory(CategoryRequestDTO $dto): ?Category
    {
        $name = $dto->name;
        $category = $this->getCategoryByName($name);

        if (!$category) {
            $category = new Category();
            $category->setName($name);
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        }

        return $category;
    }

    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }

    public function getCategoryByName(string $name): ?Category
    {
        return $this->categoryRepository->findOneBy(['name' => $name]);
    }

    public function updateCategory(?Category $category, CategoryRequestDTO $dto): ?Category
    {
        if (!$category) {
            throw new UnprocessableEntityHttpException('Category does not exist');
        }

        $category->setName($dto->name);
        $this->entityManager->flush();

        return $category;
    }

    public function deleteCategory(?Category $category): bool
    {
        if (!$category) {
            return false;
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return true;
    }
}
