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
        if ($userId !== null) {
            $row = $this->fetchOne('SELECT id FROM carts WHERE user_id = :uid', ['uid' => $userId]);
            if ($row) {
                return (int)$row['id'];
            }
            $this->execute('INSERT INTO carts (user_id) VALUES (:uid)', ['uid' => $userId]);
            return $this->lastInsertId();
        }

        $row = $this->fetchOne('SELECT id FROM carts WHERE session_id = :sid AND user_id IS NULL', ['sid' => $sessionId]);
        if ($row) {
            return (int)$row['id'];
        }
        $this->execute('INSERT INTO carts (session_id) VALUES (:sid)', ['sid' => $sessionId]);
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
        $sql = 'SELECT ci.*,
                       tt.name  AS ticket_type_name,
                       tt.price AS price,
                       tt.vat_rate AS vat_rate,
                       GREATEST(0, tt.capacity - tt.sold) AS available,
                       e.id    AS event_id,
                       e.title AS event_title
                FROM cart_items ci
                JOIN ticket_types tt ON tt.id = ci.ticket_type_id
                JOIN events e ON e.id = tt.event_id
                WHERE ci.cart_id = :cid
                ORDER BY ci.id';
        return array_map(
            static fn(array $r) => CartItemModel::fromDb($r),
            $this->fetchAll($sql, ['cid' => $cartId])
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

        // When notes are given (a reservation) store them; otherwise (a plain
        // quantity change) leave any existing special requests untouched.
        if ($notes !== null) {
            $sql = 'INSERT INTO cart_items (cart_id, ticket_type_id, quantity, special_requests)
                    VALUES (:cid, :tid, :qty, :notes)
                    ON DUPLICATE KEY UPDATE quantity = :qty, special_requests = :notes';
            $this->execute($sql, ['cid' => $cartId, 'tid' => $ticketTypeId, 'qty' => $quantity, 'notes' => $notes]);
            return;
        }

        $sql = 'INSERT INTO cart_items (cart_id, ticket_type_id, quantity)
                VALUES (:cid, :tid, :qty)
                ON DUPLICATE KEY UPDATE quantity = :qty';
        $this->execute($sql, ['cid' => $cartId, 'tid' => $ticketTypeId, 'qty' => $quantity]);
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
