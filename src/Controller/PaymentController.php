<?php

// src/Controller/PaymentController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\JsonResponse;

class PaymentController extends AbstractController
{
    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        $stripeSecretKey = $this->getParameter('stripe_secret_key');
        Stripe::setApiKey($stripeSecretKey);

        // Create a PaymentIntent with the order amount and currency
        $paymentIntent = PaymentIntent::create([
            'amount' => 1000, // should be order total, in cents
            'currency' => 'usd',
            'metadata' => ['integration_check' => 'accept_a_payment'],
        ]);

        return $this->render('payment/index.html.twig', [
            'clientSecret' => $paymentIntent->client_secret,
            'stripePublicKey' => $this->getParameter('stripe_public_key'), // Pass the public key to the template
        ]);
    }

    #[Route('/payment/confirm', name: 'app_payment_confirm', methods: ['POST'])]
    public function confirm(Request $request): Response
    {
        $stripeSecretKey = $this->getParameter('stripe_secret_key');
        $stripe = new StripeClient($stripeSecretKey);

        $token = $request->request->get('stripeToken');
        $amount = $request->request->get('amount'); // The amount should be calculated based on the order

        try {
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => 'usd',
                'description' => 'Your payment description',
                'confirm' => true,
                'payment_method' => $token,
            ]);

            // You can add additional logic here, such as saving the payment information to your database.

            // Return a JsonResponse for AJAX requests or redirect for synchronous requests
            return new JsonResponse(['success' => true, 'message' => 'Payment confirmed!']);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle the exception as needed
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
