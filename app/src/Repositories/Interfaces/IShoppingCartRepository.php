<?php
namespace App\Repositories\Interfaces;
interface IShoppingCartRepository
{
    public function addProductToShoppingCart(int $userId, int $productId, int $quantity = 1): void;
    public function getShoppingCart(int $userId): array;
    public function emptyCart($userId): void;
}