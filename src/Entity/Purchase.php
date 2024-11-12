<?php

namespace App\Entity;

use App\Contract\HasMetaTimestampsInterface;
use App\Repository\PurchaseRepository;
use App\Trait\{DateTimeTrait, IdTrait};
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'purchases')]
#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Purchase implements HasMetaTimestampsInterface
{
    use DateTimeTrait;
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn]
    private ?Product $product = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: false)]
    private ?string $price = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: false)]
    private ?string $amount = null;

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product ?? $this->product;

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
