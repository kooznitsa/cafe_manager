<?php

namespace UnitTest\Service;

use App\Client\StatsdAPIClient;
use App\DTO\Request\OrderRequestDTO;
use App\Entity\Recipe;
use App\Enum\Status;
use App\Factory\{DishFactory, OrderFactory, ProductFactory, RecipeFactory, UserFactory};
use App\Manager\{DishManager, OrderManager, ProductManager, RecipeManager, UserManager};
use App\Repository\{DishRepository, OrderRepository, ProductRepository, RecipeRepository, UserRepository};
use App\Service\{OrderBuilderService, TokenRequestService};
use Doctrine\ORM\EntityManagerInterface;
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
    private const PRODUCT_AMOUNT = 1000.0;
    private const RECIPE_AMOUNT = 20.0;

    public function testCreateOrderSuccessfully(): void
    {
        [$user, $americano, $blackTea, $orderService] = $this->prepareOrderService();
        $americanoDto = new OrderRequestDTO(
            dishId: $americano->getId(),
            userId: $user->getId(),
            status: Status::Created->name,
            isDelivery: true,
        );
        $newOrder = OrderFactory::new()->create([
            'dish' => $americano,
            'user' => $user,
            'status' => Status::Created,
            'isDelivery' => true,
        ])->_real();
        $americanoOrder = $orderService->createOrderWithUserAndDish($americanoDto);
        $expected = [
            $newOrder->getDish()->getName(),
            $newOrder->getUser()->getEmail(),
            $newOrder->getStatus(),
            $newOrder->getIsDelivery(),
        ];
        $actual = [
            $americanoOrder->getDish()->getName(),
            $americanoOrder->getUser()->getEmail(),
            $americanoOrder->getStatus(),
            $americanoOrder->getIsDelivery(),
        ];

        $amount = self::PRODUCT_AMOUNT - self::RECIPE_AMOUNT;

        self::assertEquals($expected, $actual);
        self::assertSame(true, $americano->getIsAvailable());
        self::assertSame(
            [$amount, $amount, $amount],
            array_map(fn(Recipe $recipe) => $recipe->getProduct()->getAmount(), $americano->getRecipes()),
        );
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
        self::assertSame(false, $blackTea->getIsAvailable());
        self::assertSame(
            [0.0, self::PRODUCT_AMOUNT, self::PRODUCT_AMOUNT],
            array_map(fn(Recipe $recipe) => $recipe->getProduct()->getAmount(), $blackTea->getRecipes()),
        );
    }

    private function prepareData(): array
    {
        $user = UserFactory::new()->create()->_real();
        $americano = DishFactory::new()->create(['id' => 1, 'name' => 'Американо', 'isAvailable' => true])->_real();
        $blackTea = DishFactory::new()->create(['id' => 2, 'name' => 'Черный чай', 'isAvailable' => true])->_real();
        $coffee = ProductFactory::new()->create(['name' => 'Кофе'])->_real();
        $water = ProductFactory::new()->create(['name' => 'Вода'])->_real();
        $sugar = ProductFactory::new()->create(['name' => 'Сахар'])->_real();
        $tea = ProductFactory::new()->create(['name' => 'Чай', 'amount' => 0])->_real();
        $americanoRecipe = [
            RecipeFactory::new()->create(['dish' => $americano, 'product' => $coffee])->_real(),
            RecipeFactory::new()->create(['dish' => $americano, 'product' => $water])->_real(),
            RecipeFactory::new()->create(['dish' => $americano, 'product' => $sugar])->_real(),
        ];
        $blackTeaRecipe = [
            RecipeFactory::new()->create(['dish' => $blackTea, 'product' => $tea])->_real(),
            RecipeFactory::new()->create(['dish' => $blackTea, 'product' => $water])->_real(),
            RecipeFactory::new()->create(['dish' => $blackTea, 'product' => $sugar])->_real(),
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

        self::$entityManager = Mockery::mock(EntityManagerInterface::class);
        self::$entityManager->shouldReceive('persist');
        self::$entityManager->shouldReceive('flush');

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('find')->andReturn($user);
        $userManager = new UserManager(
            self::$entityManager,
            $userRepository,
            Mockery::mock(UserPasswordHasherInterface::class),
            Mockery::mock(StatsdAPIClient::class),
        );

        $productRepository = Mockery::mock(ProductRepository::class);
        $productRepository->shouldReceive('find')->andReturn($coffee, $water, $sugar, $tea);
        $productManager = new ProductManager(self::$entityManager, $productRepository);

        $dishRepository = Mockery::mock(DishRepository::class);
        $dishRepository->shouldReceive('find')->with(1)->andReturn($americano);
        $dishRepository->shouldReceive('find')->with(2)->andReturn($blackTea);
        $dishManager = new DishManager(self::$entityManager, $dishRepository);

        $recipeRepository = Mockery::mock(RecipeRepository::class);
        $recipeRepository->shouldReceive('findBy')
            ->with(['dish' => $americano])
            ->andReturn($americanoRecipe);
        $recipeRepository->shouldReceive('findBy')
            ->with(['dish' => $blackTea])
            ->andReturn($blackTeaRecipe);
        $recipeManager = new RecipeManager(self::$entityManager, $recipeRepository);

        $cache = Mockery::mock(TagAwareCacheInterface::class);
        $cache->shouldReceive('invalidateTags');
        $cache->shouldReceive('get');
        $orderManager = new OrderManager(
            self::$entityManager,
            Mockery::mock(OrderRepository::class),
            $cache,
            Mockery::mock(PaginatedFinderInterface::class),
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
