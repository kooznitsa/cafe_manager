<?php

namespace Tests\Unit\Entity;

use App\Entity\{Category, Dish, Order, User};
use App\Enum\Status;
use Codeception\Test\Unit;
use DateTime;
use Symfony\Bridge\PhpUnit\ClockMock;

class OrderTest extends Unit
{
    private const NOW_TIME = '@now';
    private const USER_PASS = 'TSshark1957work$';
    private const USER = [
        'id' => 37,
        'name' => 'Кукушка',
        'email' => 'cuckoo@gmail.com',
        'address' => 'Сосновый бор',
    ];
    private const DISH = [
        'id' => 1,
        'category' => 'Кофе',
        'name' => 'Капучино',
        'price' => 180.00,
    ];

    public function _orderDataProvider(): array
    {
        $expectedPositive = [
            'id' => 5,
            'dish' => self::DISH,
            'user' => self::USER,
            'status' => Status::Created->name,
            'isDelivery' => true,
            'createdAt' => self::NOW_TIME,
        ];
        $positiveOrder = $this->makeOrder($expectedPositive);
        $expectedNoDish = [
            'id' => 5,
            'dish' => null,
            'user' => self::USER,
            'status' => Status::Created->name,
            'isDelivery' => true,
            'createdAt' => self::NOW_TIME,
        ];
        $expectedNoUser = [
            'id' => 5,
            'dish' => self::DISH,
            'user' => null,
            'status' => Status::Created->name,
            'isDelivery' => true,
            'createdAt' => self::NOW_TIME,
        ];

        return [
            'positive' => [
                $positiveOrder,
                $expectedPositive,
            ],
            'no dish' => [
                $this->makeOrder($expectedNoDish),
                $expectedNoDish,
            ],
            'no user' => [
                $this->makeOrder($expectedNoUser),
                $expectedNoUser,
            ],
        ];
    }

    /**
     * @dataProvider _orderDataProvider
     */
    public function testToArrayReturnsCorrectValues(Order $order, array $expected): void
    {
        ClockMock::register(Order::class);
        if ($expected['createdAt'] === self::NOW_TIME) {
            $expected['createdAt'] = DateTime::createFromFormat(
                'U',
                (string) time(),
            )->format('Y-m-d h:i:s');
        }

        $order->setCreatedAt();
        $actual = $order->toArray();

        static::assertSame($expected, $actual, 'Order::toArray should return correct result');
    }

    private function makeOrder(array $data): Order
    {
        $order = new Order();
        $order->setId($data['id']);
        $order = $this->addDish($order, $data);
        $order = $this->addUser($order, $data);
        $order->setStatus(Status::Created);
        $order->setISDelivery($data['isDelivery']);

        return $order;
    }

    private function addDish(Order $order, array $data): Order
    {
        if ($data['dish']) {
            $dish = new Dish();
            $dish->setId($data['dish']['id']);
            $dish->setName($data['dish']['name']);
            $dish = $this->addCategory($dish, $data);
            $dish->setPrice($data['dish']['price']);
            $order->setDish($dish);
        }

        return $order;
    }

    private function addUser(Order $order, array $data): Order
    {
        if ($data['user']) {
            $user = new User();
            $user->setId($data['user']['id']);
            $user->setName($data['user']['name']);
            $user->setEmail($data['user']['email']);
            $user->setPassword(self::USER_PASS);
            $user->setAddress($data['user']['address']);
            $order->setUser($user);
        }

        return $order;
    }

    private function addCategory(Dish $dish, array $data): Dish
    {
        if ($data['dish']) {
            $category = new Category();
            $category->setName($data['dish']['category']);
            $dish->setCategory($category);
        }

        return $dish;
    }
}
