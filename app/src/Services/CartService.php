<?php
namespace App\Services;

use App\Models\CartItemModel;
use App\Repositories\CartRepository;
use App\Repositories\TicketTypeRepository;
use App\Repositories\Interfaces\ICartRepository;
use App\Repositories\Interfaces\ITicketTypeRepository;
use App\Services\Interfaces\ICartService;

class CartService implements ICartService
{
    private ICartRepository $cartRepo;
    private ITicketTypeRepository $ticketRepo;

    public function __construct()
    {
        $this->cartRepo = new CartRepository();
        $this->ticketRepo = new TicketTypeRepository();
    }

    private function userId(): ?int
    {
        return isset($_SESSION['UserId']) ? (int)$_SESSION['UserId'] : null;
    }

    /**
     * Cart id for mutations — creates a cart if none exists yet.
     */
    private function cartId(): int
    {
        return $this->cartRepo->getOrCreateCartId($this->userId(), session_id());
    }

    /**
     * Cart id for reads — null if the visitor has no cart yet (no row created).
     */
    private function existingCartId(): ?int
    {
        return $this->cartRepo->findCartId($this->userId(), session_id());
    }

    /** @return CartItemModel[] */
    public function getItems(): array
    {
        $id = $this->existingCartId();
        return $id === null ? [] : $this->cartRepo->getItems($id);
    }

    public function itemCount(): int
    {
        $id = $this->existingCartId();
        return $id === null ? 0 : $this->cartRepo->itemCount($id);
    }

    /**
     * Add a quantity of a ticket type to the cart, capped by availability.
     *
     * @return array{ok:bool,message:string}
     */
    public function add(int $ticketTypeId, int $quantity, string $notes = ''): array
    {
        if ($quantity < 1) {
            return ['ok' => false, 'message' => 'Quantity must be at least 1.'];
        }

        $ticket = $this->ticketRepo->getById($ticketTypeId);
        if ($ticket === null || !$ticket->is_active) {
            return ['ok' => false, 'message' => 'That ticket is not available.'];
        }

        $cartId = $this->cartId();
        $current = $this->cartRepo->findItemQuantity($cartId, $ticketTypeId);
        $desired = $current + $quantity;

        if ($desired > $ticket->available()) {
            return [
                'ok' => false,
                'message' => "Only {$ticket->available()} ticket(s) available for {$ticket->name}.",
            ];
        }

        // Empty notes are stored as null so a plain ticket keeps no requests.
        $this->cartRepo->setQuantity($cartId, $ticketTypeId, $desired, $notes !== '' ? $notes : null);
        return ['ok' => true, 'message' => 'Added to cart.'];
    }

    /**
     * Set an absolute quantity for a line (used by the cart page).
     *
     * @return array{ok:bool,message:string}
     */
    public function updateQuantity(int $ticketTypeId, int $quantity): array
    {
        if ($quantity <= 0) {
            $this->cartRepo->removeItem($this->cartId(), $ticketTypeId);
            return ['ok' => true, 'message' => 'Item removed.'];
        }

        $ticket = $this->ticketRepo->getById($ticketTypeId);
        if ($ticket === null || !$ticket->is_active) {
            return ['ok' => false, 'message' => 'That ticket is not available.'];
        }
        if ($quantity > $ticket->available()) {
            return ['ok' => false, 'message' => "Only {$ticket->available()} available for {$ticket->name}."];
        }

        $this->cartRepo->setQuantity($this->cartId(), $ticketTypeId, $quantity);
        return ['ok' => true, 'message' => 'Cart updated.'];
    }

    public function remove(int $ticketTypeId): void
    {
        $this->cartRepo->removeItem($this->cartId(), $ticketTypeId);
    }

    /**
     * Empty the current cart (e.g. after a successful order).
     */
    public function clear(): void
    {
        $id = $this->existingCartId();
        if ($id !== null) {
            $this->cartRepo->clearCart($id);
        }
    }

    /**
     * Money totals for the current cart.
     *
     * @return array{subtotal:float,vat:float,total:float}
     */
    public function totals(): array
    {
        $total = 0.0; // VAT-inclusive grand total (what the customer pays)
        $vat = 0.0;   // VAT portion contained within the total
        foreach ($this->getItems() as $item) {
            $line = $item->lineSubtotal();
            $total += $line;
            // Prices are VAT-inclusive; derive the VAT portion of each line.
            $vat += $line - ($line / (1 + $item->vat_rate / 100));
        }
        return [
            'subtotal' => round($total - $vat, 2), // net, excl. VAT
            'vat'      => round($vat, 2),
            'total'    => round($total, 2),         // subtotal + vat
        ];
    }
}
