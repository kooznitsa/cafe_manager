<?php

namespace App\DTO\Response;

use App\Entity\Order;
use Symfony\Component\Validator\Constraints as Assert;

class OrderResponseDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $id,

        #[Assert\NotBlank]
        public readonly array $dish,

        #[Assert\NotBlank]
        public readonly array $user,

        #[Assert\NotBlank]
        public readonly string $status,

        #[Assert\NotBlank]
        public readonly bool $isDelivery,

        #[Assert\NotBlank]
        public readonly string $created_at,

        #[Assert\NotBlank]
        public readonly string $updated_at,
    ) {
    }

    public static function fromEntity(Order $order): self
    {
        return new self(...[
            'id' => $order->getId(),
            'dish' => [
                'id' => $order->getDish()->getId(),
                'category' => $order->getDish()->getCategory()->getName(),
                'name' => $order->getDish()->getName(),
                'price' => $order->getDish()->getPrice(),
            ],
            'user' => [
                'id' => $order->getUser()->getId(),
                'name' => $order->getUser()->getName(),
                'email' => $order->getUser()->getEmail(),
                'address' => $order->getUser()->getAddress(),
            ],
            'status' => $order->getStatus()->name,
            'isDelivery' => $order->getIsDelivery(),
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
