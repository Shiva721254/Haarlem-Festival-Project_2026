<?php
namespace App\ViewModels;
use App\Models\ProductModel;

class ShoppingCart
{    
    public array $shoppingCart;
    public int $userId;

    public function __construct(array $shoppingCart, int $userId)
    {
        $this->shoppingCart = $shoppingCart;
        $this->userId = $userId;
    }
}