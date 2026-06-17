<?php

namespace App\Controllers;

use App\Framework\Flash;
use App\Framework\Redirect;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\Interfaces\IOrderService;
use App\Services\Interfaces\IPaymentService;

class CustomerOrderController
{
    private IOrderService $orderService;
    private IPaymentService $paymentService;

    public function __construct(IOrderService $orderService, IPaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    public function index(): void
    {
        $userId = AuthMiddleware::userId();
        $orders = $this->orderService->getByUser($userId);
        View::render('Orders/index', ['orders' => $orders], 'My Orders');
    }

    public function pay(): void
    {
        $userId = AuthMiddleware::userId();
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $order = $this->requirePayableOrder($orderId, $userId);
        $this->redirectToStripe($order);
    }

    /** Load the order for this user and verify it can still be paid, or bail. */
    private function requirePayableOrder(int $orderId, int $userId): object
    {
        $order = $this->orderService->getByIdForUser($orderId, $userId);
        if ($order === null) {
            $this->bailToOrders('Order not found.');
        }
        $check = $this->orderService->canStartPayment($order);
        if (!$check['ok']) {
            $this->bailToOrders($check['message']);
        }
        return $order;
    }

    private function redirectToStripe(object $order): never
    {
        try {
            $url = $this->paymentService->startCheckout($order);
        } catch (\Throwable $e) {
            $this->bailToOrders('Could not start payment. Please try again.');
        }
        Redirect::to($url);
    }

    private function bailToOrders(string $message): never
    {
        Flash::error($message);
        Redirect::to('/orders');
    }
}
