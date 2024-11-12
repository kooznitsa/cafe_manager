<?php

namespace App\Service;

use App\DTO\Request\PurchaseRequestDTO;
use App\Entity\Purchase;
use App\Manager\{ProductManager, PurchaseManager};
use DateTime;
use Symfony\Component\HttpFoundation\Request;

class PurchaseBuilderService
{
    private const DEFAULT_PAGE = 0;
    private const DEFAULT_PER_PAGE = 20;
    private const DT_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private readonly ProductManager $productManager,
        private readonly PurchaseManager $purchaseManager,
    ) {
    }

    public function createPurchaseWithProduct(PurchaseRequestDTO $dto): Purchase
    {
        $product = $dto->productId ? $this->productManager->getProductById($dto->productId) : null;

        return $this->purchaseManager->savePurchase($dto, $product);
    }

    public function updatePurchaseWithProduct(Purchase $purchase, PurchaseRequestDTO $dto): ?Purchase
    {
        $product = $dto->productId ? $this->productManager->getProductById($dto->productId) : null;

        return $this->purchaseManager->updatePurchase($purchase, $product, $dto);
    }

    public function getFilterPurchaseParams(Request $request): array
    {
        $page = $request->query->get('page') ?? self::DEFAULT_PAGE;
        $perPage = $request->query->get('perPage') ?? self::DEFAULT_PER_PAGE;
        $dateFrom = $request->query->get('dateFrom');
        if ($dateFrom) {
            $dateFrom = DateTime::createFromFormat(self::DT_FORMAT, $dateFrom);
        }
        $dateTo = $request->query->get('dateTo');
        if ($dateTo) {
            $dateTo = DateTime::createFromFormat(self::DT_FORMAT, $dateTo);
        }
        $productId = $request->query->get('productId');

        return [$page, $perPage, $dateFrom, $dateTo, $productId];
    }
}
