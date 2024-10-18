<?php

namespace App\Controller\Api\v1;

use App\Entity\{Dish, Recipe};
use App\Manager\RecipeManager;
use App\Service\RecipeBuilderService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/recipe')]
#[OA\Tag(name: 'recipes')]
class RecipeController extends AbstractController
{
    public function __construct(
        private readonly RecipeManager $recipeManager,
        private readonly RecipeBuilderService $recipeBuilderService,
    ) {
    }

    /**
     * Creates recipe.
     */
    #[Route(path: '', methods: ['POST'])]
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['dishId', 'productId', 'amount'],
                    properties: [
                        new OA\Property(property: 'dishId', type: 'integer'),
                        new OA\Property(property: 'productId', type: 'integer'),
                        new OA\Property(property: 'amount', type: 'float'),
                    ]
                )
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Recipe is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function saveDishAction(Request $request): Response
    {
        $recipeId = $this->recipeBuilderService->createRecipeWithDishAndProduct($request);

        [$data, $code] = $recipeId === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'recipeId' => $recipeId], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * Lists recipe items by dish.
     */
    #[Route(path: '/by-dish/{dish_id}', requirements: ['dish_id' => '\d+'], methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of recipe items is retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'recipe',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Recipe::class, groups: ['default']))
                ),
            ],
            type: 'object'
        )
    )]
    public function getDishRecipesAction(
        Request $request,
        #[MapEntity(mapping: ['dish_id' => 'id'])] Dish $dish,
    ): Response {
        $recipes = $this->recipeManager->getDishRecipe($dish);
        $code = empty($recipes) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['recipe' => array_map(static fn(Recipe $recipe) => $recipe->toArray(), $recipes)],
            $code,
        );
    }

    /**
     * Updates recipe.
     */
    #[Route(path: '', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'recipeId',
        description: 'Recipe ID',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(name: 'dishId', description: 'Dish ID', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'productId', description: 'Product ID', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'amount', description: 'Amount', in: 'query', schema: new OA\Schema(type: 'float'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Recipe is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function updateDishAction(Request $request): Response
    {
        $recipe = $this->recipeBuilderService->updateRecipeWithDishAndProduct($request);

        return new JsonResponse(
            ['success' => $recipe !== null],
            ($recipe !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
        );
    }

    /**
     * Deletes recipe by ID.
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Recipe is deleted successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function deleteRecipeByIdAction(int $id): Response
    {
        $result = $this->recipeManager->deleteRecipeById($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
