<?php

namespace App\Controller;

use App\Service\UserBuilderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserBuilderService $userBuilderService,
    ) {
    }

    #[Route(path: '/create-user', name: 'create_user', methods: ['GET', 'POST'])]
    #[Route(path: '/update-user/{id}', name: 'update-user', methods: ['GET', 'PATCH'])]
    public function manageUserAction(Request $request, string $_route, ?int $id = null): Response
    {
        [$form, $user, $userId] = $this->userBuilderService->createOrUpdateUser($request, $_route, $id);

        if ($userId) {
            return $this->redirectToRoute('update-user', ['id' => $userId]);
        }

        return $this->render('manageUser.html.twig', [
            'form' => $form->createView(),
            'isNew' => $_route === 'create_user',
            'user' => $user ?? null,
        ]);
    }
}
