<?php

namespace App\Manager;

use App\DTO\Response\OrderResponseDTO;
use App\Enum\Status;
use App\Entity\{Dish, Order, User};
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\{ItemInterface, TagAwareCacheInterface};

class OrderManager
{
    private const CACHE_TAG = 'orders';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository,
        private readonly TagAwareCacheInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function saveOrder(Dish $dish, User $user, Status $status, bool $isDelivery): ?int
    {
        $order = new Order();
        $this->setOrderParams($order, $dish, $user, $status, $isDelivery);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $this->cache->invalidateTags([self::CACHE_TAG]);

        return $order->getId();
    }

    /**
     * @return Order[]
     */
    public function getOrders(): array
    {
        return $this->orderRepository->findAll();
    }

    public function getOrderById(int $id): ?Order
    {
        return $this->orderRepository->find($id);
    }

    /**
     * Uses cache to get user orders.
     *
     * @return Order[]
     *
     * @throws InvalidArgumentException
     */
    public function getUserOrders(User $user): array
    {
        return $this->cache->get(
            self::CACHE_TAG,
            function (ItemInterface $item) use ($user) {
                $orders = $this->orderRepository->getCreatedUserOrders($user->getId());
                $ordersSerialized = array_map(static fn(Order $order) => OrderResponseDTO::fromEntity($order), $orders);
                $item->set($ordersSerialized);
                $item->tag(self::CACHE_TAG);

                return $ordersSerialized;
            }
        );
    }

    public function getDishOrders(Dish $dish): array
    {
        return $this->orderRepository->findBy(['dish' => $dish]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function updateOrder(
        ?Order $order,
        ?Dish $dish = null,
        ?User $user = null,
        ?Status $status = null,
        ?bool $isDelivery = null,
    ): ?Order {
        if (!$order) {
            return null;
        }
        $this->removeOrderFromParent($order);
        $this->setOrderParams($order, $dish, $user, $status, $isDelivery);
        $this->entityManager->flush();
        $this->cache->invalidateTags([self::CACHE_TAG]);

        return $order;
    }

    public function updateStatus(Order $order, Status $status, bool $isFlush = true): bool
    {
        $this->removeOrderFromParent($order);
        $order->setStatus($status);
        $this->addOrderToParent($order);
        if ($isFlush) {
            $this->entityManager->flush();
        }

        return true;
    }

    public function getPaidOrders(int $page, int $perPage): array
    {
        return $this->orderRepository->getPaidOrders($page, $perPage);
    }

    private function setOrderParams(Order $order, ?Dish $dish, ?User $user, ?Status $status, ?bool $isDelivery): void
    {
        $order->setDish($dish)->setUser($user)->setStatus($status);
        if ($isDelivery !== null) {
            $order->setIsDelivery($isDelivery);
        }
        $dish?->addOrder($order);
        $user?->addOrder($order);
    }

    private function removeOrderFromParent(Order $order): void
    {
        $orderUser = $order->getUser();
        $orderUser->removeOrder($order);
        $orderDish = $order->getDish();
        $orderDish->removeOrder($order);
    }

    private function addOrderToParent(Order $order): void
    {
        $orderUser = $order->getUser();
        $orderUser->addOrder($order);
        $orderDish = $order->getDish();
        $orderDish->addOrder($order);
    }
}
