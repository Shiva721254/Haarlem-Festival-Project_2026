<?php
namespace App\Services;

use App\Models\CartItemModel;
use App\Models\TicketTypeModel;
use App\Repositories\Interfaces\ICartRepository;
use App\Repositories\Interfaces\ITicketTypeRepository;
use App\Services\Interfaces\ICartService;

class CartService implements ICartService
{
    private const MIN_DONATION = 1.00;        // pay-what-you-like floor
    private const HAARLEMPAS_RATE = 0.25;     // 25% off Stories entry fees

    private ICartRepository $cartRepo;
    private ITicketTypeRepository $ticketRepo;

    public function __construct(ICartRepository $cartRepo, ITicketTypeRepository $ticketRepo)
    {
        $this->cartRepo = $cartRepo;
        $this->ticketRepo = $ticketRepo;
    }

    /** Cart id for mutations â€” creates a cart if none exists yet. */
    private function cartId(?int $userId, string $sessionId): int
    {
        return $this->cartRepo->getOrCreateCartId($userId, $sessionId);
    }

    /** Cart id for reads â€” null if the visitor has no cart yet (no row created). */
    private function existingCartId(?int $userId, string $sessionId): ?int
    {
        return $this->cartRepo->findCartId($userId, $sessionId);
    }

    /** @return CartItemModel[] */
    public function getItems(?int $userId, string $sessionId): array
    {
        $id = $this->existingCartId($userId, $sessionId);
        return $id === null ? [] : $this->cartRepo->getItems($id);
    }

    public function itemCount(?int $userId, string $sessionId): int
    {
        $id = $this->existingCartId($userId, $sessionId);
        return $id === null ? 0 : $this->cartRepo->itemCount($id);
    }

    /**
     * Add a quantity of a ticket type to the cart, capped by availability.
     *
     * @return array{ok:bool,message:string}
     */
    public function add(?int $userId, string $sessionId, int $ticketTypeId, int $quantity, string $notes = '', ?float $amount = null, bool $haarlemPas = false): array
    {
        $ticket = $this->ticketRepo->getById($ticketTypeId);
        $error = $this->addError($quantity, $ticket);
        if ($error !== null) {
            return ['ok' => false, 'message' => $error];
        }
        $priced = $this->resolvePrice($ticket, $amount, $haarlemPas);
        if (!$priced['ok']) {
            return $priced;
        }
        return $this->storeLine($userId, $sessionId, $ticket, $ticketTypeId, $quantity, $notes, $priced['price']);
    }

    private function addError(int $quantity, ?TicketTypeModel $ticket): ?string
    {
        if ($quantity < 1) {
            return 'Quantity must be at least 1.';
        }
        if ($ticket === null || !$ticket->is_active) {
            return 'That ticket is not available.';
        }
        return null;
    }

    /** Persist (or top up) a cart line, capped by availability. */
    private function storeLine(?int $userId, string $sessionId, TicketTypeModel $ticket, int $ticketTypeId, int $quantity, string $notes, ?float $customPrice): array
    {
        $cartId = $this->cartId($userId, $sessionId);
        $desired = $this->cartRepo->findItemQuantity($cartId, $ticketTypeId) + $quantity;
        if ($desired > $ticket->available()) {
            return ['ok' => false, 'message' => "Only {$ticket->available()} ticket(s) available for {$ticket->name}."];
        }
        // Empty notes are stored as null so a plain ticket keeps no requests.
        $this->cartRepo->setQuantity($cartId, $ticketTypeId, $desired, $notes !== '' ? $notes : null);
        $this->cartRepo->setCustomPrice($cartId, $ticketTypeId, $customPrice);
        return ['ok' => true, 'message' => 'Added to cart.'];
    }

    public function addFromRequest(array $post, ?int $userId, string $sessionId): array
    {
        return $this->add(
            $userId,
            $sessionId,
            (int)($post['ticket_type_id'] ?? 0),
            (int)($post['quantity'] ?? 1),
            $this->cappedNotes((string)($post['special_requests'] ?? '')),
            isset($post['amount']) && $post['amount'] !== '' ? (float)$post['amount'] : null,
            !empty($post['haarlempas'])
        );
    }

