<?php

namespace App\Manager;

use App\Client\StatsdAPIClient;
use App\DTO\Request\UserRequestDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly StatsdAPIClient $statsdAPIClient,
    ) {
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function saveUser(UserRequestDTO $dto): User
    {
        $this->statsdAPIClient->increment('save_user_v1_attempt');

        $user = new User();
        $this->setUserParams($user, $dto);
        $this->save($user);

        return $user;
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

    public function updateUser(?User $user, UserRequestDTO $dto): ?User
    {
        if (!$user) {
            throw new UnprocessableEntityHttpException('User does not exist');
        }

        $this->setUserParams($user, $dto);
        $this->entityManager->flush();

        return $user;
    }

    public function deleteUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return true;
    }

    private function setUserParams(User $user, UserRequestDTO $dto): void
    {
        if ($dto->name !== null) {
            $user->setName($dto->name);
        }
        if ($dto->password !== null) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $dto->password));
        }
        if ($dto->email !== null) {
            $user->setEmail($dto->email);
        }
        if ($dto->address !== null) {
            $user->setAddress($dto->address);
        }
        if ($dto->roles !== null) {
            $user->setRoles($dto->roles);
        }
    }

    public function findUserByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        return $user;
    }

    public function updateUserToken(string $email): string|bool
    {
        $user = $this->findUserByEmail($email);
        if ($user === null) {
            return false;
        }
        $token = base64_encode(random_bytes(20));
        $user->setToken($token);
        $this->entityManager->flush();

        return $token;
    }

    public function findUserByToken(string $token): ?User
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['token' => $token]);

        return $user;
    }
}
