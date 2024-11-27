<?php

namespace App\DataFixture;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductsFixture extends Fixture
{
    public const COFFEE = 'Кофе в зернах';
    public const TEA = 'Чай в листьях';
    public const WATER = 'Вода';
    public const SUGAR = 'Сахар';
    public const MILK = 'Молоко';
    public const INITIAL_AMOUNT = 1000.00;

    public function load(ObjectManager $manager): void
    {
        $this->addReference(
            self::COFFEE,
            $this->makeProduct($manager, self::COFFEE, 'г', self::INITIAL_AMOUNT),
        );
        $this->addReference(
            self::TEA,
            $this->makeProduct($manager, self::TEA, 'г', self::INITIAL_AMOUNT),
        );
        $this->addReference(
            self::WATER,
            $this->makeProduct($manager, self::WATER, 'мл', self::INITIAL_AMOUNT),
        );
        $this->addReference(
            self::SUGAR,
            $this->makeProduct($manager, self::SUGAR, 'г', self::INITIAL_AMOUNT),
        );
        $this->addReference(
            self::MILK,
            $this->makeProduct($manager, self::MILK, 'мл', self::INITIAL_AMOUNT),
        );
        $manager->flush();
    }

    private function makeProduct(ObjectManager $manager, string $name, string $unit, float $amount): Product
    {
        $product = new Product();
        $product->setName($name)->setUnit($unit)->setAmount($amount);
        $manager->persist($product);

        return $product;
    }
}