    private function cappedNotes(string $raw): string
    {
        $notes = trim($raw);
        return mb_strlen($notes) > 500 ? mb_substr($notes, 0, 500) : $notes;
    }

    /**
     * Resolve the effective line price: a chosen donation amount, the HaarlemPas
     * reduction on Stories, or null (use the ticket's own price). A client price
     * is never trusted for a fixed ticket â€” the discount is computed here.
     *
     * @return array{ok:bool,price?:?float,message?:string}
     */
    private function resolvePrice(TicketTypeModel $ticket, ?float $amount, bool $haarlemPas): array
    {
        if ($ticket->is_donation) {
            return $this->donationPrice($amount);
        }
        if ($haarlemPas && $ticket->event_type_slug === 'stories') {
            return ['ok' => true, 'price' => round($ticket->price * (1 - self::HAARLEMPAS_RATE), 2)];
        }
        return ['ok' => true, 'price' => null];
    }

    private function donationPrice(?float $amount): array
    {
        if ($amount === null || $amount < self::MIN_DONATION) {
            return ['ok' => false, 'message' => 'Please enter an amount of at least 1.00 euro.'];
        }
        return ['ok' => true, 'price' => round($amount, 2)];
    }

    /**
     * Set an absolute quantity for a line (used by the cart page).
     *
     * @return array{ok:bool,message:string}
     */
    public function updateQuantity(?int $userId, string $sessionId, int $ticketTypeId, int $quantity): array
    {
        if ($quantity <= 0) {
            return $this->removeLine($userId, $sessionId, $ticketTypeId);
        }
        $ticket = $this->ticketRepo->getById($ticketTypeId);
        $error = $this->quantityError($ticket, $quantity);
        if ($error !== null) {
            return ['ok' => false, 'message' => $error];
        }
        $this->cartRepo->setQuantity($this->cartId($userId, $sessionId), $ticketTypeId, $quantity);
        return ['ok' => true, 'message' => 'Cart updated.'];
    }

    private function removeLine(?int $userId, string $sessionId, int $ticketTypeId): array
    {
        $this->cartRepo->removeItem($this->cartId($userId, $sessionId), $ticketTypeId);
        return ['ok' => true, 'message' => 'Item removed.'];
    }

    private function quantityError(?TicketTypeModel $ticket, int $quantity): ?string
    {
        if ($ticket === null || !$ticket->is_active) {
            return 'That ticket is not available.';
        }
        if ($quantity > $ticket->available()) {
            return "Only {$ticket->available()} available for {$ticket->name}.";
        }
        return null;
    }

    public function updateQuantityFromRequest(array $post, ?int $userId, string $sessionId): array
    {
        return $this->updateQuantity(
            $userId,
            $sessionId,
            (int)($post['ticket_type_id'] ?? 0),
            (int)($post['quantity'] ?? 0)
        );
    }

    public function remove(?int $userId, string $sessionId, int $ticketTypeId): void
    {
        $this->cartRepo->removeItem($this->cartId($userId, $sessionId), $ticketTypeId);
    }

    public function removeFromRequest(array $post, ?int $userId, string $sessionId): void
    {
        $this->remove($userId, $sessionId, (int)($post['ticket_type_id'] ?? 0));
    }

    /** Empty the current cart (e.g. after a successful order). */
    public function clear(?int $userId, string $sessionId): void
    {
        $id = $this->existingCartId($userId, $sessionId);
        if ($id !== null) {
            $this->cartRepo->clearCart($id);
        }
    }

    /**
     * Money totals for the current cart. Prices are VAT-inclusive; the VAT
     * portion of each line is derived from its rate.
     *
     * @return array{subtotal:float,vat:float,total:float}
     */
    public function totals(?int $userId, string $sessionId): array
    {
        $total = 0.0;
        $vat = 0.0;
        foreach ($this->getItems($userId, $sessionId) as $item) {
            $line = $item->lineSubtotal();
            $total += $line;
            $vat += $line - ($line / (1 + $item->vat_rate / 100));
        }
        return ['subtotal' => round($total - $vat, 2), 'vat' => round($vat, 2), 'total' => round($total, 2)];
    }

    public function safeRedirectTarget(string $target): string
    {
        return (str_starts_with($target, '/') && !str_starts_with($target, '//')) ? $target : '/cart';
    }
}

