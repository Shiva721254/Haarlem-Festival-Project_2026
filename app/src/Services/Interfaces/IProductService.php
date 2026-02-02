<?php
namespace App\Services\Interfaces;

use App\Models\ProductModel;

interface IProductService
{
    public function getAll(): array;
    public function getById(int $id): ?ProductModel;
    public function create(ProductModel $product): void;
    public function update(ProductModel $product): void;
    public function delete(int $id): void;

    public function getSearchMatches(string $query): array;
    public function getProducts(?string $term = null, ?string $category = null, ?string $type = null, ?int $price = null): array;
}