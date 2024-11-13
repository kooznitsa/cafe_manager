<?php

namespace App\Controller;

use App\Manager\DishManager;
use App\Service\{CartService, OrderBuilderService};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderBuilderService $orderBuilderService,
        private readonly DishManager $dishManager,
    ) {
    }

    #[Route('/cart', name: 'cart')]
    public function cart(): Response
    {
        $cart = $this->getCart();

        return $this->render('cart.html.twig', ['cart' => $cart]);
    }

    #[Route('/pay', name: 'pay_orders')]
    public function payOrders(): Response
    {
        $cart = $this->getCart();
        $orderIds = array_map(fn($order) => $order['id'], $cart['orders']);
        foreach ($orderIds as $orderId) {
            $this->orderBuilderService->payOrder($orderId);
        }

        return $this->render('paidOrders.html.twig', ['cart' => $cart]);
    }

    #[Route('/order/{dishId}', name: 'make_order', requirements: ['dishId' => '\d+'])]
    public function makeOrder(Request $request, int $dishId): Response
    {
        try {
            $this->cartService->putToCart($dishId, $this->getUser()->getId());
        } catch (\RuntimeException) {
            $dishName = $this->dishManager->getDishById($dishId)->getName();
            $this->addFlash('warning', "Блюда $dishName нет в наличии.");
        }

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/delete/{orderId}', name: 'delete_order', requirements: ['orderId' => '\d+'])]
    public function deleteOrder(Request $request, int $orderId): Response
    {
        $orderId = $request->get('orderId');
        $this->cartService->deleteFromCart($orderId);

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/delivery', name: 'toggle_delivery')]
    public function toggleDelivery(Request $request): Response
    {
        $cart = $this->getCart();
        $orderIds = array_map(fn($order) => $order['id'], $cart['orders']);
        $this->cartService->toggleDelivery($orderIds, $request->get('isDelivery'));

        return $this->redirect($request->headers->get('referer'));
    }

    private function getCart(): array
    {
        $userId = $this->getUser()->getId();

        return $this->cartService->getCart($userId);
    }
}
