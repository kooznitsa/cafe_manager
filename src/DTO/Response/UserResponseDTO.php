<?php

namespace App\DTO\Response;

use App\Entity\{Order, User};
use Symfony\Component\Validator\Constraints as Assert;

class UserResponseDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $id,

        #[Assert\NotBlank]
        #[Assert\Length(max: 32)]
        public readonly string $name,

        #[Assert\NotBlank]
        #[Assert\Length(max: 32)]
        #[Assert\Email(mode: 'strict')]
        public readonly string $email,

        #[Assert\Length(max: 255)]
        public readonly string $address,

        #[Assert\NotBlank]
        public readonly string $created_at,

        #[Assert\NotBlank]
        public readonly string $updated_at,

        #[Assert\Type('array')]
        public readonly array $orders = [],

        #[Assert\Type('array')]
        public readonly array $roles = [],
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(...[
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'address' => $user->getAddress(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            'orders' => array_map(
                static function (Order $order) {
                    return ['id' => $order->getId(),
                        'dish' => $order->getDish()->getId(),
                        'status' => $order->getStatus(),
                        'isDelivery' => $order->getIsDelivery(),
                    ];
                },
                $user->getOrders()
            ),
            'roles' => $user->getRoles(),
        ]);
    }
}
