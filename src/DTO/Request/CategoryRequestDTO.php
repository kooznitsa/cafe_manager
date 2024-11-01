<?php

namespace App\DTO\Request;

use App\Entity\Category;
use Symfony\Component\HttpFoundation\Request;
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

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->request->get('name') ?? $request->query->get('name'),
        );
    }
}
