<?php

namespace App\DTO\Request;

use App\Entity\Order;
use Symfony\Component\Validator\Constraints as Assert;

class OrderRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $dishId,

        #[Assert\NotBlank]
        public readonly int $userId,

        #[Assert\NotBlank]
        public readonly string $status,

        #[Assert\NotBlank]
        public readonly int $isDelivery,
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
}
