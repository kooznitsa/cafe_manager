<?php

namespace App\DTO\Request;

use App\Entity\Purchase;
use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $productId,

        #[Assert\NotBlank]
        #[Assert\Type('decimal')]
        public readonly float $price,

        #[Assert\NotBlank]
        #[Assert\Type('decimal')]
        public readonly float $amount,
    ) {
    }

    public static function fromEntity(Purchase $purchase): self
    {
        return new self(...[
            'productId' => $purchase->getProduct()->getId(),
            'price' => $purchase->getPrice(),
            'amount' => $purchase->getAmount(),
        ]);
    }
}
