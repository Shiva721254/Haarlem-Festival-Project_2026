<?php
namespace App\ViewModels;
use App\Models\ProductModel;

class ProductsViewModel
{
    /** @var ProductModel[] */
    public array $products;

    public ?string $searchTerm;
    public ?string $category;
    public ?string $type;
    public ?int $price;

    public function __construct(
    array $products, 
    ?string $searchTerm = null, 
    ?string $category = null, 
    ?string $type = null, 
    ?int $price = null
    ){
        $this->products = $products;
        $this->searchTerm = $searchTerm;
        $this->category = $category;
        $this->type = $type;
        $this->price = $price;
    }
}