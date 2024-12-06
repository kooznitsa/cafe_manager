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
    public const ESPRESSO = 'Эспрессо';
    public const FLAT_WHITE = 'Флэт-уайт';
    public const BLACK_TEA = 'Черный чай';
    public const GREEN_TEA = 'Зеленый чай';
    public const EARL_GREY = 'Эрл-грей';
    public const TAIGA_TEA = 'Таёжный сбор';
    public const DONUT = 'Пончик';
    public const CHEESECAKE = 'Чизкейк';
    public const COOKIE = 'Печенье';
    public const CUPCAKE = 'Капкейк';

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
            $this->makeDish($manager, $coffee, self::CAPPUCCINO, 200.00, 'coffee__cappuccino.jpg'),
        );
        $this->addReference(
            self::AMERICANO,
            $this->makeDish($manager, $coffee, self::AMERICANO, 180.00, 'coffee__americano.jpg'),
        );
        $this->addReference(
            self::ESPRESSO,
            $this->makeDish($manager, $coffee, self::ESPRESSO, 170.00, 'coffee__espresso.jpg'),
        );
        $this->addReference(
            self::FLAT_WHITE,
            $this->makeDish($manager, $coffee, self::FLAT_WHITE, 210.00, 'coffee__flat_white.jpg'),
        );
        $this->addReference(
            self::BLACK_TEA,
            $this->makeDish($manager, $tea, self::BLACK_TEA, 100.50, 'tea__black_tea.jpg'),
        );
        $this->addReference(
            self::GREEN_TEA,
            $this->makeDish($manager, $tea, self::GREEN_TEA, 80.00, 'tea__green_tea.jpg'),
        );
        $this->addReference(
            self::EARL_GREY,
            $this->makeDish($manager, $tea, self::EARL_GREY, 80.00, 'tea__earl_grey.jpg'),
        );
        $this->addReference(
            self::TAIGA_TEA,
            $this->makeDish($manager, $tea, self::TAIGA_TEA, 80.00, 'tea__taiga_tea.jpg'),
        );
        $this->addReference(
            self::DONUT,
            $this->makeDish($manager, $dessert, self::DONUT, 60.50, 'dessert__donut.jpg'),
        );
        $this->addReference(
            self::CHEESECAKE,
            $this->makeDish($manager, $dessert, self::CHEESECAKE, 250.00, 'dessert__cheesecake.jpg'),
        );
        $this->addReference(
            self::COOKIE,
            $this->makeDish($manager, $dessert, self::COOKIE, 250.00, 'dessert__cookie.jpg'),
        );
        $this->addReference(
            self::CUPCAKE,
            $this->makeDish($manager, $dessert, self::CUPCAKE, 250.00, 'dessert__cupcake.jpg'),
        );
        $manager->flush();
    }

    private function makeDish(
        ObjectManager $manager,
        Category $category,
        string $name,
        float $price,
        string $image,
    ): Dish {
        $dish = new Dish();
        $dish->setCategory($category)
            ->setName($name)
            ->setPrice($price)
            ->setImage($image)
            ->setIsAvailable(true);
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
