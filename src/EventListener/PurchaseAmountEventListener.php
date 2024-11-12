<?php

namespace App\EventListener;

use App\Entity\Purchase;
use App\Manager\ProductManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\{PostPersistEventArgs, PostUpdateEventArgs, PreRemoveEventArgs, PreUpdateEventArgs};
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Purchase::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Purchase::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Purchase::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Purchase::class)]
class PurchaseAmountEventListener
{
    public function __construct(
        public readonly ProductManager $productManager,
    ) {
    }

    public function postPersist(Purchase $purchase, PostPersistEventArgs $args): void
    {
        $product = $purchase->getProduct();
        $productAmount = $product->getAmount() + $purchase->getAmount();
        $product->setAmount($productAmount);
        $this->productManager->save($product);
    }

    public function preUpdate(Purchase $purchase, PreUpdateEventArgs $args): void
    {
        $product = $purchase->getProduct();
        $purchaseAmount = $purchase->getAmount();

        if ($args->hasChangedField('product')) {
            // Reduce old product amount
            $amount = $args->hasChangedField('amount') ? $args->getOldValue('amount') : $purchaseAmount;
            $oldProduct = $args->getOldValue('product');
            $oldProductAmount = $oldProduct->getAmount() - $amount;
            $oldProduct->setAmount($oldProductAmount);

            // Increase new product amount
            $productAmount = $product->getAmount() + $purchaseAmount;
            $product->setAmount($productAmount);
        }

        // Update existing product amount
        if ($args->hasChangedField('amount')) {
            $productAmount = $product->getAmount() - $args->getOldValue('amount')
                + $args->getNewValue('amount');
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
