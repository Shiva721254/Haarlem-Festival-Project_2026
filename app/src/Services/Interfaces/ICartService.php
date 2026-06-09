<?php
namespace App\Services\Interfaces;

use App\Models\CartItemModel;

interface ICartService
{
    /** @return CartItemModel[] */
    public function getItems(): array;

    public function itemCount(): int;

    /** @return array{ok:bool,message:string} */
    public function add(int $ticketTypeId, int $quantity): array;

    /** @return array{ok:bool,message:string} */
    public function updateQuantity(int $ticketTypeId, int $quantity): array;

    public function remove(int $ticketTypeId): void;

    /** @return array{subtotal:float,vat:float,total:float} */
    public function totals(): array;
}
