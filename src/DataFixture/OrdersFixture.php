<?php

namespace App\DataFixture;

use App\Entity\{Dish, Order, User};
use App\Enum\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrdersFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var User $tiger */
        $tiger = $this->getReference(UsersFixture::TIGER);
        /** @var User $cat */
        $cat = $this->getReference(UsersFixture::CAT);
        /** @var Dish $cappuccino */
        $cappuccino = $this->getReference(DishesFixture::CAPPUCCINO);
        /** @var Dish $americano */
        $americano = $this->getReference(DishesFixture::AMERICANO);
        /** @var Dish $blackTea */
        $blackTea = $this->getReference(DishesFixture::BLACK_TEA);
        /** @var Dish $greenTea */
        $greenTea = $this->getReference(DishesFixture::GREEN_TEA);
        /** @var Dish $donut */
        $donut = $this->getReference(DishesFixture::DONUT);
        /** @var Dish $cake */
        $cake = $this->getReference(DishesFixture::CHEESECAKE);

        $this->makeOrder($manager, $cappuccino, $tiger);
        $this->makeOrder($manager, $blackTea, $tiger);
        $this->makeOrder($manager, $donut, $tiger);
        $this->makeOrder($manager, $americano, $cat);
        $this->makeOrder($manager, $greenTea, $cat);
        $this->makeOrder($manager, $cake, $cat);

        $manager->flush();
    }

    private function makeOrder(ObjectManager $manager, Dish $dish, User $user): void
    {
        $order = new Order();
        $order->setDish($dish)->setUser($user)->setStatus(Status::Paid)->setIsDelivery(true);
        $manager->persist($dish);
    }

    public function getDependencies(): array
    {
        return [
            UsersFixture::class,
            DishesFixture::class,
        ];
    }
}
