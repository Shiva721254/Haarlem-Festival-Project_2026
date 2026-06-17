<?php

namespace App\Controllers;

use App\Services\Interfaces\IOrderService;
use App\Services\Interfaces\IPaymentService;

/**
 * Stripe webhook receiver. Makes order fulfilment robust: even if the buyer
 * never returns to the success page, Stripe notifies us that payment completed
 * and we fulfil the order (idempotently).
 */
class WebhookController
{
    private IOrderService $orderService;
    private IPaymentService $paymentService;

    public function __construct(IOrderService $orderService, IPaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    // POST: /webhook/stripe
    public function stripe(): void
    {
        $payload = file_get_contents('php://input') ?: '';
        $parsed = $this->paymentService->parseWebhookEvent($payload, $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
        if (!$parsed['ok']) {
            $this->badRequest($parsed['error'] ?? 'Invalid payload');
        }
        $this->orderService->fulfillPaidCheckout($this->paymentService->completedCheckoutInfo($parsed['event']));
        http_response_code(200);
        echo 'ok';
    }

    private function badRequest(string $message): never
    {
        http_response_code(400);
        echo $message;
        exit();
    }
}
