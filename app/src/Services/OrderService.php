<?php
namespace App\Services;
use App\Services\IOrderService;
use App\Repositories\IOrderRepository;
use App\Repositories\OrderRepository;
use App\Services\IProductService;
use App\Services\ProductService;
use App\Services\MailService;
use Exception;

class OrderService implements IOrderService
{
    private OrderRepository $orderRepository;
    private IProductService $productService;
    private MailService $mailService;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->productService = new ProductService();
        $this->mailService = new MailService();
    }

    public function checkout(int $userId, string $address, string $paymentMethod): int
    {
        $cartData = $this->productService->getShoppingCart($userId);
        if (empty($cartData['items'])) {
            throw new Exception("Cart is empty");
        }      

        $total = $cartData['grandTotal'];
        
        $db = $this->orderRepository->getConnection();
        $db->beginTransaction();

        try {
            $orderId = $this->orderRepository->saveOrder($userId, $address, $paymentMethod, $total);
            
            // Loop over the 'items' array specifically
            foreach ($cartData['items'] as $item) {
                $this->orderRepository->saveOrderItem(
                    $orderId, 
                    $item['ProductId'] ?? throw new Exception("Missing Product ID"), 
                    $item['Quantity'], 
                    $item['Price']
                );
            }

            $this->productService->emptyCart($userId);

            $db->commit();
            return $orderId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function sendOrderConfirmationEmail(int $orderId)
    {
        $order = $this->orderRepository->getOrderDetails($orderId);
        
        if (!$order) {
            throw new Exception("Order not found");
        }

        $email = $order['Email'];
        $subject = "Order Confirmation - #{$order['OrderId']}";

        // Build the HTML table for items
        $itemsHtml = "";
        foreach ($order['items'] as $item) {
            $subtotal = number_format(($item['PriceAtPurchase'] * $item['Quantity']) / 100, 2);
            $itemsHtml .= "
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['ProductName']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['Quantity']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>\${$subtotal}</td>
                </tr>";
        }

        $totalFormatted = number_format($order['TotalAmount'] / 100, 2);

        // Compose the full message
        $message = "
            <h1>Thank you for your order, {$order['FirstName']}!</h1>
            <p>We've received your order and it's now being processed.</p>
            <p><strong>Order ID:</strong> #{$order['OrderId']}<br>
            <strong>Shipping Address:</strong> {$order['Address']}<br>
            <strong>Payment Method:</strong> {$order['PaymentMethod']}</p>
            
            <table style='width: 100%; border-collapse: collapse;'>
                <thead>
                    <tr style='background-color: #f8f9fa;'>
                        <th style='text-align: left; padding: 8px;'>Product</th>
                        <th style='text-align: left; padding: 8px;'>Qty</th>
                        <th style='text-align: left; padding: 8px;'>Price</th>
                    </tr>
                </thead>
                <tbody>
                    {$itemsHtml}
                </tbody>
            </table>
            
            <h3 style='text-align: right;'>Total: \${$totalFormatted}</h3>
            <p>If you have any questions, please reply to this email.</p>
        ";

        return $this->mailService->send($email, $subject, $message);
    }
}