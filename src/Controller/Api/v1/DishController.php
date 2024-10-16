<?php

namespace App\Controller\Api\v1;

use App\Entity\{Category, Dish};
use App\Manager\DishManager;
use App\Service\DishBuilderService;
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
                    required: ['name', 'category', 'price'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(
                            property: 'category',
                            ref: new Model(type: Category::class, groups: ['create']),
                            type: 'object',
                        ),
                        new OA\Property(property: 'price', type: 'float'),
                        new OA\Property(property: 'image', type: 'file'),
                    ]
                )
            ),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Dish is successfully created.',
        content: new OA\JsonContent(),
    )]
    public function saveDishAction(
        Request $request,
        #[Autowire('%kernel.project_dir%/public/uploads/images')] string $fileDirectory
    ): Response {
        $dishId = $this->dishBuilderService->createDishWithCategory($request, $fileDirectory);

        [$data, $code] = $dishId === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'dishId' => $dishId], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * Lists dishes by category.
     */
    #[Route(path: '/by-category/{category_id}', requirements: ['category_id' => '\d+'], methods: ['GET'])]
    public function getCategoryDishesAction(
        Request $request,
        #[MapEntity(mapping: ['category_id' => 'id'])] Category $category,
    ): Response {
        $dishes = $this->dishManager->getCategoryDishes($category);
        $code = empty($dishes) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['dishes' => array_map(static fn(Dish $dish) => $dish->toArray(), $dishes)],
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
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(name: 'name', description: 'Dish name', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'category', description: 'Dish category', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'price', description: 'Dish price', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'image', description: 'Dish image', in: 'query', schema: new OA\Schema(type: 'file'))]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated dish',
        content: new OA\JsonContent(),
    )]
    public function updateDishAction(
        Request $request,
        #[Autowire('%kernel.project_dir%/public/uploads/images')] string $fileDirectory
    ): Response {
        $dish = $this->dishBuilderService->updateDishWithCategory($request, $fileDirectory);

        return new JsonResponse(
            ['success' => $dish !== null],
            ($dish !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
        );
    }

    /**
     * Deletes dish by ID.
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteDishByIdAction(int $id): Response
    {
        $result = $this->dishManager->deleteDishById($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
