<?php

namespace App\Entity;

use App\Contract\HasMetaTimestampsInterface;
use App\Enum\Status;
use App\Repository\OrderRepository;
use App\Trait\{DateTimeTrait, IdTrait};
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;

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

    #[ArrayShape([
        'id' => 'int|null',
        'dish' => 'array',
        'user' => 'array',
        'status' => 'string',
        'isDelivery' => 'bool',
        'createdAt' => 'string',
        'updatedAt' => 'string',
    ])]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dish' => isset($this->dish) ? [
                'id' => $this->dish->getId(),
                'category' => $this->dish->getCategory()->getName(),
                'name' => $this->dish->getName(),
                'price' => $this->dish->getPrice(),
            ] : null,
            'user' => isset($this->user) ? [
                'id' => $this->user->getId(),
                'name' => $this->user->getName(),
                'email' => $this->user->getEmail(),
                'address' => $this->user->getAddress(),
            ] : null,
            'status' => $this->status->name,
            'isDelivery' => $this->isDelivery,
            'createdAt' => isset($this->createdAt) ? $this->createdAt->format('Y-m-d h:i:s') : '',
        ];
    }
}
