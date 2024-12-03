<?php

namespace Tests\Functional;

use App\DTO\Request\OrderRequestDTO;
use App\Entity\{Category, Dish, Order, Product, Recipe, User};
use App\Enum\Status;
use App\Service\OrderBuilderService;
use App\Tests\Support\FunctionalTester;
use Codeception\Example;

class OrderServiceCest
{
    private const USER_EMAIL = 'test@example.com';
    private const DISH_AMERICANO = 'Американо';
    private const DISH_BLACK_TEA = 'Черный чай';
    private const PRODUCT_COFFEE = 'Кофе';
    private const PRODUCT_WATER = 'Вода';
    private const PRODUCT_SUGAR = 'Сахар';
    private const PRODUCT_TEA = 'Чай';
    private const PRODUCT_AMOUNT = 1000.0;
    private const RECIPE_AMOUNT = 20.0;
    private CONST STATUS_CREATED = Status::Created->name;

    public function _before(FunctionalTester $I): void
    {
        $user = $I->have(User::class, ['email' => self::USER_EMAIL, 'roles' => ['ROLE_USER', 'ROLE_ADMIN']]);

        $americano = $I->have(
            Dish::class,
            [
                'name' => self::DISH_AMERICANO,
                'isAvailable' => true,
                'category' => $I->have(Category::class, ['name' => self::PRODUCT_COFFEE]),
            ],
        );
        $blackTea = $I->have(
            Dish::class,
            [
                'name' => self::DISH_BLACK_TEA,
                'isAvailable' => true,
                'category' => $I->have(Category::class, ['name' => self::PRODUCT_TEA]),
            ],
        );

        $coffee = $I->have(Product::class, ['name' => self::PRODUCT_COFFEE]);
        $water = $I->have(Product::class, ['name' => self::PRODUCT_WATER]);
        $sugar = $I->have(Product::class, ['name' => self::PRODUCT_SUGAR]);
        $tea = $I->have(Product::class, ['name' => self::PRODUCT_TEA, 'amount' => 0]);

        $I->have(Recipe::class, ['dish' => $americano, 'product' => $coffee]);
        $I->have(Recipe::class, ['dish' => $americano, 'product' => $water]);
        $I->have(Recipe::class, ['dish' => $americano, 'product' => $sugar]);

        $I->have(Recipe::class, ['dish' => $blackTea, 'product' => $tea]);
        $I->have(Recipe::class, ['dish' => $blackTea, 'product' => $water]);
        $I->have(Recipe::class, ['dish' => $blackTea, 'product' => $sugar]);

        $I->have(
            Order::class,
            ['dish' => $americano, 'user' => $user, 'status' => Status::Created, 'isDelivery' => true],
        );
    }

    public function _orderDataProvider(): array
    {
        $amount = self::PRODUCT_AMOUNT - self::RECIPE_AMOUNT;

        return [
            'order created successfully' => [
                'dish' => self::DISH_AMERICANO,
                'expected' => [self::DISH_AMERICANO, self::USER_EMAIL, self::STATUS_CREATED, true],
                'dish is available' => true,
                'product amounts' => [$amount, $amount, $amount],
            ],
            'order without enough ingredients not created' => [
                'dish' => self::DISH_BLACK_TEA,
                'expected' => null,
                'dish is available' => false,
                'product amounts' => [0.0, self::PRODUCT_AMOUNT, self::PRODUCT_AMOUNT],
            ],
        ];
    }

    /**
     * @dataProvider _orderDataProvider
     */
    public function testCreateOrder(FunctionalTester $I, Example $example): void
    {
        $user = $I->grabEntityFromRepository(User::class, ['email' => self::USER_EMAIL]);
        $I->amLoggedInAs($user);

        $dish = $I->grabEntityFromRepository(Dish::class, ['name' => $example['dish']]);
        $dto = new OrderRequestDTO($dish->getId(), $user->getId(), self::STATUS_CREATED, true);
        $order = $I->grabService(OrderBuilderService::class)->createOrderWithUserAndDish($dto);
        $actual = $order ? [
            $order->getDish()->getName(),
            $order->getUser()->getEmail(),
            $order->getStatus()->name,
            $order->getIsDelivery(),
        ] : null;
        $dish = $order ? $order->getDish() : $dish;
        $recipes = $I->grabEntitiesFromRepository(Recipe::class, ['dish' => $dish]);
        $recipeAmounts = array_map(fn(Recipe $recipe) => $recipe->getProduct()->getAmount(), $recipes);

        $I->assertSame($example['expected'], $actual);
        $I->assertSame($example['dish is available'], $dish->getIsAvailable());
        $I->assertSame($example['product amounts'], $recipeAmounts);
    }
}
