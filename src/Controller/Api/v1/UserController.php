<?php

namespace App\Controller\Api\v1;

use App\Entity\User;
use App\Manager\UserManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{InputBag, JsonResponse, Request, Response};
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
                schema: new OA\Schema(ref: new Model(type: User::class, groups: ['create']))
            ),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'User is successfully created.',
        content: new OA\JsonContent(),
    )]
    public function saveUserAction(Request $request): Response
    {
        $userId = $this->userManager->saveUser(...$this->getUserParams($request->request));
        [$data, $code] = $userId === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'userId' => $userId], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * Lists all users.
     */
    #[Route(path: '', methods: ['GET'])]
    public function getUsersAction(Request $request): Response
    {
        $perPage = $request->query->get('perPage') ?? self::DEFAULT_PER_PAGE;
        $page = $request->query->get('page') ?? self::DEFAULT_PAGE;
        $users = $this->userManager->getUsers($page, $perPage);
        $code = empty($users) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(['users' => array_map(static fn(User $user) => $user->toArray(), $users)], $code);
    }

    /**
     * Retrieves user by name.
     */
    #[Route(path: '/by-name/{user_name}', methods: ['GET'], priority: 2)]
    public function getUserByNameAction(
        #[MapEntity(mapping: ['user_name' => 'name'])] User $user,
    ): Response
    {
        return new JsonResponse(['user' => $user->toArray()], Response::HTTP_OK);
    }

    /**
     * Updates user.
     */
    #[Route(path: '', methods: ['PATCH'])]
    #[OA\Parameter(name: 'userId', description: 'User ID', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'name', description: 'User name', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'password', description: 'User password', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'email', description: 'User email', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'address', description: 'User address', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated user',
        content: new OA\JsonContent(),
    )]
    public function updateUserAction(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $result = $this->userManager->updateUser($userId, ...$this->getUserParams($request->query));

        return new JsonResponse(
            ['success' => $result !== null],
            ($result !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
        );
    }

    /**
     * Deletes user by ID.
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteUserByIdAction(int $id): Response
    {
        $result = $this->userManager->deleteUserById($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    private function getUserParams(InputBag $input): array
    {
        $name = $input->get('name');
        $password = $input->get('password');
        $email = $input->get('email');
        $address = $input->get('address');

        return [$name, $password, $email, $address];
    }
}
