<?php

namespace App\DTO\Request;

use App\Entity\Purchase;
use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequestDTO
{
    public function __construct(
        public readonly ?int $productId,

        #[Assert\GreaterThanOrEqual(0)]
        public readonly ?float $price,

        #[Assert\GreaterThanOrEqual(0)]
        public readonly ?float $amount,
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
