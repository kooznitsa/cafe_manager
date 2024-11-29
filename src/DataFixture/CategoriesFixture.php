<?php

namespace App\DataFixture;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoriesFixture extends Fixture
{
    public const COFFEE = 'Кофе';
    public const TEA = 'Чай';
    public const DESSERT = 'Десерты';

    public function load(ObjectManager $manager): void
    {
        $this->addReference(self::COFFEE, $this->makeCategory($manager, self::COFFEE));
        $this->addReference(self::TEA, $this->makeCategory($manager, self::TEA));
        $this->addReference(self::DESSERT, $this->makeCategory($manager, self::DESSERT));
        $manager->flush();
    }

    private function makeCategory(ObjectManager $manager, string $name): Category
    {
        $category = new Category();
        $category->setName($name);
        $manager->persist($category);

        return $category;
    }
}
