<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use App\Trait\IdTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'recipes')]
#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\UniqueConstraint(name: 'recipes__dish_id__product_id__unique', columns: ['product_id', 'dish_id'])]
class Recipe
{
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    #[ORM\JoinColumn]
    private ?Dish $dish = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    private ?Product $product = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: false)]
    private ?string $amount = null;

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
}
