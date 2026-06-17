<?php
namespace App\Services\Interfaces;

use App\Models\CartItemModel;

interface ICartService
{
    /** @return CartItemModel[] */
    public function getItems(?int $userId, string $sessionId): array;

    public function itemCount(?int $userId, string $sessionId): int;

    /** @return array{ok:bool,message:string} */
    public function add(?int $userId, string $sessionId, int $ticketTypeId, int $quantity, string $notes = '', ?float $amount = null, bool $haarlemPas = false): array;

    /** @param array<string,mixed> $post @return array{ok:bool,message:string} */
    public function addFromRequest(array $post, ?int $userId, string $sessionId): array;

    /** @return array{ok:bool,message:string} */
    public function updateQuantity(?int $userId, string $sessionId, int $ticketTypeId, int $quantity): array;

    /** @param array<string,mixed> $post @return array{ok:bool,message:string} */
    public function updateQuantityFromRequest(array $post, ?int $userId, string $sessionId): array;

    public function remove(?int $userId, string $sessionId, int $ticketTypeId): void;

    /** @param array<string,mixed> $post */
    public function removeFromRequest(array $post, ?int $userId, string $sessionId): void;

    public function clear(?int $userId, string $sessionId): void;

    /** @return array{subtotal:float,vat:float,total:float} */
    public function totals(?int $userId, string $sessionId): array;

    public function safeRedirectTarget(string $target): string;
}
