<?php

namespace App\Controllers;

use App\Config;
use App\Framework\Flash;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\Interfaces\IOrderService;
use App\Services\Interfaces\IPaymentService;
use App\Services\OrderService;
use App\Services\PaymentService;

class CustomerOrderController
{
    private IOrderService $orderService;
    private IPaymentService $paymentService;

    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->paymentService = new PaymentService();
    }

    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $orders = $this->orderService->getByUser((int) $_SESSION['UserId']);
        View::render('Orders/index', ['orders' => $orders], 'My Orders');
    }

    public function pay(): void
    {
        AuthMiddleware::requireAuth();

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $order = $this->orderService->getByIdForUser($orderId, (int) $_SESSION['UserId']);
        if ($order === null) {
            Flash::error('Order not found.');
            header('Location: /orders');
            exit();
        }

        $check = $this->orderService->canStartPayment($order);
        if (!$check['ok']) {
            Flash::error($check['message']);
            header('Location: /orders');
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
            header('Location: /orders');
            exit();
        }

        header('Location: ' . $url);
        exit();
    }
}
