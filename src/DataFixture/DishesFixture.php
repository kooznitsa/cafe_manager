<?php

namespace App\DataFixture;

use App\Entity\{Category, Dish};
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DishesFixture extends Fixture implements DependentFixtureInterface
{
    public const CAPPUCCINO = 'Капучино';
    public const AMERICANO = 'Американо';
    public const BLACK_TEA = 'Черный чай';
    public const GREEN_TEA = 'Зеленый чай';
    public const DONUT = 'Пышка';
    public const CAKE = 'Сметанник Фокина';

    public function load(ObjectManager $manager): void
    {
        /** @var Category $coffee */
        $coffee = $this->getReference(CategoriesFixture::COFFEE);
        /** @var Category $tea */
        $tea = $this->getReference(CategoriesFixture::TEA);
        /** @var Category $dessert */
        $dessert = $this->getReference(CategoriesFixture::DESSERT);

        $this->addReference(
            self::CAPPUCCINO,
            $this->makeDish($manager, $coffee, self::CAPPUCCINO, 200.00),
        );
        $this->addReference(
            self::AMERICANO,
            $this->makeDish($manager, $coffee, self::AMERICANO, 180.00),
        );
        $this->addReference(
            self::BLACK_TEA,
            $this->makeDish($manager, $tea, self::BLACK_TEA, 100.50),
        );
        $this->addReference(
            self::GREEN_TEA,
            $this->makeDish($manager, $tea, self::GREEN_TEA, 80.00),
        );
        $this->addReference(
            self::DONUT,
            $this->makeDish($manager, $dessert, self::DONUT, 60.50),
        );
        $this->addReference(
            self::CAKE,
            $this->makeDish($manager, $dessert, self::CAKE, 250.00),
        );
        $manager->flush();
    }

    private function makeDish(ObjectManager $manager, Category $category, string $name, float $price): Dish
    {
        $dish = new Dish();
        $dish->setCategory($category)->setName($name)->setPrice($price)->setIsAvailable(true);
        $manager->persist($dish);
        sleep(1);

        return $dish;
    }

    public function getDependencies(): array
    {
        return [
            CategoriesFixture::class,
        ];
    }
}
