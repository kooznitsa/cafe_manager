<?php

namespace App\DTO\Response;

use App\Entity\Product;
use Symfony\Component\Validator\Constraints as Assert;

class ProductResponseDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $id,

        #[Assert\NotBlank]
        public readonly string $name,

        #[Assert\NotBlank]
        public readonly string $unit,

        #[Assert\NotBlank]
        #[Assert\Type('decimal')]
        #[Assert\GreaterThanOrEqual(0)]
        public readonly float $amount,

        #[Assert\NotBlank]
        public readonly string $updated_at,
    ) {
    }

    public static function fromEntity(Product $product): self
    {
        return new self(...[
            'id' => $product->getId(),
            'name' => $product->getName(),
            'unit' => $product->getUnit(),
            'amount' => $product->getAmount(),
            'updated_at' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
