<?php

namespace App\DTO\Request;

use App\Entity\Recipe;
use Symfony\Component\Validator\Constraints as Assert;

class RecipeRequestDTO
{
    public function __construct(
        public readonly ?int $dishId,

        public readonly ?int $productId,

        #[Assert\GreaterThanOrEqual(0)]
        public readonly ?float $amount,
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
