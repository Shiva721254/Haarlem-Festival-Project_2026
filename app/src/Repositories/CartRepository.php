<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\ICartRepository;
use App\Models\CartItemModel;

class CartRepository extends Repository implements ICartRepository
{
    /**
     * Find or create the cart for a logged-in user or an anonymous session,
     * returning the cart id.
     */
    public function getOrCreateCartId(?int $userId, string $sessionId): int
    {
        $row = $this->cartIdRow($userId, $sessionId);
        if ($row) {
            return (int)$row['id'];
        }
        $this->createCart($userId, $sessionId);
        return $this->lastInsertId();
    }

    /**
     * Find the existing cart id for a user/session without creating one.
     */
    public function findCartId(?int $userId, string $sessionId): ?int
    {
        if ($userId !== null) {
            $row = $this->fetchOne('SELECT id FROM carts WHERE user_id = :uid', ['uid' => $userId]);
            return $row ? (int)$row['id'] : null;
        }
        $row = $this->fetchOne('SELECT id FROM carts WHERE session_id = :sid AND user_id IS NULL', ['sid' => $sessionId]);
        return $row ? (int)$row['id'] : null;
    }

    /**
     * Cart lines enriched with ticket type, event and remaining availability.
     *
     * @return CartItemModel[]
     */
    public function getItems(int $cartId): array
    {
        return array_map(
            static fn(array $r) => CartItemModel::fromDb($r),
            $this->fetchAll($this->cartItemsSql(), ['cid' => $cartId])
        );
    }

    public function findItemQuantity(int $cartId, int $ticketTypeId): int
    {
        $row = $this->fetchOne(
            'SELECT quantity FROM cart_items WHERE cart_id = :cid AND ticket_type_id = :tid',
            ['cid' => $cartId, 'tid' => $ticketTypeId]
        );
        return $row ? (int)$row['quantity'] : 0;
    }

    /**
     * Set the absolute quantity for a ticket type in the cart (insert or update).
     * A quantity of 0 or less removes the line.
     */
    public function setQuantity(int $cartId, int $ticketTypeId, int $quantity, ?string $notes = null): void
    {
        if ($quantity <= 0) {
            $this->removeItem($cartId, $ticketTypeId);
            return;
        }
        $this->execute($this->setQuantitySql($notes), $this->quantityParams($cartId, $ticketTypeId, $quantity, $notes));
    }

    private function cartIdRow(?int $userId, string $sessionId): ?array
    {
        if ($userId !== null) {
            return $this->fetchOne('SELECT id FROM carts WHERE user_id = :uid', ['uid' => $userId]);
        }
        return $this->fetchOne('SELECT id FROM carts WHERE session_id = :sid AND user_id IS NULL', ['sid' => $sessionId]);
    }

    private function createCart(?int $userId, string $sessionId): void
    {
        $sql = $userId !== null ? 'INSERT INTO carts (user_id) VALUES (:uid)' : 'INSERT INTO carts (session_id) VALUES (:sid)';
        $this->execute($sql, $userId !== null ? ['uid' => $userId] : ['sid' => $sessionId]);
    }

    private function cartItemsSql(): string
    {
        return 'SELECT ci.*, tt.name AS ticket_type_name, tt.price AS price, tt.vat_rate AS vat_rate,
                       GREATEST(0, tt.capacity - tt.sold) AS available, e.id AS event_id, e.title AS event_title
                FROM cart_items ci JOIN ticket_types tt ON tt.id = ci.ticket_type_id
                JOIN events e ON e.id = tt.event_id WHERE ci.cart_id = :cid ORDER BY ci.id';
    }

    private function setQuantitySql(?string $notes): string
    {
        if ($notes !== null) {
            return 'INSERT INTO cart_items (cart_id, ticket_type_id, quantity, special_requests)
                    VALUES (:cid, :tid, :qty, :notes) ON DUPLICATE KEY UPDATE quantity = :qty, special_requests = :notes';
        }
        return 'INSERT INTO cart_items (cart_id, ticket_type_id, quantity)
                VALUES (:cid, :tid, :qty) ON DUPLICATE KEY UPDATE quantity = :qty';
    }

    private function quantityParams(int $cartId, int $ticketTypeId, int $quantity, ?string $notes): array
    {
        $params = ['cid' => $cartId, 'tid' => $ticketTypeId, 'qty' => $quantity];
        return $notes === null ? $params : $params + ['notes' => $notes];
    }

    /** Set the effective unit price for a line (donation amount or discount). */
    public function setCustomPrice(int $cartId, int $ticketTypeId, ?float $price): void
    {
        $this->execute(
            'UPDATE cart_items SET custom_price = :price WHERE cart_id = :cid AND ticket_type_id = :tid',
            ['price' => $price, 'cid' => $cartId, 'tid' => $ticketTypeId]
        );
    }

    public function removeItem(int $cartId, int $ticketTypeId): void
    {
        $this->execute(
            'DELETE FROM cart_items WHERE cart_id = :cid AND ticket_type_id = :tid',
            ['cid' => $cartId, 'tid' => $ticketTypeId]
        );
    }

    /**
     * Total number of tickets in the cart (for the header badge).
     */
    public function itemCount(int $cartId): int
    {
        $row = $this->fetchOne('SELECT COALESCE(SUM(quantity),0) AS n FROM cart_items WHERE cart_id = :cid', ['cid' => $cartId]);
        return $row ? (int)$row['n'] : 0;
    }

    public function clearCart(int $cartId): void
    {
        $this->execute('DELETE FROM cart_items WHERE cart_id = :cid', ['cid' => $cartId]);
    }
}
