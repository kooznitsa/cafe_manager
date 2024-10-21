<?php

namespace App\DTO;

use App\Entity\{Order, User};
use Symfony\Component\Validator\Constraints as Assert;

class ManageUserDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 32)]
        public string $name = '',

        #[Assert\NotBlank]
        #[Assert\Length(max: 32)]
        #[Assert\PasswordStrength]
        public string $password = '',

        #[Assert\NotBlank]
        #[Assert\Length(max: 32)]
        #[Assert\Email(mode: 'strict')]
        public string $email = '',

        #[Assert\Length(max: 255)]
        public string $address = '',

        #[Assert\Type('array')]
        public array $orders = [],
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
        ]);
    }
}
