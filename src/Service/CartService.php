<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CartService
{
    private HttpClientInterface $client;

    public function __construct(
        private readonly TokenRequestService $tokenRequestService,
    ) {
        $this->client = $this->tokenRequestService->client();
    }

    public function getCart(int $userId): ?array
    {
        $response = $this->client->request('GET', "{$_ENV['GATEWAY_BASE_URL']}/order/by-user/$userId");

        return $response->getContent() ? $response->toArray() : null;
    }

    public function putToCart(int $dishId, int $userId): void
    {
        $this->client->request(
            'POST',
            "{$_ENV['GATEWAY_BASE_URL']}/order",
            ['body' => ['dishId' => $dishId, 'userId' => $userId, 'status' => 'Created', 'isDelivery' => 0]],
        );
    }

    public function deleteFromCart(int $orderId): void
    {
        $this->client->request('DELETE', "{$_ENV['GATEWAY_BASE_URL']}/order/$orderId");
    }

    public function toggleDelivery(array $orderIds, int $isDelivery): void
    {
        foreach ($orderIds as $orderId) {
            $this->client->request(
                'PATCH',
                "{$_ENV['GATEWAY_BASE_URL']}/order?orderId=$orderId&isDelivery=$isDelivery",
            );
        }
    }
}
