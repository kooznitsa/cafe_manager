<?php

namespace App\Controller\Api\v1;

use App\DTO\Request\ProductRequestDTO;
use App\DTO\Response\ProductResponseDTO;
use App\Entity\Product;
use App\Manager\ProductManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
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
    public function saveProductAction(Request $request): Response
    {
        $name = $request->get('name');
        $unit = $request->get('unit');
        $productId = $this->productManager->saveProduct($name, $unit);
        [$data, $code] = $productId === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'categoryId' => $productId], Response::HTTP_OK];

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
            type: 'object'
        )
    )]
    public function getProductsAction(Request $request): Response
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
    #[OA\Parameter(name: 'name', description: 'Product name', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'unit', description: 'Product unit', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Product is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function updateProductAction(Request $request): Response
    {
        $productId = $request->query->get('productId');
        $name = $request->query->get('name');
        $unit = $request->query->get('unit');
        $result = $this->productManager->updateProduct($productId, $name, $unit);

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
    public function deleteProductByIdAction(int $id): Response
    {
        $result = $this->productManager->deleteProductById($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
