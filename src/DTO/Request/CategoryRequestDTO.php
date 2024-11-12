<?php

namespace App\DTO\Request;

use App\Entity\Category;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $name,
    ) {
    }

    public static function fromEntity(Category $category): self
    {
        return new self(...[
            'name' => $category->getName(),
        ]);
    }
}
