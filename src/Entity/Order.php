<?php

namespace App\Entity;

use App\Contract\HasMetaTimestampsInterface;
use App\Repository\OrderRepository;
use App\Enum\Status;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'orders')]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Order implements HasMetaTimestampsInterface
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn]
    private ?Dish $dish = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn]
    private ?User $user = null;

    #[ORM\Column(type: 'string', nullable: false, enumType: Status::class)]
    private Status $status = Status::Created;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private ?bool $isDelivery = false;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private DateTime $updatedAt;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user ?? $this->user;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status ?? $this->status;

        return $this;
    }

    public function getIsDelivery(): ?bool
    {
        return $this->isDelivery;
    }

    public function setIsDelivery(bool $isDelivery): static
    {
        $this->isDelivery = $isDelivery;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new DateTime();
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function __toString(): string
    {
        return $this->user->getEmail() . ' - ' . $this->dish->getName();
    }
}
