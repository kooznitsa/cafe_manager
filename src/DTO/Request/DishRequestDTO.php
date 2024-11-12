<?php

namespace App\DTO\Request;

use App\Entity\Dish;
use Symfony\Component\Validator\Constraints as Assert;

class DishRequestDTO
{
    public function __construct(
        public readonly ?string $name,

        public readonly ?int $categoryId,

        #[Assert\GreaterThanOrEqual(0)]
        public readonly ?float $price,

        public readonly ?string $image,

        public readonly ?int $isAvailable,
    ) {
    }

    public static function fromEntity(Dish $dish): self
    {
        return new self(...[
            'name' => $dish->getName(),
            'categoryId' => $dish->getCategory()->getId(),
            'price' => $dish->getPrice(),
            'image' => $dish->getImage(),
            'isAvailable' => $dish->getIsAvailable(),
        ]);
    }
}
