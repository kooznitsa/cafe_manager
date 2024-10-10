<?php

namespace App\Entity;

use App\Repository\SaleRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'sales')]
#[ORM\Entity(repositoryClass: SaleRepository::class)]
class Sale
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn]
    private ?Dish $dish = null;

    #[ORM\Column(nullable: false)]
    private ?DateTime $sold_at = null;

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
        $this->dish = $dish;

        return $this;
    }

    public function getSoldAt(): DateTime
    {
        return $this->sold_at;
    }

    public function setSoldAt(): void
    {
        $this->sold_at = new DateTime();
    }
}
