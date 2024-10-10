<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'purchases')]
#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn]
    private ?Product $product = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: false)]
    private ?string $price = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: false)]
    private ?string $amount = null;

    #[ORM\Column(nullable: false)]
    private ?DateTime $purchased_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPurchasedAt(): DateTime
    {
        return $this->purchased_at;
    }

    public function setPurchasedAt(): void
    {
        $this->purchased_at = new DateTime();
    }
}
