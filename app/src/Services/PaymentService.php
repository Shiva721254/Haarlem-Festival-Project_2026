<?php
namespace App\Services;

use App\Config;
use App\Models\OrderModel;
use App\Services\Interfaces\IPaymentService;
use App\Framework\Flash;
use App\Framework\Redirect;
use Stripe\Stripe;
use Stripe\Checkout\Session;

/**
 * Stripe Checkout integration (test mode in development). Supports iDEAL and
 * card. Keys are read from config (.env), never hardcoded.
 */
class PaymentService implements IPaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(Config::stripeSecretKey());
    }

    public function startCheckout(OrderModel $order): string
    {
        return $this->createCheckoutSession(
            $order,
            Config::appUrl() . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
            Config::appUrl() . '/checkout/cancel?order=' . $order->id
        );
    }

    public function startCheckoutOrBail(OrderModel $order, string $fallbackUrl): never
    {
        try {
            $url = $this->startCheckout($order);
        } catch (\Throwable $e) {
            Flash::error('Could not start payment. Please try again.');
            Redirect::to($fallbackUrl);
        }
        Redirect::to($url);
    }

    public function createCheckoutSession(OrderModel $order, string $successUrl, string $cancelUrl): string
    {
        $session = Session::create([
            'mode'                 => 'payment',
            'payment_method_types' => ['ideal', 'card'],
            'line_items'           => $this->buildLineItems($order),
            'success_url'          => $successUrl,
            'cancel_url'           => $cancelUrl,
            'metadata'             => ['order_id' => (string) $order->id],
        ]);

        return $session->url;
    }

    /** @return array<int,array<string,mixed>> */
    private function buildLineItems(OrderModel $order): array
    {
        return array_map(fn($item) => $this->buildLineItem($item), $order->items);
    }

    /** @return array<string,mixed> */
    private function buildLineItem(object $item): array
    {
        return [
            'price_data' => [
                'currency'     => 'eur',
                'unit_amount'  => (int) round($item->unit_price * 100), // cents
                'product_data' => ['name' => $this->lineLabel($item)],
            ],
            'quantity' => $item->quantity,
        ];
    }

    private function lineLabel(object $item): string
    {
        $label = $item->ticket_type_name ?? 'Ticket';
        return empty($item->event_title) ? $label : $label . ' — ' . $item->event_title;
    }

    public function retrieveSession(string $sessionId): array
    {
        $session = Session::retrieve($sessionId);

        return [
            'paid'           => $session->payment_status === 'paid',
            'payment_intent' => $this->resolvePaymentIntent($session->payment_intent),
            'order_id'       => isset($session->metadata->order_id) ? (int) $session->metadata->order_id : null,
        ];
    }

    private function resolvePaymentIntent($paymentIntent): ?string
    {
        if (is_string($paymentIntent)) {
            return $paymentIntent;
        }
        if (is_object($paymentIntent)) {
            return $paymentIntent->id ?? null;
        }
        return null;
    }

    public function parseWebhookEvent(string $payload, string $signature): array
    {
        try {
            return ['ok' => true, 'event' => $this->constructWebhookEvent($payload, $signature), 'error' => null];
        } catch (\Throwable $e) {
            return ['ok' => false, 'event' => null, 'error' => 'Invalid signature'];
        }
    }

    private function constructWebhookEvent(string $payload, string $signature): object
    {
        $secret = Config::stripeWebhookSecret();
        return $secret !== ''
            ? \Stripe\Webhook::constructEvent($payload, $signature, $secret)
            : $this->decodeUnverifiedWebhook($payload);
    }

    private function decodeUnverifiedWebhook(string $payload): object
    {
        $event = json_decode($payload);
        if (!is_object($event)) {
            throw new \RuntimeException('Invalid payload');
        }
        return $event;
    }

    public function completedCheckoutInfo(object $event): array
    {
        if (($event->type ?? '') !== 'checkout.session.completed') {
            return ['paid' => false, 'payment_intent' => null, 'order_id' => null];
        }
        return $this->sessionInfo($event->data->object ?? null);
    }

    private function sessionInfo(?object $session): array
    {
        return [
            'paid' => ($session->payment_status ?? '') === 'paid',
            'payment_intent' => $this->resolvePaymentIntent($session->payment_intent ?? null),
            'order_id' => isset($session->metadata->order_id) ? (int)$session->metadata->order_id : null,
        ];
    }
}
