<?php

namespace App\Controller;

use App\Classe\CartService;
use App\Classe\MailService;
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

        // Changement d'état (state)
        if ($order->getState() == 0) {
            $order->setState(1);
            $this->entityManager->flush();

            // Vider le panier
            $cartService->removeFullCart();
        }

        // Envoyer un mail de confirmation
        $mailService = new MailService();
        $content = 'Bravo ' . $order->getUser()->getFirstname() . ', nous vous confirmons votre paiement.';

        $mailService->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Confirmation de paiement !', $content);

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
