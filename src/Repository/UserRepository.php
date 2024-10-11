<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($registry, User::class);
    }

    public function createUser(string $name, string $password, string $email, string $address): User
    {
        $user = new User();

        $user->setName($name);
        $user->setPassword($password);
        $user->setEmail($email);
        $user->setAddress($address);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function updateUserName(int $userId, string $name): ?User
    {
        $user = $this->findUser($userId);
        if (!($user instanceof User)) {
            return null;
        }
        $user->setName($name);
        $this->entityManager->flush();

        return $user;
    }

    public function findUser(int $id): ?User
    {
        $user = $this->find($id);

        return $user instanceof User ? $user : null;
    }

    /**
     * @return User[]
     */
    public function findUsersByName(string $name): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->from(User::class, 'u')
            ->andWhere($queryBuilder->expr()->like('u.name',':userName'))
            ->setParameter('userName', "%$name%");

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return User[]
     */
    public function findUsersByAddress(string $address): array
    {
        return $this->findBy(['address' => $address]);
    }
}
