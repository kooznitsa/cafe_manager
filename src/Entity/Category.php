<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use App\Trait\IdTrait;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'categories')]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\UniqueConstraint(name: 'categories__name__unique', columns: ['name'])]
class Category
{
    use IdTrait;

    #[ORM\Column(length: 32, nullable: false)]
    private ?string $name = null;

    /**
     * @var Collection<int, Dish>
     */
    #[ORM\OneToMany(targetEntity: Dish::class, mappedBy: 'category')]
    private Collection $dishes;

    public function __construct()
    {
        $this->dishes = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Dish[]
     */
    public function getDishes(): array
    {
        return $this->dishes->toArray();
    }

    public function addDish(Dish $dish): static
    {
        if (!$this->dishes->contains($dish)) {
            $this->dishes->add($dish);
            $dish->setCategory($this);
        }

        return $this;
    }

    public function removeDish(Dish $dish): static
    {
        if ($this->dishes->removeElement($dish)) {
            if ($dish->getCategory() === $this) {
                $dish->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
