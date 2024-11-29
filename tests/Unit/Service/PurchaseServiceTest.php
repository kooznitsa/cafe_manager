<?php

namespace UnitTest\Service;

use App\DTO\Request\PurchaseRequestDTO;
use App\Factory\{ProductFactory, PurchaseFactory};
use App\Service\PurchaseBuilderService;
use App\Manager\{ProductManager, PurchaseManager};
use App\Repository\{ProductRepository, PurchaseRepository};
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\{Factories, ResetDatabase};

class PurchaseServiceTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private static EntityManagerInterface|MockInterface $entityManager;

    private const PRICE = 1000.0;
    private const PURCHASE_AMOUNT = 200.0;
    private const PRODUCT_AMOUNT = 1000.0;
    private const PRODUCT_NAME = 'Кофе';
    private const WRONG_PRODUCT_ID = 3;

    public function purchaseDataProvider(): array
    {
        return [
            'purchase created successfully' => [
                new PurchaseRequestDTO(
                    productId: 1,
                    price: self::PRICE,
                    amount: self::PURCHASE_AMOUNT,
                ),
                [self::PRODUCT_NAME, self::PRICE, self::PURCHASE_AMOUNT],
            ],
            'purchase with wrong ID not created' => [
                new PurchaseRequestDTO(
                    productId: 3,
                    price: self::PRICE,
                    amount: self::PURCHASE_AMOUNT,
                ),
                null,
            ],
        ];
    }

    /**
     * @dataProvider purchaseDataProvider
     */
    public function testCreatePurchase(PurchaseRequestDTO $dto, ?array $expected): void
    {
        [$product, $purchaseService] = $this->preparePurchaseService();
        $result = $purchaseService->createPurchaseWithProduct($dto);
        $actual = $result ? [$result->getProduct()->getName(), $result->getPrice(), $result->getAmount()] : null;

        self::assertEquals($expected, $actual);
    }

    public function testProductAmountIncreased(): void
    {
        [$product, $purchaseService] = $this->preparePurchaseService();

        $newPurchase = PurchaseFactory::new()->create([
            'product' => $product,
            'price' => self::PRICE,
            'amount' => self::PURCHASE_AMOUNT,
        ])->_real();

        $expected = self::PURCHASE_AMOUNT + self::PRODUCT_AMOUNT;

        self::assertSame($expected, $newPurchase->getProduct()->getAmount());
    }

    private function preparePurchaseService(): array
    {
        self::$entityManager = Mockery::mock(EntityManagerInterface::class);
        self::$entityManager->shouldReceive('persist');
        self::$entityManager->shouldReceive('flush');

        $product = ProductFactory::new()->create(['name' => self::PRODUCT_NAME])->_real();
        $productRepository = Mockery::mock(ProductRepository::class);
        $productRepository->shouldReceive('find')->with(1)->andReturn($product);
        $productRepository->shouldReceive('find')->with(3)->andReturn(null);
        $productManager = new ProductManager(self::$entityManager, $productRepository);
        $purchaseManager = new PurchaseManager(self::$entityManager, Mockery::mock(PurchaseRepository::class));

        return [$product, new PurchaseBuilderService($productManager, $purchaseManager)];
    }
}
