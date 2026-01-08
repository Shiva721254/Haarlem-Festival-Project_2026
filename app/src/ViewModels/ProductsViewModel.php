<?php
namespace App\ViewModels;
use App\Models\ProductModel;

class ProductsViewModel
{
    /** @var ProductModel[] */
    public array $products;

    public ?string $searchTerm;

    public function __construct(array $products, ?string $searchTerm = null)
    {
        $this->products = $products;
        $this->searchTerm = $searchTerm;
    }
}