<?php

namespace App\DataFixture;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixture extends Fixture
{
    public const TIGER = 'tiger@gmail.com';
    public const CAT = 'cat@gmail.com';

    public function __construct(
        public UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->addReference(self::TIGER, $this->makeUser($manager, self::TIGER));
        $this->addReference(self::CAT, $this->makeUser($manager, self::CAT));
        $manager->flush();
    }

    private function makeUser(ObjectManager $manager, string $email): User
    {
        $user = new User();
        $password = $this->hasher->hashPassword($user, "{$email}1957Test");
        $user->setName(ucfirst(explode("@", $email)[0]))
            ->setEmail($email)
            ->setPassword($password)
            ->setAddress('Сортавала')
            ->setRoles(['ROLE_USER']);

        $manager->persist($user);

        return $user;
    }
}
