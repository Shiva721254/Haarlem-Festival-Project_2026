<?php

namespace App\Controllers;

use App\Services\Interfaces\IOrderService;
use App\Services\Interfaces\IPaymentService;
use App\Framework\View;
use App\Framework\Flash;
use App\Framework\Redirect;
use App\Middleware\AuthMiddleware;

/**
 * Checkout: turn the cart into an order and take payment via Stripe (test mode).
 * Requires a logged-in user.
 */
class CheckoutController
{
    private IOrderService $orderService;
    private IPaymentService $paymentService;

    public function __construct(IOrderService $orderService, IPaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    // POST: /checkout — create the order and redirect to Stripe.
    public function start(): void
    {
        $userId = AuthMiddleware::userId();
        $result = $this->orderService->createFromCart($userId, session_id());
        if (!$result['ok']) {
            $this->bailToCart($result['message']);
        }
        $order = $this->loadPayableOrder($result['order']->id);
        $this->paymentService->startCheckoutOrBail($order, '/cart');
    }

    /** Reload the order (with enriched item names) and verify it can be paid. */
    private function loadPayableOrder(int $orderId): object
    {
        $order = $this->orderService->getById($orderId);
        if ($order === null) {
            $this->bailToCart('Could not load your order. Please try again.');
        }
        $check = $this->orderService->canStartPayment($order);
        if (!$check['ok']) {
            $this->bailToCart($check['message']);
        }
        return $order;
    }

    /** Flash an error and send the visitor back to the cart. */
    private function bailToCart(string $message): never
    {
        Flash::error($message);
        Redirect::to('/cart');
    }

    // GET: /checkout/success — Stripe redirects back here after payment.
    public function success(): void
    {
        AuthMiddleware::requireAuth();
        $sessionId = $_GET['session_id'] ?? '';
        if ($sessionId === '') {
            Redirect::to('/cart');
        }
        $info = $this->paymentService->retrieveSession($sessionId);
        $order = $this->confirmPaidOrder($info);
        $this->orderService->fulfill($order, session_id()); // idempotent: pending -> paid + tickets
        View::render('Checkout/success', ['order' => $this->orderService->getById($order->id)], 'Order confirmed');
    }

    /** Verify the payment server-side and return the owning order, or bail. */
    private function confirmPaidOrder(array $info): object
    {
        $order = $info['order_id'] ? $this->orderService->getById($info['order_id']) : null;
        if (!$info['paid'] || $order === null || $order->user_id !== (int) $_SESSION['UserId']) {
            Flash::error('We could not confirm your payment. If you were charged, contact support.');
            Redirect::to('/cart');
        }
        if ($info['payment_intent']) {
            $this->orderService->setPaymentIntent($order->id, $info['payment_intent']);
        }
        return $order;
    }

    // GET: /checkout/cancel - user backed out of Stripe.
    public function cancel(): void
    {
        $userId = AuthMiddleware::userId();
        $orderId = (int) ($_GET['order'] ?? 0);
        $order = $orderId > 0 ? $this->orderService->getByIdForUser($orderId, $userId) : null;
        Flash::error($this->orderService->cancelMessage($order));
        Redirect::to('/orders');
    }
}
