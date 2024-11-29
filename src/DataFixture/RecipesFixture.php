<?php

namespace App\DataFixture;

use App\Entity\{Dish, Product, Recipe};
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RecipesFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Dish $cappuccino */
        $cappuccino = $this->getReference(DishesFixture::CAPPUCCINO);
        /** @var Dish $americano */
        $americano = $this->getReference(DishesFixture::AMERICANO);
        /** @var Dish $blackTea */
        $blackTea = $this->getReference(DishesFixture::BLACK_TEA);
        /** @var Dish $greenTea */
        $greenTea = $this->getReference(DishesFixture::GREEN_TEA);

        /** @var Product $coffee */
        $coffee = $this->getReference(ProductsFixture::COFFEE);
        /** @var Product $tea */
        $tea = $this->getReference(ProductsFixture::TEA);
        /** @var Product $water */
        $water = $this->getReference(ProductsFixture::WATER);
        /** @var Product $sugar */
        $sugar = $this->getReference(ProductsFixture::SUGAR);
        /** @var Product $milk */
        $milk = $this->getReference(ProductsFixture::MILK);

        $this->makeRecipe($manager, $cappuccino, $coffee, 50.00);
        $this->makeRecipe($manager, $cappuccino, $water, 150.00);
        $this->makeRecipe($manager, $cappuccino, $milk, 80.00);
        $this->makeRecipe($manager, $cappuccino, $sugar, 15.00);

        $this->makeRecipe($manager, $americano, $coffee, 50.00);
        $this->makeRecipe($manager, $americano, $water, 150.00);
        $this->makeRecipe($manager, $americano, $sugar, 15.00);

        $this->makeRecipe($manager, $blackTea, $tea, 20.00);
        $this->makeRecipe($manager, $blackTea, $sugar, 15.00);
        $this->makeRecipe($manager, $blackTea, $water, 250.00);

        $this->makeRecipe($manager, $greenTea, $tea, 20.00);
        $this->makeRecipe($manager, $greenTea, $water, 250.00);

        $manager->flush();
    }

    private function makeRecipe(ObjectManager $manager, Dish $dish, Product $product, float $amount): void
    {
        $recipe = new Recipe();
        $recipe->setDish($dish)->setProduct($product)->setAmount($amount);
        $manager->persist($recipe);
    }

    public function getDependencies(): array
    {
        return [
            DishesFixture::class,
            ProductsFixture::class,
        ];
    }
}
