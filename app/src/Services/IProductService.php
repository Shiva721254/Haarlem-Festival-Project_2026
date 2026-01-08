<?php
namespace App\Services;

use App\Models\ProductModel;

interface IProductService
{
    public function getAll(): array;
    public function getById(int $id): ?ProductModel;
    public function create(ProductModel $product): void;
    public function update(ProductModel $product): void;
    public function delete(int $id): void;

    public function getShoppingCart(int $userId): array;
    public function addProductToShoppingCart(int $userId, int $productId, $quantity): void;

    public function getSearchMatches(string $query): array;
    public function emptyCart($userId): void;
}