<?php

namespace App\DTO\Request;

use App\Entity\{Order, User};
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class UserRequestDTO
{
    public function __construct(
        #[Assert\Length(max: 32)]
        public ?string $name,

        #[Assert\Length(max: 120)]
        #[Assert\PasswordStrength]
        public ?string $password,

        #[Assert\Length(max: 32)]
        #[Assert\Email(mode: 'strict')]
        public ?string $email,

        #[Assert\Length(max: 255)]
        public ?string $address,

        #[Assert\Type('array')]
        #[OA\Property(property: 'orders[]', type: 'array', items: new OA\Items(type: Order::class))]
        public ?array $orders,

        #[Assert\Type('array')]
        #[OA\Property(property: 'roles[]', type: 'array', items: new OA\Items(type: 'string'))]
        public ?array $roles,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(...[
            'name' => $user->getName(),
            'password' => $user->getPassword(),
            'email' => $user->getEmail(),
            'address' => $user->getAddress(),
            'orders' => array_map(
                static function (Order $order) {
                    return ['id' => $order->getId(),
                        'dish' => $order->getDish(),
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
