<?php

namespace App\Service;

use App\DTO\ManageUserDTO;
use App\Entity\User;
use App\Form\Type\{User\CreateUserType, User\UpdateUserType};
use App\Manager\{OrderManager, UserManager};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class UserBuilderService
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderManager $orderManager,
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

//        if ($form->isSubmitted() && $form->isValid()) {
        if ($form->isSubmitted()) {
            /** @var ManageUserDTO $userDto */
            $userDto = $form->getData();
            $userId = $this->saveUserFromDTO($user ?? new User(), $userDto);
        }

        return [$form, $user ?? null, $userId ?? null];
    }

    public function saveUserFromDTO(User $user, ManageUserDTO $manageUserDTO): ?int
    {
        $user->setName($manageUserDTO->name)
            ->setPassword($manageUserDTO->password)
            ->setEmail($manageUserDTO->email)
            ->setAddress($manageUserDTO->address);
        foreach ($manageUserDTO->orders as $order) {
            $newOrder = $this->orderManager->getOrderById($order['id']);
            $newOrder->setDish($order['dish'])->setStatus($order['status'])->setIsDelivery($order['isDelivery']);
            $user->addOrder($newOrder);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user->getId();
    }
}
