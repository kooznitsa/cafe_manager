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

    public function saveOrder(Dish $dish, User $user, Status $status, bool $isDelivery): ?int
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
        ?Status $status = null,
        ?bool $isDelivery = null,
    ): ?Order {
        /** @var Order $order */
        $order = $this->getOrderById($orderId);
        if (!$order) {
            return null;
        }
        $this->removeOrderFromParent($order);
        $this->setOrderParams($order, $dish, $user, $status, $isDelivery);
        $this->entityManager->flush();

        return $order;
    }

    public function updateStatus(Order $order, Status $status): bool
    {
        $this->removeOrderFromParent($order);
        $order->setStatus($status);
        $this->addOrderToParent($order);
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

        return $this->updateStatus($order, Status::Deleted);
    }

    public function getPaidOrders(int $page, int $perPage): array
    {
        return $this->orderRepository->getPaidOrders($page, $perPage);
    }

    private function setOrderParams(Order $order, ?Dish $dish, ?User $user, ?Status $status, ?bool $isDelivery): void
    {
        $order->setDish($dish);
        $order->setUser($user);
        $order->setStatus($status);
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
