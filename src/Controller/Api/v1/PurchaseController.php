<?php

namespace App\Controller\Api\v1;

use App\DTO\Request\PurchaseRequestDTO;
use App\DTO\Response\PurchaseResponseDTO;
use App\Entity\Purchase;
use App\Manager\PurchaseManager;
use App\Service\PurchaseBuilderService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\HttpKernel\Attribute\{MapQueryParameter, MapQueryString, MapRequestPayload};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/purchase')]
#[OA\Tag(name: 'purchases')]
class PurchaseController extends AbstractController
{
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
                schema: new OA\Schema(ref: new Model(type: PurchaseRequestDTO::class)),
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Purchase is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function savePurchaseAction(#[MapRequestPayload] PurchaseRequestDTO $dto): Response
    {
        $purchase = $this->purchaseBuilderService->createPurchaseWithProduct($dto);
        [$data, $code] = [['success' => true, 'purchaseId' => $purchase->getId()], Response::HTTP_OK];

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
                    items: new OA\Items(ref: new Model(type: PurchaseResponseDTO::class)),
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
            [
                'purchases' => array_map(
                    static fn(Purchase $purchase) => PurchaseResponseDTO::fromEntity($purchase),
                    $purchases,
                )
            ],
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
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Purchase is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function updatePurchaseAction(
        #[MapQueryParameter] int $purchaseId,
        #[MapQueryString] PurchaseRequestDTO $dto,
    ): Response {
        $purchase = $this->purchaseManager->getPurchaseById($purchaseId);
        $result = $this->purchaseBuilderService->updatePurchaseWithProduct($purchase, $dto);

        return new JsonResponse(
            ['success' => $result !== null],
            ($result !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
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
    public function deletePurchaseByIdAction(#[MapEntity(mapping: ['id' => 'id'])] Purchase $purchase): Response
    {
        $result = $this->purchaseManager->deletePurchase($purchase);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
