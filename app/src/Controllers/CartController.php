<?php

namespace App\Controllers;

use App\Services\CartService;
use App\Services\Interfaces\ICartService;
use App\Framework\View;
use App\Framework\Flash;

/**
 * Shopping cart for festival tickets. Available to guests and logged-in users;
 * checkout (next feature) is what will require authentication.
 */
class CartController
{
    private ICartService $cartService;

    public function __construct()
    {
        $this->cartService = new CartService();
    }

    // GET: /cart
    public function index(): void
    {
        View::render('Cart/index', [
            'items'  => $this->cartService->getItems(),
            'totals' => $this->cartService->totals(),
        ], 'Your cart');
    }

    // POST: /cart/add
    public function add(): void
    {
        $ticketTypeId = (int)($_POST['ticket_type_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        $result = $this->cartService->add($ticketTypeId, $quantity);
        $result['ok'] ? Flash::success($result['message']) : Flash::error($result['message']);

        // Return to where the user came from (the event page), or the cart.
        $back = $_POST['return_to'] ?? '/cart';
        header('Location: ' . $this->safeRedirect($back));
        exit();
    }

    // POST: /cart/update
    public function update(): void
    {
        $ticketTypeId = (int)($_POST['ticket_type_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        $result = $this->cartService->updateQuantity($ticketTypeId, $quantity);
        $result['ok'] ? Flash::success($result['message']) : Flash::error($result['message']);

        header('Location: /cart');
        exit();
    }

    // POST: /cart/remove
    public function remove(): void
    {
        $ticketTypeId = (int)($_POST['ticket_type_id'] ?? 0);
        $this->cartService->remove($ticketTypeId);
        Flash::success('Item removed.');
        header('Location: /cart');
        exit();
    }

    /**
     * Only allow same-site relative redirects (avoid open redirects).
     */
    private function safeRedirect(string $target): string
    {
        return (str_starts_with($target, '/') && !str_starts_with($target, '//')) ? $target : '/cart';
    }
}
