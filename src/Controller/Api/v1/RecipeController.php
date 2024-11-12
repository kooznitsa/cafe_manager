<?php

namespace App\Controller\Api\v1;

use App\DTO\Request\RecipeRequestDTO;
use App\DTO\Response\RecipeResponseDTO;
use App\Entity\{Dish, Recipe};
use App\Manager\RecipeManager;
use App\Service\RecipeBuilderService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Response};
use Symfony\Component\HttpKernel\Attribute\{MapQueryParameter, MapQueryString, MapRequestPayload};
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
                schema: new OA\Schema(ref: new Model(type: RecipeRequestDTO::class)),
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Recipe is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function saveDishAction(#[MapRequestPayload] RecipeRequestDTO $dto): Response
    {
        $recipe = $this->recipeBuilderService->createRecipeWithDishAndProduct($dto);
        [$data, $code] = [['success' => true, 'recipeId' => $recipe->getId()], Response::HTTP_OK];

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
                    items: new OA\Items(ref: new Model(type: RecipeResponseDTO::class)),
                ),
            ],
            type: 'object'
        )
    )]
    public function getDishRecipesAction(
        #[MapEntity(mapping: ['dish_id' => 'id'])] Dish $dish,
    ): Response {
        $recipes = $this->recipeManager->getDishRecipe($dish);
        $code = empty($recipes) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['recipe' => array_map(static fn(Recipe $recipe) => RecipeResponseDTO::fromEntity($recipe), $recipes)],
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
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Recipe is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function updateRecipeAction(
        #[MapQueryParameter] int $recipeId,
        #[MapQueryString] RecipeRequestDTO $dto,
    ): Response {
        $recipe = $this->recipeManager->getRecipeById($recipeId);
        $result = $this->recipeBuilderService->updateRecipeWithDishAndProduct($recipe, $dto);

        return new JsonResponse(
            ['success' => $result !== null],
            ($result !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
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
    public function deleteRecipeByIdAction(
        #[MapEntity(mapping: ['id' => 'id'])] Recipe $recipe,
    ): Response {
        $result = $this->recipeManager->deleteRecipe($recipe);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
