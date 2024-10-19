<?php

namespace App\Entity;

use App\Contract\HasMetaTimestampsInterface;
use App\Repository\OrderRepository;
use App\Enum\Status;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'orders')]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Order implements HasMetaTimestampsInterface
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups(['default'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn]
    #[Groups(['default', 'create', 'update'])]
    private ?Dish $dish = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn]
    #[Groups(['default', 'create', 'update'])]
    private ?User $user = null;

    #[ORM\Column(type: 'string', nullable: false, enumType: Status::class)]
    #[Groups(['default', 'create', 'update'])]
    private Status $status = Status::Created;

    #[ORM\Column(nullable: false)]
    #[Groups(['default', 'create', 'update'])]
    private ?bool $isDelivery = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    #[Groups(['default'])]
    private DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['default'])]
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

    #[ArrayShape([
        'id' => 'int|null',
        'dish' => 'array',
        'user' => 'string',
        'status' => 'string',
        'isDelivery' => 'bool',
        'createdAt' => 'string',
        'updatedAt' => 'string',
    ])]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dish' => $this->getDish()->toArray(),
            'user' => $this->getUser()->toArray(),
            'status' => $this->status,
            'isDelivery' => $this->isDelivery,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
