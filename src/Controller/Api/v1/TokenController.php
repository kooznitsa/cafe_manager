<?php

namespace App\Controller\Api\v1;

use App\Service\AuthService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/token')]
#[OA\Tag(name: 'token')]
class TokenController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    #[Route(path: '', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Generate new JWT token',
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['username', 'password'],
                    properties: [
                        new OA\Property(property: 'username', type: 'string'),
                        new OA\Property(property: 'password', type: 'string'),
                    ]
                )
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Token is generated successfully.',
        content: new OA\JsonContent(example: ['token' => 'string']),
    )]
    public function getTokenAction(Request $request): Response
    {
        $user = $request->getUser() ?? $request->request->get('username');
        $password = $request->getPassword() ?? $request->request->get('password');
        if (!$user || !$password) {
            return new JsonResponse(['message' => 'Authorization required'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->authService->isCredentialsValid($user, $password)) {
            return new JsonResponse(['message' => 'Invalid password or username'], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse(['token' => $this->authService->getToken($user)]);
    }
}
