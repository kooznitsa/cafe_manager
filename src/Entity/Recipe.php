<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'recipes')]
#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\UniqueConstraint(name: 'recipes__dish_id__product_id__unique', columns: ['product_id', 'dish_id'])]
class Recipe
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups(['default'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    #[ORM\JoinColumn]
    #[Groups(['create', 'update'])]
    private ?Dish $dish = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    #[Groups(['default', 'create', 'update'])]
    private ?Product $product = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: false)]
    #[Groups(['default', 'create', 'update'])]
    private ?string $amount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDish(): ?Dish
    {
        return $this->dish;
    }

    public function setDish(?Dish $dish): static
    {
        $this->dish = $dish ?? $this->dish;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product ?? $this->product;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount ?? $this->amount;

        return $this;
    }

    #[ArrayShape([
        'id' => 'int|null',
        'product' => 'array',
        'amount' => 'float',
    ])]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product' => $this->getProduct()->toArray(),
            'amount' => $this->amount,
        ];
    }
}
