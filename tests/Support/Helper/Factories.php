<?php

namespace App\Tests\Support\Helper;

use App\Enum\Status;
use App\Entity\{Category, Dish, Order, Product, Purchase, Recipe, User};
use Codeception\Module;
use Codeception\Module\DataFactory;
use League\FactoryMuffin\Faker\Facade as Faker;

class Factories extends Module
{
    public function _beforeSuite($settings = []): void
    {
        /** @var DataFactory $factory */
        $factory = $this->getModule('DataFactory');

        $factory->_define(
            User::class,
            [
                'name' => Faker::userName()(),
                'password' => Faker::text(20)(),
                'email' => Faker::unique()->email()(),
                'address' => Faker::address()(),
                'roles' => ['ROLE_USER'],
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ]
        );
        $factory->_define(
            Product::class,
            [
                'name' => Faker::unique()->text(20)(),
                'unit' => Faker::text(5)(),
                'amount' => 1000.0,
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ]
        );
        $factory->_define(
            Category::class,
            [
                'name' => Faker::unique()->text(20)(),
            ]
        );
        $factory->_define(
            Dish::class,
            [
                'name' => Faker::text(20)(),
                'category' => 'entity|'.Category::class,
                'price' => 150.0,
                'image' => Faker::image()(),
                'isAvailable' => true,
            ]
        );
        $factory->_define(
            Recipe::class,
            [
                'dish' => 'entity|'.Dish::class,
                'product' => 'entity|'.Product::class,
                'amount' => 20.0,
            ]
        );
        $factory->_define(
            Order::class,
            [
                'dish' => 'entity|'.Dish::class,
                'user' => 'entity|'.User::class,
                'status' => Faker::randomElement(Status::cases()),
                'isDelivery' => true,
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ]
        );
        $factory->_define(
            Purchase::class,
            [
                'product' => 'entity|'.Product::class,
                'price' => 1000.0,
                'amount' => 200.0,
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ]
        );
    }
}
