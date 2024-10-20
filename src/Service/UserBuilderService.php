<?php

namespace App\Service;

use App\DTO\ManageUserDTO;
use App\Entity\User;
use App\Form\Type\{CreateUserType, UpdateUserType};
use App\Manager\UserManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class UserBuilderService
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function createOrUpdateUser(Request $request, string $_route, ?int $id = null): array
    {
        if ($id) {
            $user = $this->userManager->getUserById($id);
            $dto = ManageUserDTO::fromEntity($user);
        }
        $form = $this->formFactory->create(
            $_route === 'create_user' ? CreateUserType::class : UpdateUserType::class,
            $dto ?? null,
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ManageUserDTO $userDto */
            $userDto = $form->getData();
            $this->userManager->saveUserFromDTO($user ?? new User(), $userDto);
        }

        return [$form, $user ?? null];
    }
}
