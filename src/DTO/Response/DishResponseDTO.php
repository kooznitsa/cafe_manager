<?php

namespace App\DTO\Response;

use App\Entity\{Dish, Recipe};
use Symfony\Component\Validator\Constraints as Assert;

class DishResponseDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $id,

        #[Assert\NotBlank]
        public readonly string $name,

        #[Assert\NotBlank]
        #[Assert\Type('array')]
        public readonly array $category,

        #[Assert\Type('decimal')]
        public readonly float $price,

        #[Assert\NotBlank]
        public readonly string $image,

        #[Assert\Type('array')]
        public readonly array $recipes = [],
    ) {
    }

    public static function fromEntity(Dish $dish): self
    {
        return new self(...[
            'id' => $dish->getId(),
            'name' => $dish->getName(),
            'category' => [
                'id' => $dish->getCategory()->getId(),
                'name' => $dish->getCategory()->getName(),
            ],
            'price' => $dish->getPrice(),
            'image' => $dish->getImage(),
            'recipes' => array_map(
                static function (Recipe $recipe) {
                    return ['id' => $recipe->getId(),
                        'product' => $recipe->getProduct()->getname(),
                        'unit' => $recipe->getProduct()->getunit(),
                    ];
                },
                $dish->getRecipes()
            ),
        ]);
    }
}
