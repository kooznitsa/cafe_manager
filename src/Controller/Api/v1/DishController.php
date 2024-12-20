<?php

namespace App\Controller\Api\v1;

use App\DTO\Response\DishResponseDTO;
use App\Entity\{Category, Dish};
use App\Manager\DishManager;
use App\Service\{DishBuilderService, FileService};
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/dish')]
#[OA\Tag(name: 'dishes')]
class DishController extends AbstractController
{
    public function __construct(
        private readonly DishManager $dishManager,
        private readonly DishBuilderService $dishBuilderService,
        private readonly FileService $fileService,
    ) {
    }

    /**
     * Creates dish.
     */
    #[Route(path: '', methods: ['POST'])]
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'categoryId', 'price'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'categoryId', type: 'integer'),
                        new OA\Property(property: 'price', type: 'float'),
                        new OA\Property(property: 'image', type: 'file'),
                    ]
                )
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Dish is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function saveDishAction(
        Request $request,
        #[Autowire('%kernel.project_dir%/public/uploads/images')] string $fileDirectory,
    ): Response {
        $dish = $this->dishBuilderService->saveDish($request, $fileDirectory);
        [$data, $code] = [['success' => true, 'dishId' => $dish->getId()], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * Lists dishes by category.
     */
    #[Route(path: '/by-category/{category_id}', requirements: ['category_id' => '\d+'], methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of dishes is retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'dishes',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: DishResponseDTO::class)),
                ),
            ],
            type: 'object'
        )
    )]
    public function getCategoryDishesAction(
        #[MapEntity(mapping: ['category_id' => 'id'])] Category $category,
    ): Response {
        $dishes = $this->dishManager->getCategoryDishes($category);
        $code = empty($dishes) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['dishes' => array_map(fn(Dish $dish) => DishResponseDTO::fromEntity($dish), $dishes)],
            $code,
        );
    }

    /**
     * Updates dish.
     */
    #[Route(path: '', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'dishId',
        description: 'Dish ID',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(name: 'name', description: 'Dish name', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'categoryId', description: 'Category ID', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'price', description: 'Dish price', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'image', description: 'Dish image', in: 'query', schema: new OA\Schema(type: 'file'))]
    #[OA\Parameter(
        name: 'isAvailable',
        description: 'Dish is available',
        in: 'query',
        schema: new OA\Schema(type: 'integer', enum: [0, 1]),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Dish is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function updateDishAction(
        Request $request,
        #[Autowire('%kernel.project_dir%/public/uploads/images')] string $fileDirectory
    ): Response {
        $dish = $this->dishBuilderService->updateDish($request, $fileDirectory);

        return new JsonResponse(
            ['success' => $dish !== null],
            ($dish !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
        );
    }

    /**
     * Deletes dish by ID.
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Dish is deleted successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function deleteDishByIdAction(
        #[MapEntity(mapping: ['id' => 'id'])] Dish $dish,
        #[Autowire('%kernel.project_dir%/public/uploads/images')] string $fileDirectory,
    ): Response {
        $this->fileService->removeFile($fileDirectory, $dish->getImage());
        $result = $this->dishManager->deleteDish($dish);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
