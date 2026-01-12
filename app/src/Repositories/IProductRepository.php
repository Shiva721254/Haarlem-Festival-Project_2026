<?php
namespace App\Repositories;

use App\Models\ProductModel;

interface IProductRepository
{
    public function getAll(): array;
    public function getById(int $id): ?ProductModel;
    public function create(ProductModel $product): void;
    public function update(ProductModel $product): void;
    public function delete(int $id): void;

    public function getShoppingCart(int $userId): array;
    public function addProductToShoppingCart(int $userId, int $productId, int $quantity = 1): void;

    public function searchProducts(string $term): array;
    public function emptyCart($userId): void;

    public function getProducts(?string $term = null, ?string $category = null, ?string $type = null, ?int $price = null): array;
}