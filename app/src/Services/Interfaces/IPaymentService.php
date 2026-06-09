<?php
namespace App\Services\Interfaces;

use App\Models\OrderModel;

interface IPaymentService
{
    /**
     * Create a hosted Stripe Checkout session for an order and return the URL
     * the customer should be redirected to.
     */
    public function createCheckoutSession(OrderModel $order, string $successUrl, string $cancelUrl): string;

    /**
     * Look up a checkout session and report whether it was paid.
     *
     * @return array{paid:bool,payment_intent:?string,order_id:?int}
     */
    public function retrieveSession(string $sessionId): array;
}
