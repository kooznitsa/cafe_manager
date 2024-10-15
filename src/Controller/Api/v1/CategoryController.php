<?php

namespace App\Controller\Api\v1;

use App\Entity\Category;
use App\Manager\CategoryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/category')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryManager $categoryManager,
    ) {
    }

    #[Route(path: '', methods: ['POST'])]
    public function saveCategoryAction(Request $request): Response
    {
        $categoryId = $this->categoryManager->savecategory($request->request->get('name'));
        [$data, $code] = $categoryId === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'categoryId' => $categoryId], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    #[Route(path: '', methods: ['GET'])]
    public function getCategoriesAction(Request $request): Response
    {
        $categories = $this->categoryManager->getCategories();
        $code = empty($categories) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['categories' => array_map(static fn(Category $category) => $category->toArray(), $categories)],
            $code,
        );
    }

    #[Route(path: '', methods: ['PATCH'])]
    public function updateCategoryAction(Request $request): Response
    {
        $categoryId = $request->query->get('categoryId');
        $name = $request->query->get('name');
        $result = $this->categoryManager->updateCategory($categoryId, $name);

        return new JsonResponse(
            ['success' => $result !== null],
            ($result !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
        );
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteCategoryByIdAction(int $id): Response
    {
        $result = $this->categoryManager->deleteCategoryById($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
