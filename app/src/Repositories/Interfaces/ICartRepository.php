<?php
namespace App\Repositories\Interfaces;

use App\Models\CartItemModel;

interface ICartRepository
{
    public function getOrCreateCartId(?int $userId, string $sessionId): int;

    public function findCartId(?int $userId, string $sessionId): ?int;

    /** @return CartItemModel[] */
    public function getItems(int $cartId): array;

    public function findItemQuantity(int $cartId, int $ticketTypeId): int;

    public function setQuantity(int $cartId, int $ticketTypeId, int $quantity, ?string $notes = null): void;

    public function removeItem(int $cartId, int $ticketTypeId): void;

    public function itemCount(int $cartId): int;

    public function clearCart(int $cartId): void;
}
