<?php

namespace App\Controllers;

use App\Config;
use App\Services\OrderService;
use App\Services\Interfaces\IOrderService;

/**
 * Stripe webhook receiver. Makes order fulfilment robust: even if the buyer
 * never returns to the success page, Stripe notifies us that payment completed
 * and we fulfil the order (idempotently).
 */
class WebhookController
{
    private IOrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    // POST: /webhook/stripe
    public function stripe(): void
    {
        $payload = file_get_contents('php://input') ?: '';
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret = Config::stripeWebhookSecret();

        try {
            if ($secret !== '') {
                // Verify the signature so only genuine Stripe events are trusted.
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
            } else {
                // No secret configured (local dev): accept the unverified payload.
                $event = json_decode($payload);
                if (!is_object($event)) {
                    http_response_code(400);
                    echo 'Invalid payload';
                    return;
                }
            }
        } catch (\Throwable $e) {
            http_response_code(400);
            echo 'Invalid signature';
            return;
        }

        if (($event->type ?? '') === 'checkout.session.completed') {
            $session = $event->data->object ?? null;
            $orderId = isset($session->metadata->order_id) ? (int) $session->metadata->order_id : 0;
            $paid = ($session->payment_status ?? '') === 'paid';

            if ($orderId > 0 && $paid) {
                $order = $this->orderService->getById($orderId);
                if ($order !== null) {
                    $pi = is_string($session->payment_intent ?? null) ? $session->payment_intent : null;
                    if ($pi !== null) {
                        $this->orderService->setPaymentIntent($order->id, $pi);
                    }
                    $this->orderService->fulfill($order); // idempotent: pending -> paid + tickets
                }
            }
        }

        http_response_code(200);
        echo 'ok';
    }
}
