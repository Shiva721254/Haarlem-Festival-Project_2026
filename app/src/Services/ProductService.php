<?php
namespace App\Services;

use App\Models\ProductModel;
use App\Repositories\ProductRepository;
use App\Repositories\IProductRepository;
use App\Services\IProductService;

class ProductService implements IProductService
{
    private IProductRepository $productRepository;   
    public function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    public function getShoppingCart(int $userId): array
    {
        $shoppingCart = $this->productRepository->getShoppingCart($userId);
        return $shoppingCart;
    }

    public function getAll(): array
    {
        $products = $this->productRepository->getAll();
        return $products;
    }

    public function getProducts(?string $term = null, ?string $category = null, ?string $type = null, ?int $price = null): array
    {
        $products = $this->productRepository->getProducts($term, $category, $type, $price);
        return $products;
    }

    public function getById(int $id): ?ProductModel
    {
        $product = $this->productRepository->getById($id);
        return $product;
    }
    
    public function create(ProductModel $product): void
    {
        $this->productRepository->create($product);
    }

    public function update(ProductModel $product): void
    {
        $this->productRepository->update($product);
    }

    public function delete(int $id): void
    {
        $this->productRepository->delete($id);
    }

     public function addProductToShoppingCart(int $userId, int $productId, $quantity): void
    {
        $this->productRepository->addProductToShoppingCart($userId, $productId, $quantity);
    }

    public function getSearchMatches(string $query): array
    {
        if (empty($query)) return [];

        $results = $this->productRepository->searchProducts($query);
        
        $products = [];
        foreach ($results as $row) {
            $products[] = ProductModel::fromArray($row);
        }

        return $products; 
    }

    public function emptyCart($userId): void
    {
        $this->productRepository->emptyCart($userId);
    }
}