<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Service\OrderBuilderService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\{PostUpdateEventArgs, PreUpdateEventArgs};
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Order::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Order::class)]
class OrderAmountEventListener
{
    public function __construct(
        public readonly OrderBuilderService $orderBuilderService,
    ) {
    }

    public function preUpdate(Order $order, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('dish')) {
            $oldDish = $args->getOldValue('dish');
            $newDish = $args->getNewValue('dish');

            $this->orderBuilderService->updateRelated($newDish);
            $this->orderBuilderService->updateRelated($oldDish, isCancelled: true);
        }

        if ($args->hasChangedField('status')) {
            $this->orderBuilderService->updateStatus($order, $order->getStatus(), isFlush: false);
        }
    }

    public function postUpdate(Order $order, PostUpdateEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        $entityManager->persist($order);
        $entityManager->flush();
    }
}
