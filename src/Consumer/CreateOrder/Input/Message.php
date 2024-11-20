<?php

namespace App\Consumer\CreateOrder\Input;

use App\Enum\Status;
use Symfony\Component\Validator\Constraints as Assert;

class Message
{
    #[Assert\Type('numeric')]
    private int $dishId;

    #[Assert\Type('numeric')]
    private int $userId;

    #[Assert\Callback([Status::class, 'validate'])]
    public readonly ?string $status;

    public readonly ?bool $isDelivery;

    public static function createFromQueue(string $messageBody): self
    {
        $message = json_decode($messageBody, true, 512, JSON_THROW_ON_ERROR);
        $result = new self();
        $result->dishId = $message['dishId'];
        $result->userId = $message['userId'];
        $result->status = $message['status'];
        $result->isDelivery = $message['isDelivery'];

        return $result;
    }

    public function getDishId(): int
    {
        return $this->dishId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getIsDelivery(): int
    {
        return $this->isDelivery;
    }
}
