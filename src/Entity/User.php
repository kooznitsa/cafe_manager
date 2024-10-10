<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;

#[ORM\Table(name: '`users`')]
#[ORM\Entity]
class User
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    private string $password;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $address;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private DateTime $updatedAt;

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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(): void
    {
        $this->createdAt = new DateTime();
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTime();
    }

    #[ArrayShape([
        'id' => 'int|null',
        'name' => 'string',
        'password' => 'string',
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
            'password' => $this->password,
            'email' => $this->email,
            'address' => $this->address,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
