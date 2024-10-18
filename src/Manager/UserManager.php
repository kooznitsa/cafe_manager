<?php

namespace App\Manager;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
    ) {
    }

    public function saveUser(string $name, string $password, string $email, string $address): ?int
    {
        $user = new User();
        $this->setUserParams($user, $name, $password, $email, $address);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user->getId();
    }

    /**
     * @return User[]
     */
    public function getUsers(int $page, int $perPage): array
    {
        return $this->userRepository->getUsers($page, $perPage);
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function updateUser(
        int $userId,
        ?string $name = null,
        ?string $password = null,
        ?string $email = null,
        ?string $address = null,
    ): ?User {
        /** @var User $user */
        $user = $this->getUserById($userId);
        if (!$user) {
            return null;
        }

        $this->setUserParams($user, $name, $password, $email, $address);
        $this->entityManager->flush();

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return true;
    }

    public function deleteUserById(int $userId): bool
    {
        /** @var User $user */
        $user = $this->getUserById($userId);
        if (!$user) {
            return false;
        }
        return $this->deleteUser($user);
    }

    private function setUserParams(
        User $user,
        ?string $name,
        ?string $password,
        ?string $email,
        ?string $address,
    ): void {
        $user->setName($name);
        $user->setPassword($password);
        $user->setEmail($email);
        $user->setAddress($address);
    }
}
