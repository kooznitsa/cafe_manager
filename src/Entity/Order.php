<?php

namespace App\Entity;

use App\Contract\HasMetaTimestampsInterface;
use App\Enum\Status;
use App\Repository\OrderRepository;
use App\Trait\{DateTimeTrait, IdTrait};
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'orders')]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Order implements HasMetaTimestampsInterface
{
    use DateTimeTrait;
    use IdTrait;

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

    public function __toString(): string
    {
        return $this->user->getEmail() . ' - ' . $this->dish->getName();
    }
}
