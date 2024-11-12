<?php

namespace App\Controller\Api\v1;

use App\DTO\Request\ProductRequestDTO;
use App\DTO\Response\ProductResponseDTO;
use App\Entity\Product;
use App\Manager\ProductManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Response};
use Symfony\Component\HttpKernel\Attribute\{MapQueryParameter, MapQueryString, MapRequestPayload};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/product')]
#[OA\Tag(name: 'products')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductManager $productManager,
    ) {
    }

    /**
     * Creates product.
     */
    #[Route(path: '', methods: ['POST'])]
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: ProductRequestDTO::class)),
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Product is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function saveProductAction(#[MapRequestPayload] ProductRequestDTO $dto): Response
    {
        $product = $this->productManager->saveProduct($dto);
        [$data, $code] = [['success' => true, 'categoryId' => $product->getId()], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * Lists all products.
     */
    #[Route(path: '', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of products is retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'orders',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ProductResponseDTO::class))
                ),
            ],
            type: 'object',
        )
    )]
    public function getProductsAction(): Response
    {
        $products = $this->productManager->getProducts();
        $code = empty($products) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['products' => array_map(fn(Product $product) => ProductResponseDTO::fromEntity($product), $products)],
            $code,
        );
    }

    /**
     * Updates product.
     */
    #[Route(path: '', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'productId',
        description: 'Product ID',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Product is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function updateProductAction(
        #[MapQueryParameter] int $productId,
        #[MapQueryString] ProductRequestDTO $dto,
    ): Response {
        $product = $this->productManager->getProductById($productId);
        $result = $this->productManager->updateProduct($product, $dto);

        return new JsonResponse(
            ['success' => $result !== null],
            ($result !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
        );
    }

    /**
     * Deletes product by ID.
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Product is deleted successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function deleteProductByIdAction(
        #[MapEntity(mapping: ['id' => 'id'])] Product $product,
    ): Response {
        $result = $this->productManager->deleteProduct($product);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
