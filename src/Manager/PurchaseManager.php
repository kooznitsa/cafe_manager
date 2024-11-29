<?php

namespace App\Manager;

use App\DTO\Request\PurchaseRequestDTO;
use App\Entity\{Product, Purchase};
use App\Repository\PurchaseRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PurchaseManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PurchaseRepository $purchaseRepository,
    ) {
    }

    public function save(Purchase $purchase): void
    {
        $this->entityManager->persist($purchase);
        $this->entityManager->flush();
    }

    public function savePurchase(PurchaseRequestDTO $dto, ?Product $product): ?Purchase
    {
        if ($product !== null) {
            $purchase = new Purchase();
            $this->setPurchaseParams($purchase, $product, $dto);
            $this->save($purchase);

            return $purchase;
        }

        return null;
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

    public function updatePurchase(?Purchase $purchase, ?Product $product, PurchaseRequestDTO $dto): ?Purchase
    {
        if (!$purchase) {
            throw new UnprocessableEntityHttpException('Purchase does not exist');
        }

        $purchaseProduct = $purchase->getProduct();
        if ($product) {
            $purchaseProduct->removePurchase($purchase);
        }
        $this->setPurchaseParams($purchase, $product, $dto);
        $product?->addPurchase($purchase);
        $this->entityManager->flush();

        return $purchase;
    }

    public function deletePurchase(?Purchase $purchase): bool
    {
        if (!$purchase) {
            return false;
        }
        $this->entityManager->remove($purchase);
        $purchase->getProduct()->removePurchase($purchase);
        $this->entityManager->flush();

        return true;
    }

    private function setPurchaseParams(Purchase $purchase, Product $product, PurchaseRequestDTO $dto): void
    {
        $purchase->setProduct($product)->setPrice($dto->price)->setAmount($dto->amount);
    }
}
