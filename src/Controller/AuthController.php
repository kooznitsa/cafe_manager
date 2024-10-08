<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthController extends AbstractController
{
    public function post(Request $request): Response
    {
        // ...
        $form = null;

        if ($form->isSubmitted() && $form->isValid()) {
            // do some sort of processing

            $this->addFlash(
                "notice",
                "Your changes were saved!"
            );

            return $this->redirectToRoute(/* ... */);
        }

        return $this->render(/* ... */);
    }
}
