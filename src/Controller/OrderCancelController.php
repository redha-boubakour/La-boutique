<?php

namespace App\Controller;

use App\Classe\MailService;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderCancelController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/erreur/{stripeSessionId}", name="order_cancel")
     */
    public function index($stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // Envoyer un mail d'erreur

        $mailService = new MailService();
        $content = 'Oups.. il y a eu une eurreur lors de votre processus de paiement, veuillez réessayer via le lien ci-dessous.';

        $mailService->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Echec de paiement !', $content);

        return $this->render('order_cancel/index.html.twig', [
            'order' => $order
        ]);
    }
}
