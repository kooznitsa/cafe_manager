<?php

namespace App\EventListener;

use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\{PostUpdateEventArgs, PrePersistEventArgs, PreRemoveEventArgs, PreUpdateEventArgs};
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Purchase::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Purchase::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Purchase::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Purchase::class)]
class PurchaseAmountEventListener
{
    public function prePersist(Purchase $purchase, PrePersistEventArgs $args): void
    {
        $product = $purchase->getProduct();
        $productAmount = $product->getAmount() + $purchase->getAmount();
        $product->setAmount($productAmount);
    }

    public function preUpdate(Purchase $purchase, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('amount')) {
            if (!$args->hasChangedField('product')) {
                $product = $purchase->getProduct();
                $productAmount = $product->getAmount() - $args->getOldValue('amount')
                    + $args->getNewValue('amount');
            } else {
                $oldProduct = $args->getOldValue('product');
                $oldProductAmount = $oldProduct->getAmount() - $args->getOldValue('amount');
                $oldProduct->setAmount($oldProductAmount);

                $product = $args->getNewValue('product');
                $productAmount = $product->getAmount() + $args->getNewValue('amount');
            }

            $product->setAmount($productAmount);
        }

        if ($args->hasChangedField('product')) {
            $oldProduct = $args->getOldValue('product');
            $oldProductAmount = $oldProduct->getAmount() - $purchase->getAmount();
            $oldProduct->setAmount($oldProductAmount);

            $product = $purchase->getProduct();
            $productAmount = $product->getAmount() + $purchase->getAmount();
            $product->setAmount($productAmount);
        }
    }

    public function postUpdate(Purchase $purchase, PostUpdateEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        $entityManager->persist($purchase);
        $entityManager->flush();
    }

    public function preRemove(Purchase $purchase, PreRemoveEventArgs $args): void
    {
        $product = $purchase->getProduct();
        $productAmount = $product->getAmount() - $purchase->getAmount();
        $product->setAmount($productAmount);
    }
}
