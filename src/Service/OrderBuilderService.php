<?php

namespace App\Service;

use App\Enum\Status;
use App\Entity\Order;
use App\Manager\{DishManager, OrderManager, UserManager};
use Symfony\Component\HttpFoundation\Request;

class OrderBuilderService
{
    public function __construct(
        private readonly OrderManager $orderManager,
        private readonly DishManager $dishManager,
        private readonly UserManager $userManager,
    ) {
    }

    public function createOrderWithUserAndDish(Request $request): ?int
    {
        [$dishId, $userId, $status, $isDelivery] = $this->getOrderParams($request);
        $status = $status ? Status::from($status) : $status;

        if ($dishId and $userId) {
            $dish = $this->dishManager->getDishById($dishId);
            $user = $this->userManager->getUserById($userId);

            return $this->orderManager->saveOrder($dish, $user, $status, $isDelivery);
        }
        return null;
    }

    public function updateOrderWithUserAndDish(Request $request): ?Order
    {
        [$dish, $user, $status, $isDelivery] = $this->getOrderParams($request, 'PATCH');
        $orderId = $request->query->get('orderId');
        $status = $status ? Status::from($status) : $status;

        if ($dish) {
            $dish = $this->dishManager->getDishById($dish);
        }
        if ($user) {
            $user = $this->userManager->getUserById($user);
        }

        return $this->orderManager->updateOrder($orderId, $dish, $user, $status, $isDelivery);
    }

    public function payOrder(int $orderId): bool
    {
        $order = $this->orderManager->getOrderById($orderId);
        if (!$order or !in_array($order->getStatus(), [Status::Created, Status::Delivered])) {
            return false;
        }
        return $this->orderManager->updateStatus($order, Status::Paid);
    }

    public function deliverOrder(int $orderId): bool
    {
        $order = $this->orderManager->getOrderById($orderId);
        if (
            !$order or
            !in_array($order->getStatus(), [Status::Created, Status::Paid]) or
            !$order->getIsDelivery()
        ) {
            return false;
        }
        return $this->orderManager->updateStatus($order, Status::Delivered);
    }

    public function cancelOrder(int $orderId): bool
    {
        $order = $this->orderManager->getOrderById($orderId);
        if (!$order or !in_array($order->getStatus(), [Status::Created])) {
            return false;
        }
        return $this->orderManager->updateStatus($order, Status::Cancelled);
    }

    public function getOrderParams(Request $request, string $requestMethod = 'POST'): array
    {
        $inputBag = $requestMethod === 'POST' ? $request->request : $request->query;

        $dishId = $inputBag->get('dishId');
        $userId = $inputBag->get('userId');
        $status = $inputBag->get('status');
        $isDelivery = $inputBag->get('isDelivery');

        return [$dishId, $userId, $status, $isDelivery];
    }
}
