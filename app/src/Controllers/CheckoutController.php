<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\Interfaces\IOrderService;
use App\Services\Interfaces\IPaymentService;
use App\Config;
use App\Framework\View;
use App\Framework\Flash;
use App\Middleware\AuthMiddleware;

/**
 * Checkout: turn the cart into an order and take payment via Stripe (test mode).
 * Requires a logged-in user.
 */
class CheckoutController
{
    private IOrderService $orderService;
    private IPaymentService $paymentService;

    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->paymentService = new PaymentService();
    }

    // POST: /checkout — create the order and redirect to Stripe.
    public function start(): void
    {
        AuthMiddleware::requireAuth();
        $userId = (int) $_SESSION['UserId'];

        $result = $this->orderService->createFromCart($userId);
        if (!$result['ok']) {
            Flash::error($result['message']);
            header('Location: /cart');
            exit();
        }

        // Reload with enriched items (names) for the Stripe line items.
        $order = $this->orderService->getById($result['order']->id);
        if ($order === null) {
            Flash::error('Could not load your order. Please try again.');
            header('Location: /cart');
            exit();
        }

        $check = $this->orderService->canStartPayment($order);
        if (!$check['ok']) {
            Flash::error($check['message']);
            header('Location: /cart');
            exit();
        }

        try {
            $url = $this->paymentService->createCheckoutSession(
                $order,
                Config::appUrl() . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
                Config::appUrl() . '/checkout/cancel?order=' . $order->id
            );
        } catch (\Throwable $e) {
            Flash::error('Could not start payment. Please try again.');
            header('Location: /cart');
            exit();
        }

        header('Location: ' . $url);
        exit();
    }

    // GET: /checkout/success — Stripe redirects back here after payment.
    public function success(): void
    {
        AuthMiddleware::requireAuth();

        $sessionId = $_GET['session_id'] ?? '';
        if ($sessionId === '') {
            header('Location: /cart');
            exit();
        }

        // Verify the payment server-side (never trust the client/redirect alone).
        $info = $this->paymentService->retrieveSession($sessionId);
        $order = $info['order_id'] ? $this->orderService->getById($info['order_id']) : null;

        if (!$info['paid'] || $order === null || $order->user_id !== (int) $_SESSION['UserId']) {
            Flash::error('We could not confirm your payment. If you were charged, contact support.');
            header('Location: /cart');
            exit();
        }

        if ($info['payment_intent']) {
            $this->orderService->setPaymentIntent($order->id, $info['payment_intent']);
        }

        // Idempotent: fulfill only flips a pending order to paid + issues tickets.
        $this->orderService->fulfill($order);

        // Reload to show the paid order with its invoice number.
        $order = $this->orderService->getById($order->id);
        View::render('Checkout/success', ['order' => $order], 'Order confirmed');
    }

    // GET: /checkout/cancel - user backed out of Stripe.
    public function cancel(): void
    {
        AuthMiddleware::requireAuth();

        $orderId = (int) ($_GET['order'] ?? 0);
        $order = $orderId > 0
            ? $this->orderService->getByIdForUser($orderId, (int) $_SESSION['UserId'])
            : null;

        if ($order !== null && $order->canPayLater()) {
            $deadline = date('j M Y, H:i', strtotime($order->pay_later_until));
            Flash::error('Payment cancelled. You can still pay this order until ' . $deadline . '.');
            header('Location: /orders');
            exit();
        }

        Flash::error('Payment cancelled.');
        header('Location: /orders');
        exit();
    }
}
