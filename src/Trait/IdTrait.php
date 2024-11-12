<?php

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
