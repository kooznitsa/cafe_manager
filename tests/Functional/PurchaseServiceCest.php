<?php

namespace Tests\Functional;

use App\DTO\Request\PurchaseRequestDTO;
use App\Entity\{Product, Purchase, User};
use App\Service\PurchaseBuilderService;
use App\Tests\Support\FunctionalTester;
use Codeception\Example;

class PurchaseServiceCest
{
    private const USER_EMAIL = 'test@example.com';
    private const PRODUCT_COFFEE = 'Кофе';
    private const PRODUCT_TEA = 'Чай';
    private const PRODUCT_AMOUNT = 1000.0;
    private const PURCHASE_PRICE = 1000.0;
    private const PURCHASE_AMOUNT = 200.0;

    public function _before(FunctionalTester $I): void
    {
        $I->have(User::class, ['email' => self::USER_EMAIL, 'roles' => ['ROLE_USER', 'ROLE_ADMIN']]);
        $product = $I->have(Product::class, ['name' => self::PRODUCT_COFFEE]);
        $I->have(
            Purchase::class,
            ['product' => $product, 'price' => self::PURCHASE_PRICE, 'amount' => self::PURCHASE_AMOUNT],
        );
    }

    public function _purchaseDataProvider(): array
    {
        return [
            'purchase created successfully' => [
                'product' => self::PRODUCT_COFFEE,
                'expected' => [self::PRODUCT_COFFEE, self::PURCHASE_PRICE, self::PURCHASE_AMOUNT],
            ],
            'purchase with wrong ID not created' => [
                'product' => self::PRODUCT_TEA,
                'expected' => null,
            ],
        ];
    }

    /**
     * @dataProvider _purchaseDataProvider
     */
    public function testCreatePurchase(FunctionalTester $I, Example $example): void
    {
        $user = $I->grabEntityFromRepository(User::class, ['email' => self::USER_EMAIL]);
        $I->amLoggedInAs($user);

        try {
            $product = $I->grabEntityFromRepository(Product::class, ['name' => $example['product']]);
        } catch (\Throwable) {
            $product = null;
        }

        $dto = new PurchaseRequestDTO(
            $product ? $product->getId() : null,
            self::PURCHASE_PRICE,
            self::PURCHASE_AMOUNT,
        );
        $result = $I->grabService(PurchaseBuilderService::class)->createPurchaseWithProduct($dto);
        $actual = $result ? [$result->getProduct()->getName(), $result->getPrice(), $result->getAmount()] : null;

        $I->assertSame($example['expected'], $actual);
        if ($product) {
            $I->assertSame(self::PURCHASE_AMOUNT * 2 + self::PRODUCT_AMOUNT, $product->getAmount());
        }
    }
}
