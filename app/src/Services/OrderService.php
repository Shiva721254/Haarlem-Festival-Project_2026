<?php
namespace App\Services;
use App\Services\IOrderService;
use App\Repositories\IOrderRepository;
use App\Repositories\OrderRepository;
use App\Services\IProductService;
use Exception;

class OrderService implements IOrderService
{
    private OrderRepository $orderRepository;
    private IProductService $productService;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->productService = new ProductService();
    }

    public function checkout(int $userId, string $address, string $paymentMethod): int
    {
        $cartItems = $this->productService->getShoppingCart($userId);
        if (empty($cartItems)){
            throw new Exception("Cart is empty");
        }

        $total = 0;
        foreach ($cartItems as $item) {
            $price = $item['Price'] ?? 0; 
            $qty   = $item['Quantity'] ?? 0;
            $total += $price * $qty;
        }
        
        $db = $this->orderRepository->getConnection();
        $db->beginTransaction();

        try {
            $orderId = $this->orderRepository->saveOrder($userId, $address, $paymentMethod, $total);
            
            foreach ($cartItems as $item) {
                $this->orderRepository->saveOrderItem(
                    $orderId, 
                    $item['ProductId'] ?? $item['product_id'], 
                    $item['Quantity'] ?? $item['quantity'], 
                    $item['Price'] ?? $item['price']
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
}