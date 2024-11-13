<?php

namespace App\Service;

use App\DTO\Request\OrderRequestDTO;
use App\Enum\Status;
use App\Entity\{Dish, Order};
use App\Manager\{DishManager, OrderManager, ProductManager, RecipeManager, UserManager};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OrderBuilderService
{
    private HttpClientInterface $client;

    public function __construct(
        private readonly OrderManager $orderManager,
        private readonly DishManager $dishManager,
        public readonly ProductManager $productManager,
        private readonly RecipeManager $recipeManager,
        private readonly UserManager $userManager,
        private readonly TokenRequestService $tokenRequestService,
    ) {
        $this->client = $this->tokenRequestService->client();
    }

    public function createOrderWithUserAndDish(OrderRequestDTO $dto): ?int
    {
        [$dish, $user, $status, $isDelivery] = $this->getOrderParams($dto);

        if ($dish and $user) {
            try {
                $this->updateRelated($dish);
                return $this->orderManager->saveOrder($dish, $user, $status, $isDelivery);
            } catch (\RuntimeException) {
                return null;
            }
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
                $product->setAmount($amount);
                $dish->setIsAvailable(true);
            }
            if ($isSaved) {
                $entityManager->persist($order);
                $entityManager->flush();
            }
        } else {
            $dish->setIsAvailable(false);
            $this->dishManager->save($dish);
            throw new \RuntimeException('Недостаточно ингредиентов для заказа.');
        }
    }

    public function updateOrderWithUserAndDish(Order $order, OrderRequestDTO $dto): ?Order
    {
        [$dish, $user, $status, $isDelivery] = $this->getOrderParams($dto);

        if ($dish !== null) {
            $this->updateRelated($dish);
            $this->updateRelated($order->getDish(), isCancelled: true);
        }

        return $this->orderManager->updateOrder($order, $dish, $user, $status, $isDelivery);
    }

    public function payOrder(Order $order): bool
    {
        if (!$order or !in_array($order->getStatus(), [Status::Created, Status::Delivered])) {
            return false;
        }
        return $this->updateStatus($order, Status::Paid);
    }

    public function deliverOrder(Order $order): bool
    {
        if (
            !$order or
            !in_array($order->getStatus(), [Status::Created, Status::Paid]) or
            !$order->getIsDelivery()
        ) {
            return false;
        }
        return $this->updateStatus($order, Status::Delivered);
    }

    public function cancelOrder(Order $order): bool
    {
        if (!$order or !in_array($order->getStatus(), [Status::Created])) {
            return false;
        }
        return $this->updateStatus($order, Status::Cancelled);
    }

    public function deleteOrder(Order $order): bool
    {
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

    private function getOrderParams(OrderRequestDTO $dto): array
    {
        $dish = $dto->dishId ? $this->dishManager->getDishById($dto->dishId) : null;
        $user = $dto->userId ? $this->userManager->getUserById($dto->userId) : null;
        $status = $dto->status ? Status::from($dto->status) : null;
        $isDelivery = $dto->isDelivery;

        return [$dish, $user, $status, $isDelivery];
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
