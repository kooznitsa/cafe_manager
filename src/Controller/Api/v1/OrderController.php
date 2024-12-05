<?php

namespace App\Controller\Api\v1;

use App\DTO\Request\OrderRequestDTO;
use App\DTO\Response\OrderResponseDTO;
use App\Entity\{Dish, Order, User};
use App\Manager\OrderManager;
use App\Service\{AsyncService, OrderBuilderService};
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\HttpKernel\Attribute\{MapQueryParameter, MapQueryString, MapRequestPayload};
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/order')]
#[OA\Tag(name: 'orders')]
class OrderController extends AbstractController
{
    private const DEFAULT_PAGE = 0;
    private const DEFAULT_PER_PAGE = 20;
    private const AGGREGATION_FIELD = 'status';

    public function __construct(
        private readonly OrderManager $orderManager,
        private readonly OrderBuilderService $orderBuilderService,
        private readonly AsyncService $asyncService,
        private readonly LoggerInterface $elasticsearchLogger,
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
                schema: new OA\Schema(ref: new Model(type: OrderRequestDTO::class)),
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function saveOrderAction(
        #[MapRequestPayload] OrderRequestDTO $dto,
    ): Response {
        $this->elasticsearchLogger->info('Creating new order');
        $order = $this->orderBuilderService->createOrderWithUserAndDish($dto);

        if ($order === null) {
            $this->elasticsearchLogger->error("Creation error");
            [$data, $code] = [['success' => false], Response::HTTP_BAD_REQUEST];
        } else {
            $this->elasticsearchLogger->info("New order created with ID {$order->getId()}");
            [$data, $code] = [['success' => true, 'orderId' => $order->getId()], Response::HTTP_OK];
        }

        return new JsonResponse($data, $code);
    }

    /**
     * Creates order with queue.
     */
    #[Route(path: '/q/{isAsync}', requirements: ['isAsync' => '0|1'], methods: ['POST'])]
    #[OA\RequestBody(
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: OrderRequestDTO::class)),
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order is created successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function saveOrderWithQueueAction(
        #[MapRequestPayload] OrderRequestDTO $dto,
        int $isAsync,
    ): Response {
        if ($isAsync === 0) {
            $order = $this->orderBuilderService->createOrderWithUserAndDish($dto);
        } else {
            $message = $dto->toAMQPMessage();
            $order = $this->asyncService->publishToExchange(AsyncService::CREATE_ORDER, $message);
        }

        [$data, $code] = $order === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'orderId' => $order->getId()], Response::HTTP_OK];

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
                    items: new OA\Items(ref: new Model(type: OrderResponseDTO::class)),
                ),
            ],
            type: 'object'
        )
    )]
    public function getUserOrdersAction(
        #[MapEntity(mapping: ['user_id' => 'id'])] User $user,
    ): Response {
        $orders = $this->orderManager->getUserOrders($user);
        $code = empty($orders) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(['orders' => $orders], $code);
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
                    items: new OA\Items(ref: new Model(type: OrderResponseDTO::class))
                ),
            ],
            type: 'object'
        )
    )]
    public function getDishOrdersAction(
        #[MapEntity(mapping: ['dish_id' => 'id'])] Dish $dish,
    ): Response {
        $orders = $this->orderManager->getDishOrders($dish);
        $code = empty($orders) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(
            ['orders' => array_map(fn(Order $order) => OrderResponseDTO::fromEntity($order), $orders)],
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
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order is updated successfully.',
        content: new OA\JsonContent(example: ['success' => true]),
    )]
    public function updateOrderAction(
        #[MapQueryParameter] int $orderId,
        #[MapQueryString] OrderRequestDTO $dto,
    ): Response {
        $order = $this->orderManager->getOrderById($orderId);
        $result = $this->orderBuilderService->updateOrderWithUserAndDish($order, $dto);

        return new JsonResponse(
            ['success' => $result !== null],
            ($result !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND,
        );
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
    public function payOrderAction(
        #[MapEntity(mapping: ['id' => 'id'])] Order $order,
    ): Response {
        $result = $this->orderBuilderService->payOrder($order);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
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
    public function deliverOrderAction(
        #[MapEntity(mapping: ['id' => 'id'])] Order $order,
    ): Response {
        $result = $this->orderBuilderService->deliverOrder($order);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
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
    public function cancelOrderAction(
        #[MapEntity(mapping: ['id' => 'id'])] Order $order,
    ): Response {
        $result = $this->orderBuilderService->cancelOrder($order);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
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
    public function deleteOrderByIdAction(
        #[MapEntity(mapping: ['id' => 'id'])] Order $order,
    ): Response {
        $result = $this->orderBuilderService->deleteOrder($order);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * Returns total sales per day.
     */
    #[Route(path: '/stats', methods: ['GET'])]
    #[OA\Parameter(name: 'page', description: 'Page', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'perPage', description: 'Per page', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order stats are retrieved successfully.',
        content: new OA\JsonContent(example: ['orders' => [["orderDate" => "2024-10-18", "total" => "330.00"]]]),
    )]
    public function getPaidOrdersAction(Request $request): Response
    {
        $perPage = $request->query->get('perPage') ?? self::DEFAULT_PER_PAGE;
        $page = $request->query->get('page') ?? self::DEFAULT_PAGE;
        $orders = $this->orderManager->getPaidOrders($page, $perPage);

        $code = empty($orders) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;

        return new JsonResponse(['orders' => $orders], $code);
    }

    /**
     * Get orders by query.
     */
    #[Route(path: '/get-orders-by-query', methods: ['GET'])]
    #[OA\Parameter(name: 'query', description: 'Query string', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', description: 'Page', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'perPage', description: 'Per page', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Array of orders is retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'orders',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: OrderResponseDTO::class)),
                ),
            ],
            type: 'object'
        )
    )]
    public function getOrdersByQueryAction(Request $request): Response
    {
        $query = $request->query->get('query') ?? '';
        $perPage = $request->query->get('perPage') ?? self::DEFAULT_PER_PAGE;
        $page = $request->query->get('page') ?? self::DEFAULT_PAGE;
        $orders = $this->orderManager->findOrdersByQuery($query, $perPage, $page);

        return new JsonResponse(
            ['orders' => array_map(fn(Order $order) => OrderResponseDTO::fromEntity($order), $orders)],
            empty($orders) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK,
        );
    }

    /**
     * Get orders with aggregation.
     */
    #[Route(path: '/get-orders-with-aggregation', methods: ['GET'])]
    #[OA\Parameter(name: 'field', description: 'Field', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Orders grouped by status are retrieved successfully.',
        content: new OA\JsonContent(
            example: ['orders' => [
                'doc_count_error_upper_bound' => 0,
                'sum_other_doc_count' => 0,
                'buckets' => [
                    ['key' => 'created', 'doc_count' => 24],
                    ['key' => 'paid', 'doc_count' => 9],
                    ['key' => 'deleted', 'doc_count' => 3],
                    ['key' => 'cancelled', 'doc_count' => 1],
                ]
            ]],
        ),
    )]
    public function getOrdersWithAggregationAction(Request $request): Response
    {
        $queryString = $request->query->get('queryString') ?? '';
        $field = $request->query->get('field') ?? self::AGGREGATION_FIELD;
        $orders = $this->orderManager->findOrdersWithAggregation($queryString, $field);

        return new JsonResponse($orders, empty($orders) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK);
    }
}
