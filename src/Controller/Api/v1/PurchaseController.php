<?php

namespace App\Controller\Api\v1;

use App\Entity\Purchase;
use App\Manager\PurchaseManager;
use App\Service\PurchaseBuilderService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/purchase')]
#[OA\Tag(name: 'purchases')]
class PurchaseController extends AbstractController
{
    private const DEFAULT_PAGE = 0;
    private const DEFAULT_PER_PAGE = 20;

    public function __construct(
        private readonly PurchaseManager $purchaseManager,
        private readonly PurchaseBuilderService $purchaseBuilderService,
    ) {
    }

    /**
     * Creates purchase.
     */
    #[Route(path: '', methods: ['POST'])]
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['productId', 'price', 'amount'],
                    properties: [
                        new OA\Property(property: 'productId', type: 'integer'),
                        new OA\Property(property: 'price', type: 'float'),
                        new OA\Property(property: 'amount', type: 'float'),
                    ]
                )
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Purchase is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function savePurchaseAction(Request $request): Response
    {
        $purchaseId = $this->purchaseBuilderService->createPurchaseWithProduct($request);

        [$data, $code] = $purchaseId === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'purchaseId' => $purchaseId], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * Lists purchases.
     */
    #[Route(path: '/', methods: ['GET'])]
    #[OA\Parameter(name: 'page', description: 'Page', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'perPage', description: 'Per page', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'dateFrom', description: 'Date from', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'dateTo', description: 'Date to', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'productId', description: 'Product ID', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of purchases is retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'purchases',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Purchase::class, groups: ['default']))
                ),
            ],
            type: 'object'
        )
    )]
    public function getPurchasesAction(Request $request): Response
    {
        [$page, $perPage, $dateFrom, $dateTo, $productId] = $this->purchaseBuilderService
            ->getFilterPurchaseParams($request);

        $purchases = $this->purchaseManager->getPurchases($page, $perPage, $dateFrom, $dateTo, $productId);
        $code = empty($purchases) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['purchases' => array_map(static fn(Purchase $purchase) => $purchase->toArray(), $purchases)],
            $code,
        );
    }

    /**
     * Updates purchase.
     */
    #[Route(path: '', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'purchaseId',
        description: 'Purchase ID',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(name: 'productId', description: 'Product ID', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'price', description: 'Purchase price', in: 'query', schema: new OA\Schema(type: 'float'))]
    #[OA\Parameter(name: 'amount', description: 'Purchase amount', in: 'query', schema: new OA\Schema(type: 'float'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Purchase is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function updatePurchaseAction(Request $request): Response
    {
        $purchase = $this->purchaseBuilderService->updatePurchaseWithProduct($request);

        return new JsonResponse(
            ['success' => $purchase !== null],
            ($purchase !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
        );
    }

    /**
     * Deletes purchase by ID.
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Purchase is deleted successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function deletePurchaseByIdAction(int $id): Response
    {
        $result = $this->purchaseManager->deletePurchaseById($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
