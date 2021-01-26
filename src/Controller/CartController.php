<?php

namespace App\Controller;

use App\Classe\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\component\Routing\Annotation\Route;

class CartController extends AbstractController 
{
    /**
    * @Route("/panier", name="cart")
    */
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal()
        ]);
    }

    /**
    * @route("/panier/add/{id}", name="cart_add")
    */
    public function add($id, CartService $cartService)
    {
        $cartService->add($id);

        return $this->redirectToRoute('cart');
    }

    /**
    * @route("/panier/remove/{id}", name="cart_remove")
    */
    public function remove($id, CartService $cartService)
    {
        $cartService->remove($id);

        return $this->redirectToRoute('cart');
    }
}