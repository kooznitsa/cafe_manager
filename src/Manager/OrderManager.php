<?php

namespace App\Manager;

use App\Enum\Status;
use App\Entity\{Dish, Order, User};
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository,
    ) {
    }

    public function saveOrder(Dish $dish, User $user, string $status, bool $isDelivery): ?int
    {
        $order = new Order();
        $this->setOrderParams($order, $dish, $user, $status, $isDelivery);
        $this->entityManager->persist($order);
        $this->entityManager->flush();

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

    public function getUserOrders(User $user): array
    {
        return $this->orderRepository->findBy(['user' => $user]);
    }

    public function getDishOrders(Dish $dish): array
    {
        return $this->orderRepository->findBy(['dish' => $dish]);
    }

    public function updateOrder(
        int $orderId,
        ?Dish $dish = null,
        ?User $user = null,
        ?string $status = null,
        ?bool $isDelivery = null,
    ): ?Order {
        /** @var Order $order */
        $order = $this->getOrderById($orderId);
        if (!$order) {
            return null;
        }
        if ($user) {
            $orderUser = $order->getUser();
            $orderUser->removeOrder($order);
        }
        if ($dish) {
            $orderDish = $order->getDish();
            $orderDish->removeOrder($order);
        }
        $this->setOrderParams($order, $dish, $user, $status, $isDelivery);
        $this->entityManager->flush();

        return $order;
    }

    public function deleteOrder(Order $order): bool
    {
        $order->setStatus(Status::Deleted->value);
        $order->getDish()->removeOrder($order);
        $order->getUser()->removeOrder($order);
        $this->entityManager->flush();

        return true;
    }

    public function deleteOrderById(int $orderId): bool
    {
        /** @var Order $order */
        $order = $this->getOrderById($orderId);
        if (!$order) {
            return false;
        }
        return $this->deleteOrder($order);
    }

    private function setOrderParams(Order $order, ?Dish $dish, ?User $user, ?string $status, ?bool $isDelivery): void
    {
        $order->setDish($dish);
        $order->setUser($user);
        $order->setStatus($status);
        $order->setIsDelivery($isDelivery);
        $dish?->addOrder($order);
        $user?->addOrder($order);
    }
}
