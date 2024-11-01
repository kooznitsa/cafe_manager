<?php

namespace App\Controller\Api\v1;

use App\DTO\Request\CategoryRequestDTO;
use App\DTO\Response\CategoryResponseDTO;
use App\Entity\Category;
use App\Manager\CategoryManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/category')]
#[OA\Tag(name: 'categories')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryManager $categoryManager,
    ) {
    }

    /**
     * Creates category.
     */
    #[Route(path: '', methods: ['POST'])]
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: CategoryRequestDTO::class)),
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Category is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function saveCategoryAction(Request $request): Response
    {
        $categoryId = $this->categoryManager->saveCategory($request->request->get('name'));
        [$data, $code] = $categoryId === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'categoryId' => $categoryId], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * Lists all categories.
     */
    #[Route(path: '', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of categories is retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'categories',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: CategoryResponseDTO::class)),
                ),
            ],
            type: 'object'
        )
    )]
    public function getCategoriesAction(Request $request): Response
    {
        $categories = $this->categoryManager->getCategories();
        $code = empty($categories) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['users' => array_map(fn(Category $category) => CategoryResponseDTO::fromEntity($category), $categories)],
            $code,
        );
    }

    /**
     * Updates category.
     */
    #[Route(path: '', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'categoryId',
        description: 'Category ID',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(name: 'name', description: 'Category name', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Category is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
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

    /**
     * Deletes category by ID.
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Category is deleted successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function deleteCategoryByIdAction(int $id): Response
    {
        $result = $this->categoryManager->deleteCategoryById($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
