<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';

    public function load(ObjectManager $manager): void
    {
        UserFactory::new()
            ->with([
                'email' => 'superadmin@example.com',
                'plainPassword' => 'adminpass',
            ])
            ->create();
    }
}
