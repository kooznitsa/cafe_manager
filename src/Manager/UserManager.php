<?php

namespace App\Manager;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function create(string $name, string $password, string $email, string $address): User
    {
        $user = new User();

        $user->setName($name);
        $user->setPassword($password);
        $user->setEmail($email);
        $user->setAddress($address);
        $user->setCreatedAt();
        $user->setUpdatedAt();

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @return User[]
     */
    public function findUsersByAddress(string $address): array
    {
        return $this->entityManager->getRepository(User::class)->findBy(['address' => $address]);
    }
}
