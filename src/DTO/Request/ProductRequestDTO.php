<?php

namespace App\DTO\Request;

use App\Entity\Product;
use Symfony\Component\Validator\Constraints as Assert;

class ProductRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $name,

        #[Assert\NotBlank]
        public readonly string $unit,
    ) {
    }

    public static function fromEntity(Product $product): self
    {
        return new self(...[
            'name' => $product->getName(),
            'unit' => $product->getUnit(),
        ]);
    }
}
