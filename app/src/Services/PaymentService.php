<?php
namespace App\Services;

use App\Config;
use App\Models\OrderModel;
use App\Services\Interfaces\IPaymentService;
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

    public function createCheckoutSession(OrderModel $order, string $successUrl, string $cancelUrl): string
    {
        $lineItems = [];
        foreach ($order->items as $item) {
            $label = $item->ticket_type_name ?? 'Ticket';
            if (!empty($item->event_title)) {
                $label .= ' — ' . $item->event_title;
            }
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => (int) round($item->unit_price * 100), // cents
                    'product_data' => ['name' => $label],
                ],
                'quantity' => $item->quantity,
            ];
        }

        $session = Session::create([
            'mode'                 => 'payment',
            'payment_method_types' => ['ideal', 'card'],
            'line_items'           => $lineItems,
            'success_url'          => $successUrl,
            'cancel_url'           => $cancelUrl,
            'metadata'             => ['order_id' => (string) $order->id],
        ]);

        return $session->url;
    }

    public function retrieveSession(string $sessionId): array
    {
        $session = Session::retrieve($sessionId);

        $paymentIntent = null;
        if (is_string($session->payment_intent)) {
            $paymentIntent = $session->payment_intent;
        } elseif (is_object($session->payment_intent)) {
            $paymentIntent = $session->payment_intent->id ?? null;
        }

        return [
            'paid'           => $session->payment_status === 'paid',
            'payment_intent' => $paymentIntent,
            'order_id'       => isset($session->metadata->order_id) ? (int) $session->metadata->order_id : null,
        ];
    }
}
