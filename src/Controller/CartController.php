<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/cart', name: 'cart')]
    public function cart(): Response
    {
        return $this->render('cart.html.twig', []);
    }
}
