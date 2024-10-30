<?php

namespace App\DTO\Response;

use App\Entity\{Category, Dish};
use Symfony\Component\Validator\Constraints as Assert;

class CategoryResponseDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $id,

        #[Assert\NotBlank]
        public readonly string $name,

        #[Assert\Type('array')]
        public readonly array $dishes = [],
    ) {
    }

    public static function fromEntity(Category $category): self
    {
        return new self(...[
            'id' => $category->getId(),
            'name' => $category->getName(),
            'dishes' => array_map(
                static function (Dish $dish) {
                    return ['id' => $dish->getId(),
                        'price' => $dish->getPrice(),
                        'image' => $dish->getImage(),
                    ];
                },
                $category->getDishes()
            ),
        ]);
    }
}
