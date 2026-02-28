<?php
namespace App\Repositories\Interfaces;
use App\Models\ProductRating;

interface IProductRatingRepository 
{
    public function addRating(ProductRating $rating): bool;
    public function updateRating(ProductRating $rating): bool;
    public function deleteRating(int $productId, int $userId): bool;

    public function getRatingByProductId(int $productId): ?ProductRating;
    public function getAllRatingsByProductId(int $productId): array;

    public function getAverageRatingByProductId(int $productId): float;
    public function getNumberOfRatingsByProductId(int $productId): int;
}