<?php

namespace App\DTO\Request;

use App\Entity\Product;
use Symfony\Component\Validator\Constraints as Assert;

class ProductRequestDTO
{
    public function __construct(
        public readonly ?string $name,

        public readonly ?string $unit,

        #[Assert\GreaterThanOrEqual(0)]
        public readonly ?float $amount,
    ) {
    }

    public static function fromEntity(Product $product): self
    {
        return new self(...[
            'name' => $product->getName(),
            'unit' => $product->getUnit(),
            'amount' => $product->getAmount(),
        ]);
    }
}
