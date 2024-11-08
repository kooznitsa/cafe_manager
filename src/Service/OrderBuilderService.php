<?php

namespace App\Service;

use App\Enum\Status;
use App\Entity\{Dish, Order};
use App\Manager\{DishManager, OrderManager, ProductManager, RecipeManager, UserManager};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderBuilderService
{
    public function __construct(
        private readonly OrderManager $orderManager,
        private readonly DishManager $dishManager,
        public readonly ProductManager $productManager,
        private readonly RecipeManager $recipeManager,
        private readonly UserManager $userManager,
        private HttpClientInterface $client,
    ) {
    }

    public function createOrderWithUserAndDish(Request $request): ?int
    {
        [$dishId, $userId, $status, $isDelivery] = $this->getOrderParams($request);
        $status = $status ? Status::from($status) : $status;

        if ($dishId and $userId) {
            $dish = $this->dishManager->getDishById($dishId);
            $user = $this->userManager->getUserById($userId);

            $this->updateRelated($dish);
            return $this->orderManager->saveOrder($dish, $user, $status, $isDelivery);
        }
        return null;
    }

    public function updateRelated(
        Dish $dish,
        ?Order $order = null,
        ?EntityManagerInterface $entityManager = null,
        bool $isSaved = false,
        bool $isCancelled = false,
    ): void {
        $updatedAmounts = $this->checkOrderIngredients($dish, $isCancelled);

        if ($updatedAmounts) {
            foreach ($updatedAmounts as $productId => $amount) {
                $product = $this->productManager->getProductById($productId);
                $this->productManager->updateProduct($product, amount: $amount, isFlush: false);
                $this->dishManager->updateDish($dish, isFlush: false);
            }
            if ($isSaved) {
                $entityManager->persist($order);
                $entityManager->flush();
            }
        } else {
            $this->dishManager->updateDish($dish, isAvailable: false);
            throw new \RuntimeException('Недостаточно ингредиентов для заказа.');
        }
    }

    public function updateOrderWithUserAndDish(Request $request): ?Order
    {
        [$dish, $user, $status, $isDelivery] = $this->getOrderParams($request, 'PATCH');
        $orderId = $request->query->get('orderId');
        $order = $this->orderManager->getOrderById($orderId);
        $status = $status ? Status::from($status) : $status;

        if ($dish !== null) {
            $dish = $this->dishManager->getDishById($dish);
            $this->updateRelated($dish);
            $this->updateRelated($order->getDish(), isCancelled: true);
        }
        if ($user !== null) {
            $user = $this->userManager->getUserById($user);
        }

        return $this->orderManager->updateOrder($order, $dish, $user, $status, $isDelivery);
    }

    public function payOrder(int $orderId): bool
    {
        $order = $this->orderManager->getOrderById($orderId);
        if (!$order or !in_array($order->getStatus(), [Status::Created, Status::Delivered])) {
            return false;
        }
        return $this->updateStatus($order, Status::Paid);
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
        return $this->updateStatus($order, Status::Delivered);
    }

    public function cancelOrder(int $orderId): bool
    {
        $order = $this->orderManager->getOrderById($orderId);
        if (!$order or !in_array($order->getStatus(), [Status::Created])) {
            return false;
        }
        return $this->updateStatus($order, Status::Cancelled);
    }

    public function deleteOrder(int $orderId): bool
    {
        $order = $this->orderManager->getOrderById($orderId);
        if (!$order or !in_array($order->getStatus(), [Status::Created, Status::Cancelled])) {
            return false;
        }
        return $this->updateStatus($order, Status::Deleted);
    }

    public function updateStatus(Order $order, Status $status, bool $isFlush = true): bool
    {
        if (in_array($status, [Status::Cancelled, Status::Deleted])) {
            $this->updateRelated($order->getDish(), isCancelled: true);
        }
        $this->orderManager->updateStatus($order, $status, $isFlush);

        return true;
    }

    public function getChartData(): array
    {
        $response = $this->client->request('GET', "{$_ENV['GATEWAY_BASE_URL']}/order/stats")->toArray();
        $dates = array_column($response['orders'], 'orderDate');
        $sums = array_column($response['orders'], 'total');

        return [$dates, $sums];
    }

    private function getOrderParams(Request $request, string $requestMethod = 'POST'): array
    {
        $inputBag = $requestMethod === 'POST' ? $request->request : $request->query;

        $dishId = $inputBag->get('dishId');
        $userId = $inputBag->get('userId');
        $status = $inputBag->get('status');
        $isDelivery = $inputBag->get('isDelivery');

        return [$dishId, $userId, $status, $isDelivery];
    }

    private function checkOrderIngredients(Dish $dish, bool $isCancelled = false): ?array
    {
        $recipeItems = $this->recipeManager->getDishRecipe($dish);
        $updatedAmounts = [];

        foreach ($recipeItems as $recipeItem) {
            $recipeProduct = $recipeItem->getProduct();
            $recipeAmount = $recipeItem->getAmount();
            $productAmount = $recipeProduct->getAmount();
            $newAmount = $isCancelled ? $productAmount + $recipeAmount : $productAmount - $recipeAmount;
            $updatedAmounts[$recipeProduct->getId()] = $newAmount > 0 ? $newAmount : null;
        }

        return in_array(null, $updatedAmounts, true) ? null : $updatedAmounts;
    }
}
