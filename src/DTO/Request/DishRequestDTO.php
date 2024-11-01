<?php

namespace App\DTO\Request;

use App\Entity\Dish;
use Symfony\Component\Validator\Constraints as Assert;

class DishRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $name,

        #[Assert\NotBlank]
        public readonly int $categoryId,

        #[Assert\NotBlank]
        #[Assert\Type('decimal')]
        public readonly float $price,

        public readonly ?string $image,
    ) {
    }

    public static function fromEntity(Dish $dish): self
    {
        return new self(...[
            'name' => $dish->getName(),
            'categoryId' => $dish->getCategory()->getId(),
            'price' => $dish->getPrice(),
            'image' => $dish->getImage(),
        ]);
    }
}
