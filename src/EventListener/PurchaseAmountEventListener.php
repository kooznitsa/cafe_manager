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
        $this->productManager->updateProduct($product, amount: $productAmount);
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
                $this->productManager->updateProduct($oldProduct, amount: $oldProductAmount, isFlush: false);

                $product = $args->getNewValue('product');
                $productAmount = $product->getAmount() + $args->getNewValue('amount');
            }

            $this->productManager->updateProduct($product, amount: $productAmount, isFlush: false);
        }

        if ($args->hasChangedField('product')) {
            $oldProduct = $args->getOldValue('product');
            $oldProductAmount = $oldProduct->getAmount() - $purchase->getAmount();
            $this->productManager->updateProduct($oldProduct, amount: $oldProductAmount, isFlush: false);

            $product = $purchase->getProduct();
            $productAmount = $product->getAmount() + $purchase->getAmount();
            $this->productManager->updateProduct($product, amount: $productAmount, isFlush: false);
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
        $this->productManager->updateProduct($product, amount: $productAmount, isFlush: false);
    }
}
