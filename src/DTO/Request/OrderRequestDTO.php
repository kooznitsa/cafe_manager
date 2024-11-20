<?php

namespace App\DTO\Request;

use App\Entity\Order;
use App\Enum\Status;
use JsonException;
use Symfony\Component\Validator\Constraints as Assert;

class OrderRequestDTO
{
    public function __construct(
        public readonly ?int $dishId,

        public readonly ?int $userId,

        #[Assert\Callback([Status::class, 'validate'])]
        public readonly ?string $status,

        public readonly ?bool $isDelivery,
    ) {
    }

    public static function fromEntity(Order $order): self
    {
        return new self(...[
            'dishId' => $order->getDish()->getId(),
            'userId' => $order->getUser()->getId(),
            'status' => $order->getStatus(),
            'isDelivery' => $order->getIsDelivery(),
        ]);
    }

    /**
     * @throws JsonException
     */
    public function toAMQPMessage(): string
    {
        $payload = [
            'dishId' => $this->dishId,
            'userId' => $this->userId,
            'status' => $this->status,
            'isDelivery' => $this->isDelivery,
        ];

        return json_encode($payload, JSON_THROW_ON_ERROR);
    }
}
