<?php

namespace App\Manager;

use App\Entity\{Product, Purchase};
use App\Repository\PurchaseRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class PurchaseManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PurchaseRepository $purchaseRepository,
    ) {
    }

    public function savePurchase(Product $product, float $price, float $amount): ?int
    {
        $purchase = new Purchase();
        $this->setPurchaseParams($purchase, $product, $price, $amount);
        $this->entityManager->persist($purchase);
        $this->entityManager->flush();

        return $purchase->getId();
    }

    /**
     * @return Purchase[]
     */
    public function getPurchases(
        int $page,
        int $perPage,
        ?DateTime $dateFrom,
        ?DateTime $dateTo,
        ?int $productId,
    ): array {
        return $this->purchaseRepository->getPurchases($page, $perPage, $dateFrom, $dateTo, $productId);
    }

    public function getPurchaseById(int $id): ?Purchase
    {
        return $this->purchaseRepository->find($id);
    }

    public function updatePurchase(
        int $purchaseId,
        ?Product $product = null,
        ?float $price = null,
        ?float $amount = null,
    ): ?Purchase {
        /** @var Purchase $purchase */
        $purchase = $this->getPurchaseById($purchaseId);
        if (!$purchase) {
            return null;
        }
        $purchaseProduct = $purchase->getProduct();
        $purchaseProduct->removePurchase($purchase);
        $this->setPurchaseParams($purchase, $product, $price, $amount);
        $product?->addPurchase($purchase);
        $this->entityManager->flush();

        return $purchase;
    }

    public function deletePurchase(Purchase $purchase): bool
    {
        $this->entityManager->remove($purchase);
        $purchase->getProduct()->removePurchase($purchase);
        $this->entityManager->flush();

        return true;
    }

    public function deletePurchaseById(int $purchaseId): bool
    {
        /** @var Purchase $purchase */
        $purchase = $this->getPurchaseById($purchaseId);
        if (!$purchase) {
            return false;
        }
        return $this->deletePurchase($purchase);
    }

    private function setPurchaseParams(Purchase $purchase, ?Product $product, ?float $price, ?float $amount): void
    {
        $purchase->setProduct($product);
        $purchase->setPrice($price);
        $purchase->setAmount($amount);
    }
}
