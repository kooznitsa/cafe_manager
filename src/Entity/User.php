<?php

namespace App\Entity;

use App\Contract\HasMetaTimestampsInterface;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: '`users`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'users__email__unique', columns: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User implements HasMetaTimestampsInterface
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups(['default'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    #[Groups(['default', 'create', 'update'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    #[Groups(['create', 'update'])]
    #[Assert\PasswordStrength]
    private string $password;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    #[Assert\Email(mode: 'strict')]
    #[Groups(['default', 'create', 'update'])]
    private string $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['default', 'create', 'update'])]
    private string $address;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    #[Groups(['default'])]
    private DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['default'])]
    private DateTime $updatedAt;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name ?? $this->name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password ?? $this->password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email ?? $this->email;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address ?? $this->address;
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

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->email;
    }

    #[ArrayShape([
        'id' => 'int|null',
        'name' => 'string',
        'email' => 'string',
        'address' => 'string',
        'createdAt' => 'string',
        'updatedAt' => 'string',
    ])]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
