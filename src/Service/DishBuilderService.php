<?php

namespace App\Service;

use App\Entity\Dish;
use App\Manager\{CategoryManager, DishManager};
use Symfony\Component\HttpFoundation\Request;

class DishBuilderService
{
    public function __construct(
        private readonly CategoryManager $categoryManager,
        private readonly DishManager $dishManager,
        private readonly FileService $fileService,
    ) {
    }

    public function createDishWithCategory(Request $request, string $fileDirectory): ?int
    {
        [$name, $categoryId, $price, $image] = $this->getDishParams($request, $fileDirectory);

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
        if ($category) {
            $category = $this->categoryManager->getCategoryById($category);
        }

        return $this->dishManager->updateDish($dishId, $name, $category, $price, $image);
    }

    public function getDishParams(Request $request, string $fileDirectory, string $requestMethod = 'POST'): array
    {
        $inputBag = $requestMethod === 'POST' ? $request->request : $request->query;

        $name = $inputBag->get('name');
        $price = $inputBag->get('price');
        $categoryId = $inputBag->get('categoryId');
        $file = $request->files->get('image');
        $imageName = $file ? $this->fileService->uploadFile($file, $fileDirectory) : null;

        return [$name, $categoryId, $price, $imageName];
    }
}
