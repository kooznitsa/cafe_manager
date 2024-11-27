<?php

namespace UnitTest\Service;

use App\Client\StatsdAPIClient;
use App\DataFixture\{DishesFixture, ProductsFixture, UsersFixture};
use App\DTO\Request\OrderRequestDTO;
use App\Enum\Status;
use App\Factory\{DishFactory, OrderFactory, ProductFactory, RecipeFactory, UserFactory};
use App\Manager\{DishManager, OrderManager, ProductManager, RecipeManager, UserManager};
use App\Repository\{DishRepository, OrderRepository, ProductRepository, RecipeRepository, UserRepository};
use App\Service\{OrderBuilderService, TokenRequestService};
use Doctrine\ORM\{EntityManagerInterface, EntityRepository};
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Mockery;
use Mockery\MockInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Zenstruck\Foundry\Test\{Factories, ResetDatabase};

class OrderServiceTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private static EntityManagerInterface|MockInterface $entityManager;

    public function testCreateOrderSuccessfully(): void
    {
        [$user, $americano, $blackTea, $orderService] = $this->prepareOrderService();
        $americanoDto = new OrderRequestDTO(
            dishId: $americano->getId(),
            userId: $user->getId(),
            status: Status::Created->name,
            isDelivery: true,
        );
        $americanoOrder = $orderService->createOrderWithUserAndDish($americanoDto);

        self::assertEquals(OrderFactory::last(), $americanoOrder);
    }

    public function testOrderWithoutIngredients(): void
    {
        [$user, $americano, $blackTea, $orderService] = $this->prepareOrderService();
        $blackTeaDto = new OrderRequestDTO(
            dishId: $blackTea->getId(),
            userId: $user->getId(),
            status: Status::Created->name,
            isDelivery: true,
        );
        $blackTeaOrder = $orderService->createOrderWithUserAndDish($blackTeaDto);

        self::assertEquals(null, $blackTeaOrder);
    }

    private function prepareData(): array
    {
        $user = UserFactory::new()->create(['name' => UsersFixture::TIGER])->_real();

        $americano = DishFactory::new()->create(['name' => DishesFixture::AMERICANO, 'isAvailable' => true])->_real();
        $blackTea = DishFactory::new()->create(['name' => DishesFixture::BLACK_TEA, 'isAvailable' => false])->_real();

        $coffee = ProductFactory::new()->create(['name' => ProductsFixture::COFFEE, 'amount' => 1000])->_real();
        $water = ProductFactory::new()->create(['name' => ProductsFixture::WATER, 'amount' => 1000])->_real();
        $sugar = ProductFactory::new()->create(['name' => ProductsFixture::SUGAR, 'amount' => 1000])->_real();
        $tea = ProductFactory::new()->create(['name' => ProductsFixture::TEA, 'amount' => 0])->_real();

        $americanoRecipe = [
            RecipeFactory::new()->create(['dish' => $americano, 'product' => $coffee, 'amount' => 20])->_real(),
            RecipeFactory::new()->create(['dish' => $americano, 'product' => $water, 'amount' => 20])->_real(),
            RecipeFactory::new()->create(['dish' => $americano, 'product' => $sugar, 'amount' => 20])->_real(),
        ];
        $blackTeaRecipe = [
            RecipeFactory::new()->create(['dish' => $blackTea, 'product' => $tea, 'amount' => 20])->_real(),
            RecipeFactory::new()->create(['dish' => $blackTea, 'product' => $water, 'amount' => 20])->_real(),
            RecipeFactory::new()->create(['dish' => $blackTea, 'product' => $sugar, 'amount' => 20])->_real(),
        ];

        return [
            $user,
            $americano,
            $blackTea,
            $coffee,
            $water,
            $sugar,
            $tea,
            $americanoRecipe,
            $blackTeaRecipe,
        ];
    }

    private function prepareOrderService(): array
    {
        [
            $user,
            $americano,
            $blackTea,
            $coffee,
            $water,
            $sugar,
            $tea,
            $americanoRecipe,
            $blackTeaRecipe,
        ] = $this->prepareData();

        /** @var MockInterface|EntityRepository $repository */
        $repository = Mockery::mock(EntityRepository::class);

        /** @var MockInterface|EntityManagerInterface $repository */
        self::$entityManager = Mockery::mock(EntityManagerInterface::class);
        self::$entityManager->shouldReceive('persist');
        self::$entityManager->shouldReceive('flush');

        $cache = Mockery::mock(TagAwareCacheInterface::class);
        $cache->shouldReceive('invalidateTags', 'get');
        $orderManager = new OrderManager(
            self::$entityManager,
            Mockery::mock(OrderRepository::class),
            $cache,
            Mockery::mock(PaginatedFinderInterface::class),
        );

        $dishRepository = Mockery::mock(DishRepository::class);
        $dishRepository->shouldReceive('find')->once()->andReturn($americano, $blackTea);
        $dishManager = new DishManager(self::$entityManager, $dishRepository);

        $productRepository = Mockery::mock(ProductRepository::class);
        $productRepository->shouldReceive('find')->andReturn($coffee, $tea, $sugar, $water);
        $productManager = new ProductManager(self::$entityManager, $productRepository);

        $recipeRepository = Mockery::mock(RecipeRepository::class);
        $recipeRepository->shouldReceive('findBy')->andReturn($americanoRecipe, $blackTeaRecipe);
        $recipeManager = new RecipeManager(self::$entityManager, $recipeRepository);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('find')->andReturn($user);
        $userManager = new UserManager(
            self::$entityManager,
            $userRepository,
            Mockery::mock(UserPasswordHasherInterface::class),
            Mockery::mock(StatsdAPIClient::class),
        );

        $tokenRequestService = Mockery::mock(TokenRequestService::class);
        $tokenRequestService->shouldReceive('client');

        return [
            $user,
            $americano,
            $blackTea,
            new OrderBuilderService(
                $orderManager,
                $dishManager,
                $productManager,
                $recipeManager,
                $userManager,
                $tokenRequestService,
            ),
        ];
    }
}
