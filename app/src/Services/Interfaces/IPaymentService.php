<?php
namespace App\Services\Interfaces;

use App\Models\OrderModel;

interface IPaymentService
{
    /**
     * Create a checkout session using the app's standard success/cancel URLs and
     * return the URL the customer should be redirected to.
     */
    public function startCheckout(OrderModel $order): string;

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

    /** @return array{ok:bool,event:?object,error:?string} */
    public function parseWebhookEvent(string $payload, string $signature): array;

    /** @return array{paid:bool,payment_intent:?string,order_id:?int} */
    public function completedCheckoutInfo(object $event): array;
}
