<?php

namespace App\Manager;

use App\DTO\Request\UserRequestDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UserManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function saveUser(User $user, UserRequestDTO $manageUserDTO): ?int
    {
        $this->setUserParams($user, $manageUserDTO);
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

    public function updateUser(int $userId, UserRequestDTO $manageUserDTO): ?User
    {
        /** @var User $user */
        $user = $this->getUserById($userId);

        if ($user === null) {
            throw new UnprocessableEntityHttpException('User does not exist');
        }

        $this->setUserParams($user, $manageUserDTO);
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

    private function setUserParams(User $user, UserRequestDTO $dto): void
    {
        $user->setName($dto->name);
        if ($dto->password !== null) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $dto->password));
        }
        $user->setEmail($dto->email)
            ->setAddress($dto->address)
            ->setRoles($dto->roles);
    }
}
