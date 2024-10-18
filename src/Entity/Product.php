<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'products')]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\UniqueConstraint(name: 'products__name__unique', columns: ['name'])]
class Product
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups(['default'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(['default', 'create', 'update'])]
    private ?string $name = null;

    #[ORM\Column(length: 32, nullable: false)]
    #[Groups(['default', 'create', 'update'])]
    private ?string $unit = null;

    /**
     * @var Collection<int, Purchase>
     */
    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'product')]
    private Collection $purchases;

    public function __construct()
    {
        $this->purchases = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return Collection<int, Purchase>
     */
    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setProduct($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            if ($purchase->getProduct() === $this) {
                $purchase->setProduct(null);
            }
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
        'unit' => 'string',
    ])]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'unit' => $this->unit,
        ];
    }
}
