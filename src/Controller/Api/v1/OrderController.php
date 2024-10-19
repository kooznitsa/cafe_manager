<?php

namespace App\Controller\Api\v1;

use App\Enum\Status;
use App\Entity\{Dish, Order, User};
use App\Manager\OrderManager;
use App\Service\OrderBuilderService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/order')]
#[OA\Tag(name: 'orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderManager $orderManager,
        private readonly OrderBuilderService $orderBuilderService,
    ) {
    }

    /**
     * Creates order.
     */
    #[Route(path: '', methods: ['POST'])]
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['dishId', 'userId', 'status', 'isDelivery'],
                    properties: [
                        new OA\Property(property: 'dishId', type: 'integer'),
                        new OA\Property(property: 'userId', type: 'integer'),
                        new OA\Property(property: 'status', type: 'string', enum: Status::class),
                        new OA\Property(property: 'isDelivery', type: 'boolean'),
                    ]
                )
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function saveDishAction(Request $request): Response {
        $orderId = $this->orderBuilderService->createOrderWithUserAndDish($request);

        [$data, $code] = $orderId === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'dishId' => $orderId], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * Lists orders by user.
     */
    #[Route(path: '/by-user/{user_id}', requirements: ['user_id' => '\d+'], methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of user orders is retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'orders',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Order::class, groups: ['default']))
                ),
            ],
            type: 'object'
        )
    )]
    public function getUserOrdersAction(
        Request $request,
        #[MapEntity(mapping: ['user_id' => 'id'])] User $user,
    ): Response {
        $orders = $this->orderManager->getUserOrders($user);
        $code = empty($orders) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['orders' => array_map(static fn(Order $order) => $order->toArray(), $orders)],
            $code,
        );
    }

    /**
     * Lists orders by dish.
     */
    #[Route(path: '/by-dish/{dish_id}', requirements: ['dish_id' => '\d+'], methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of dish orders is retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'orders',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Order::class, groups: ['default']))
                ),
            ],
            type: 'object'
        )
    )]
    public function getDishOrdersAction(
        Request $request,
        #[MapEntity(mapping: ['dish_id' => 'id'])] Dish $dish,
    ): Response {
        $orders = $this->orderManager->getDishOrders($dish);
        $code = empty($orders) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['orders' => array_map(static fn(Order $order) => $order->toArray(), $orders)],
            $code,
        );
    }

    /**
     * Updates order.
     */
    #[Route(path: '', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'orderId',
        description: 'Order ID',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(name: 'dishId', description: 'Dish ID', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'userId', description: 'User ID', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(
        name: 'status',
        description: 'Order status',
        in: 'query',
        schema: new OA\Schema(type: 'string', enum: Status::class),
    )]
    #[OA\Parameter(
        name: 'isDelivery',
        description: 'Order for delivery',
        in: 'query',
        schema: new OA\Schema(type: 'boolean'),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function updateDishAction(Request $request): Response
    {
        $order = $this->orderBuilderService->updateOrderWithUserAndDish($request);

        return new JsonResponse(
            ['success' => $order !== null],
            ($order !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
        );
    }

    /**
     * Deletes order by ID.
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order is deleted successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function deleteOrderByIdAction(int $id): Response
    {
        $result = $this->orderManager->deleteOrderById($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * Performs payment.
     */
    #[Route(path: '/pay/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order is paid successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function payOrderAction(int $id): Response
    {
        $result = $this->orderBuilderService->payOrder($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * Delivers order.
     */
    #[Route(path: '/deliver/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order is delivered successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function deliverOrderAction(int $id): Response
    {
        $result = $this->orderBuilderService->deliverOrder($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * Cancels order.
     */
    #[Route(path: '/cancel/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order is cancelled successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function cancelOrderAction(int $id): Response
    {
        $result = $this->orderBuilderService->cancelOrder($id);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
