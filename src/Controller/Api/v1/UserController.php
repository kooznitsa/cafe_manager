<?php

namespace App\Controller\Api\v1;

use App\DTO\Request\UserRequestDTO;
use App\DTO\Response\UserResponseDTO;
use App\Entity\User;
use App\Manager\UserManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\HttpKernel\Attribute\{MapQueryParameter, MapQueryString, MapRequestPayload};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/user')]
#[OA\Tag(name: 'users')]
class UserController extends AbstractController
{
    private const DEFAULT_PAGE = 0;
    private const DEFAULT_PER_PAGE = 20;

    public function __construct(
        private readonly UserManager $userManager,
    ) {
    }

    /**
     * Creates user.
     */
    #[Route(path: '', methods: ['POST'])]
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: UserRequestDTO::class)),
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function saveUserAction(#[MapRequestPayload] UserRequestDTO $dto): Response
    {
        $user = $this->userManager->saveUser($dto);
        [$data, $code] = [['success' => true, 'userId' => $user->getId()], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * Lists all users.
     */
    #[Route(path: '', methods: ['GET'])]
    #[OA\Parameter(name: 'page', description: 'Page', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'perPage', description: 'Per page', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of users is retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'users',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: UserResponseDTO::class))
                ),
            ],
            type: 'object'
        )
    )]
    public function getUsersAction(Request $request): Response
    {
        $perPage = $request->query->get('perPage') ?? self::DEFAULT_PER_PAGE;
        $page = $request->query->get('page') ?? self::DEFAULT_PAGE;
        $users = $this->userManager->getUsers($page, $perPage);
        $code = empty($users) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['users' => array_map(fn(User $user) => UserResponseDTO::fromEntity($user), $users)],
            $code,
        );
    }

    /**
     * Retrieves user by email.
     */
    #[Route(path: '/by-email/{user_email}', methods: ['GET'], priority: 2)]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User is retrieved successfully.',
        content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['default'])),
    )]
    public function getUserByEmailAction(
        #[MapEntity(mapping: ['user_email' => 'email'])] User $user,
    ): Response {
        return new JsonResponse(['user' => UserResponseDTO::fromEntity($user)], Response::HTTP_OK);
    }

    /**
     * Updates user.
     */
    #[Route(path: '', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'userId',
        description: 'User ID',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function updateUserAction(
        #[MapQueryParameter] int $userId,
        #[MapQueryString] UserRequestDTO $dto,
    ): Response {
        $user = $this->userManager->getUserById($userId);
        $result = $this->userManager->updateUser($user, $dto);

        return new JsonResponse(
            ['success' => $result !== null],
            ($result !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
        );
    }

    /**
     * Deletes user by ID.
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User is deleted successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function deleteUserByIdAction(
        #[MapEntity(mapping: ['id' => 'id'])] User $user,
    ): Response {
        $result = $this->userManager->deleteUser($user);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
