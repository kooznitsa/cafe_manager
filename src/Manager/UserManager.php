<?php

namespace App\Manager;

use App\Entity\User;
use App\Repository\UserRepository;

class UserManager
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function createUser(string $name, string $password, string $email, string $address): User
    {
        return $this->userRepository->createUser($name, $password, $email, $address);
    }

    public function updateUserName(int $userId, string $name): ?User
    {
        return $this->userRepository->updateUserName($userId, $name);
    }

    public function findUser(int $id): ?User
    {
        return $this->userRepository->findUser($id);
    }

    /**
     * @return User[]
     */
    public function findUsersByName(string $name): array
    {
        return $this->userRepository->findUsersByName($name);
    }

    /**
     * @return User[]
     */
    public function findUsersByAddress(string $address): array
    {
        return $this->userRepository->findUsersByAddress($address);
    }
}
