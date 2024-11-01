<?php

namespace App\DTO\Request;

use App\Entity\Recipe;
use Symfony\Component\Validator\Constraints as Assert;

class RecipeRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $dishId,

        #[Assert\NotBlank]
        public readonly int $productId,

        #[Assert\NotBlank]
        #[Assert\Type('decimal')]
        public readonly float $amount,
    ) {
    }

    public static function fromEntity(Recipe $recipe): self
    {
        return new self(...[
            'dishId' => $recipe->getDish()->getId(),
            'productId' => $recipe->getProduct()->getId(),
            'amount' => $recipe->getAmount(),
        ]);
    }
}
