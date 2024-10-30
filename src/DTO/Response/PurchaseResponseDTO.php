<?php

namespace App\DTO\Response;

use App\Entity\Purchase;
use Symfony\Component\Validator\Constraints as Assert;

class PurchaseResponseDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $id,

        #[Assert\NotBlank]
        public readonly int $productId,

        #[Assert\NotBlank]
        #[Assert\Type('decimal')]
        public readonly float $price,

        #[Assert\NotBlank]
        #[Assert\Type('decimal')]
        public readonly float $amount,

        #[Assert\NotBlank]
        public readonly string $created_at,

        #[Assert\NotBlank]
        public readonly string $updated_at,
    ) {
    }

    public static function fromEntity(Purchase $purchase): self
    {
        return new self(...[
            'id' => $purchase->getId(),
            'productId' => $purchase->getProduct()->getId(),
            'price' => $purchase->getPrice(),
            'amount' => $purchase->getAmount(),
            'created_at' => $purchase->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $purchase->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
