<?php

namespace App\Entity;

use App\Repository\DishRepository;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'dishes')]
#[ORM\Entity(repositoryClass: DishRepository::class)]
#[ORM\UniqueConstraint(name: 'dishes__name__category__unique', columns: ['name', 'category_id'])]
class Dish
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(['create', 'update'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'dishes')]
    #[ORM\JoinColumn]
    #[Groups(['create', 'update'])]
    private ?Category $category = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: false)]
    #[Groups(['create', 'update'])]
    private ?string $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['create', 'update'])]
    private ?string $image = null;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\OneToMany(targetEntity: Recipe::class, mappedBy: 'dish')]
    private Collection $recipes;

    /**
     * @var Collection<int, Sale>
     */
    #[ORM\OneToMany(targetEntity: Sale::class, mappedBy: 'dish')]
    private Collection $sales;

    public function __construct()
    {
        $this->recipes = new ArrayCollection();
        $this->sales = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name ?? $this->name;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category ?? $this->category;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price ?? $this->price;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image ?? $this->image;

        return $this;
    }

    public function addRecipe(Recipe $recipe): static
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes->add($recipe);
            $recipe->setDish($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Sale>
     */
    public function getSales(): Collection
    {
        return $this->sales;
    }

    public function addSale(Sale $sale): static
    {
        if (!$this->sales->contains($sale)) {
            $this->sales->add($sale);
            $sale->setDish($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    #[ArrayShape([
        'id' => 'int|null',
        'name' => 'string',
        'category' => 'array',
        'price' => 'float',
        'image' => 'string',
    ])]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->getCategory()->toArray(),
            'price' => $this->price,
            'image' => $this->image,
        ];
    }
}
