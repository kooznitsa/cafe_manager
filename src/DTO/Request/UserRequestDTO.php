<?php

namespace App\DTO\Request;

use App\Entity\{Order, User};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class UserRequestDTO
{
    public function __construct(
        #[Assert\Length(max: 32)]
        public ?string $name = '',

        #[Assert\Length(max: 120)]
        #[Assert\PasswordStrength]
        public ?string $password = '',

        #[Assert\Length(max: 32)]
        #[Assert\Email(mode: 'strict')]
        public ?string $email = '',

        #[Assert\Length(max: 255)]
        public ?string $address = '',

        #[Assert\Type('array')]
        public ?array $orders = [],

        #[Assert\Type('array')]
        public ?array $roles = [],
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

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->request->get('name') ?? $request->query->get('name'),
            password: $request->request->get('password') ?? $request->query->get('password'),
            email: $request->request->get('email') ?? $request->query->get('email'),
            address: $request->request->get('address') ?? $request->query->get('address'),
            roles: $request->request->all('roles') ?? $request->query->all('roles') ?? [],
        );
    }
}
