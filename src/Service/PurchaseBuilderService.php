<?php

namespace App\Service;

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

    public function createPurchaseWithProduct(Request $request): ?int
    {
        [$productId, $price, $amount] = $this->getPurchaseParams($request);
        $product = $this->productManager->getProductById($productId);
        if ($productId) {
            return $this->purchaseManager->savePurchase($product, $price, $amount);
        }
        return null;
    }

    public function updatePurchaseWithProduct(Request $request): ?Purchase
    {
        [$product, $price, $amount] = $this->getPurchaseParams($request, 'PATCH');
        $purchaseId = $request->query->get('purchaseId');
        if ($product) {
            $product = $this->productManager->getProductById($product);
        }
        return $this->purchaseManager->updatePurchase($purchaseId, $product, $price, $amount);
    }

    public function getPurchaseParams(Request $request, string $requestMethod = 'POST'): array
    {
        $inputBag = $requestMethod === 'POST' ? $request->request : $request->query;

        $price = $inputBag->get('price');
        $amount = $inputBag->get('amount');
        $productId = $inputBag->get('productId');

        return [$productId, $price, $amount];
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
