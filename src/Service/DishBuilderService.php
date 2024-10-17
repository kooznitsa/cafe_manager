<?php

namespace App\Service;

use App\Entity\{Category, Dish};
use App\Manager\{CategoryManager, DishManager};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

class DishBuilderService
{
    public function __construct(
        private readonly CategoryManager $categoryManager,
        private readonly DishManager $dishManager,
    ) {
    }

    public function createDishWithCategory(Request $request, string $fileDirectory): ?int
    {
        [$name, $category, $price, $image] = $this->getDishParams($request, $fileDirectory);

        $categoryId = $this->categoryManager->saveCategory($category);
        if ($categoryId) {
            $category = $this->categoryManager->getCategoryById($categoryId);

            return $this->dishManager->saveDish($name, $category, $price, $image);
        }
        return null;
    }

    public function updateDishWithCategory(Request $request, string $fileDirectory): ?Dish
    {
        [$name, $category, $price, $image] = $this->getDishParams($request, $fileDirectory, 'PATCH');
        $dishId = $request->query->get('dishId');

        return $this->dishManager->updateDish($dishId, $name, $category, $price, $image);
    }

    public function getDishParams(Request $request, string $fileDirectory, string $requestMethod = 'POST'): array
    {
        $inputBag = $requestMethod === 'POST' ? $request->request : $request->query;

        $name = $inputBag->get('name');
        $price = $inputBag->get('price');

        $file = $request->files->get('image');
        $imageName = $file ? $this->uploadFile($file, $fileDirectory) : null;

        $category = $inputBag->get('category');
        if ($category) {
            $categoryJson = json_decode($category, true);
            $category = new Category();
            $category->setName($categoryJson['name']);
        }

        return [$name, $category, $price, $imageName];
    }

    private function uploadFile(File $file, string $fileDirectory): string
    {
        $imageName = $_FILES['image']['name'];

        try {
            $file->move($fileDirectory, $imageName);
        } catch (FileException $e) {
            var_dump("File upload failed: $e.");
        }

        return $imageName;
    }
}
