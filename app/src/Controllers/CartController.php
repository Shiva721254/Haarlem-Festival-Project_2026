<?php

namespace App\Controllers;

use App\Services\Interfaces\ICartService;
use App\Services\Interfaces\IOrderService;
use App\Framework\View;
use App\Framework\Flash;
use App\Framework\Redirect;

/**
 * Shopping cart for festival tickets. Available to guests and logged-in users;
 * checkout (next feature) is what will require authentication.
 */
class CartController
{
    private ICartService $cartService;
    private IOrderService $orderService;

    public function __construct(ICartService $cartService, IOrderService $orderService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
    }

    // GET: /cart
    public function index(): void
    {
        View::render('Cart/index', [
            'items'         => $this->cartService->getItems($this->userId(), session_id()),
            'totals'        => $this->cartService->totals($this->userId(), session_id()),
            'pendingOrders' => $this->pendingPayLaterOrders(),
        ], 'Your cart');
    }

    /** @return array<int,object> the user's orders that can still be paid later */
    private function pendingPayLaterOrders(): array
    {
        $userId = $this->userId();
        return $userId === null ? [] : $this->orderService->getPendingPayLaterForUser($userId);
    }

    private function userId(): ?int
    {
        return isset($_SESSION['UserId']) ? (int) $_SESSION['UserId'] : null;
    }

    // POST: /cart/add
    public function add(): void
    {
        Flash::results([$this->cartService->addFromRequest($_POST, $this->userId(), session_id())]);
        $back = $this->cartService->safeRedirectTarget($_POST['return_to'] ?? '/cart');
        Redirect::to($back);
    }

    // POST: /cart/update
    public function update(): void
    {
        Flash::results([$this->cartService->updateQuantityFromRequest($_POST, $this->userId(), session_id())]);
        Redirect::to('/cart');
    }

    // POST: /cart/remove
    public function remove(): void
    {
        $this->cartService->removeFromRequest($_POST, $this->userId(), session_id());
        Flash::success('Item removed.');
        Redirect::to('/cart');
    }
}
