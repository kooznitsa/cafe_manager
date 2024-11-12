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

    public function saveDish(Request $request, string $fileDirectory): Dish
    {
        $category = $this->categoryManager->getCategoryById($request->request->get('categoryId'));
        $dish = $this->dishManager->getDishByNameAndCategory($request->request->get('name'), $category);

        if (!$dish) {
            $dish = new Dish();
            $dish = $this->setDishParams($dish, $request, $fileDirectory);
            $this->dishManager->save($dish);
        }

        return $dish;
    }

    public function updateDish(Request $request, string $fileDirectory): ?Dish
    {
        $dish = $this->dishManager->getDishById($request->query->get('dishId'));
        if ($dish !== null) {
            $dish = $this->setDishParams($dish, $request, $fileDirectory);
            $this->dishManager->save($dish);

            return $dish;
        }

        return null;
    }

    public function getDishParams(Request $request, string $fileDirectory): array
    {
        $name = $request->request->get('name') ?? $request->query->get('name');
        $price = $request->request->get('price') ?? $request->query->get('price');
        $categoryId = $request->request->get('categoryId') ?? $request->query->get('categoryId');
        $category = $categoryId ? $this->categoryManager->getCategoryById($categoryId) : null;
        $isAvailable = $request->request->get('isAvailable') ?? $request->query->get('isAvailable');
        $file = $request->files->get('image');
        $imageName = $file ? $this->fileService->uploadFile($file, $fileDirectory) : null;

        return [$name, $category, $price, $imageName, $isAvailable];
    }

    private function setDishParams(
        Dish $dish,
        Request $request,
        ?string $fileDirectory,
    ): Dish {
        [$name, $category, $price, $imageName, $isAvailable] = $this->getDishParams($request, $fileDirectory);

        $dish->setName($name)->setCategory($category)->setPrice($price)->setImage($imageName);

        if ($isAvailable !== null) {
            $dish->setIsAvailable($isAvailable);
        }

        return $dish;
    }
}
