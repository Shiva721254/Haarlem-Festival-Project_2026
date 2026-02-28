<?php
namespace App\Models;

use App\Enums\ProductCategory;
use App\Enums\ProductType;

class ProductModel 
{
    public int $ProductId;
    public string $ProductName;
    public string $Description;
    public ProductCategory $Category;
    public int $Price;

    public static function fromDb(array $data): self
    {
        $product = new self();
        $product->ProductId = (int)$data['ProductId'];
        $product->ProductName = $data['ProductName'];
        $product->Description = $data['Description'] ?? '';
        $product->Category = ProductCategory::from($data['Category']);
        $product->Price = (int)$data['Price'];
        
        return $product;
    }

    public function fromPost(): self
    {
        $this->ProductId = isset($_POST['ProductId']) ? (int)$_POST['ProductId'] : 0;
        $this->ProductName = $_POST['ProductName'] ?? '';
        $this->Description = $_POST['Description'] ?? '';
        $this->Category = ProductCategory::from($_POST['Category']);
        $this->Price = isset($_POST['Price']) ? (int)$_POST['Price'] : 0;
        
        return $this;
    }
}