<?php

namespace App\Controller;

use App\Classe\CartService;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index($stripeSessionId, CartService $cartService): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // isPaid(true)
        if (!$order->getIsPaid()) {
            $order->setIsPaid(1);
            $this->entityManager->flush();

            // Vider le panier
            $cartService->removeFullCart();
        }

        // Envoyer un mail de confirmation

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
