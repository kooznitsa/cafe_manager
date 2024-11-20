<?php

namespace App\Consumer\CreateOrder;

use App\Consumer\CreateOrder\Input\Message;
use App\DTO\Request\OrderRequestDTO;
use App\Entity\{Dish, User};
use App\Repository\{DishRepository, UserRepository};
use App\Service\OrderBuilderService;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Consumer implements ConsumerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly OrderBuilderService $orderService,
        private readonly UserRepository $userRepository,
        private readonly DishRepository $dishRepository,
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        try {
            $message = Message::createFromQueue($msg->getBody());
            $errors = $this->validator->validate($message);
            if ($errors->count() > 0) {
                return $this->reject((string) $errors);
            }
        } catch (JsonException $e) {
            return $this->reject($e->getMessage());
        }

        $user = $this->userRepository->find($message->getUserId());
        if (!($user instanceof User)) {
            return $this->reject("User ID {$message->getUserId()} was not found");
        }

        $dish = $this->dishRepository->find($message->getDishId());
        if (!($dish instanceof Dish)) {
            return $this->reject("Dish ID {$message->getDishId()} was not found");
        }

        $dto = new OrderRequestDTO(
            dishId: $dish->getId(),
            userId: $user->getId(),
            status: $message->getStatus(),
            isDelivery: $message->getIsDelivery(),
        );
        $order = $this->orderService->createOrderWithUserAndDish($dto);
        if (order === null) {
            $this->reject('Order was not created');
        } else {
            $this->logger->info("Order was created");
        }

        $this->entityManager->clear();
        $this->entityManager->getConnection()->close();

        return self::MSG_ACK;
    }

    private function reject(string $error): int
    {
        echo "Incorrect message: $error";

        return self::MSG_REJECT;
    }
}
