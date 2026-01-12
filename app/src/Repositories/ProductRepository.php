<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Models\ProductModel;
use \PDO;

class ProductRepository extends Repository implements IProductRepository
{
    public function getAll(): array
    {
        $sql = 'SELECT ProductId, ProductName, Description, Category, Type, Price 
                FROM products ORDER BY Category';
        
        $result = $this->getConnection()->query($sql);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => ProductModel::fromDb($row), $rows);
    }

    public function searchProducts(?string $term = null): array
    {
        $sql = 'SELECT * FROM products WHERE
                ProductName LIKE :term OR
                Description LIKE :term OR
                Category LIKE :term OR
                Type LIKE :term
                ORDER BY (ProductName LIKE :term) DESC LIMIT 5';
        
        $stmt  = $this->getConnection()->prepare($sql);
        $stmt->execute(['term' => '%' . $term . '%']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getProducts(?string $term = null, ?string $category = null, ?string $type = null, ?int $price = null): array
    {
        $sql = 'SELECT ProductId, ProductName, Description, Category, Type, Price FROM products';
        $conditions = [];
        $params = [];

        if (!empty($term)) {
            $conditions[] = "(ProductName LIKE :term OR Description LIKE :term OR Category LIKE :term OR Type LIKE :term)";
            $params['term'] = '%' . $term . '%';
        }
        if ($category) {
            $conditions[] = "Category = :category";
            $params['category'] = $category;
        }
        if ($type) {
            $conditions[] = "Type = :type";
            $params['type'] = $type;
        }
        if ($price) {
            $conditions[] = "Price <= :price";
            $params['price'] = $price;
        }

        if (!empty($conditions)){
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        if (!empty($term)) {
                $sql .= " ORDER BY (ProductName LIKE :term) DESC";
            }
        
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params); 
            // Change FETCH_ASSOC to FETCH_OBJ           
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): ?ProductModel
    {
        $sql = 'SELECT * FROM products WHERE ProductId = :ProductId';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':ProductId', $id, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? ProductModel::fromDb($data) : null;
    }

    public function create(ProductModel $product): void
    {
        $sql = 'INSERT INTO products (ProductName, Description, Category, Type, Price)
                VALUES (:ProductName, :Description, :Category, :Type, :Price)';

        $stmt = $this->getConnection()->prepare($sql);
        $this->bindParams($stmt, $product);
        $stmt->execute();
    }

    public function update(ProductModel $product): void
    {
        $sql = 'UPDATE products 
                SET ProductName = :ProductName, Description = :Description, 
                    Category = :Category, Type = :Type, Price = :Price
                WHERE ProductId = :ProductId';

        $stmt = $this->getConnection()->prepare($sql);
        $this->bindParams($stmt, $product);
        $stmt->bindValue(':ProductId', $product->ProductId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->getConnection()->prepare('DELETE FROM products WHERE ProductId = :ProductId');
        $stmt->bindValue(':ProductId', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function bindParams($stmt, ProductModel $product): void
    {
        $stmt->bindValue(':ProductName', $product->ProductName, PDO::PARAM_STR);
        $stmt->bindValue(':Description', $product->Description, PDO::PARAM_STR);
        $stmt->bindValue(':Category', $product->Category->value, PDO::PARAM_STR);
        $stmt->bindValue(':Type', $product->Type->value, PDO::PARAM_STR);
        $stmt->bindValue(':Price', $product->Price, PDO::PARAM_INT);
    }

    public function addProductToShoppingCart(int $userId, int $productId, int $quantity = 1): void
    {
        $sql = 'INSERT INTO shoppingCart (UserId, ProductId, Quantity) 
                VALUES (:UserId, :ProductId, :Quantity)
                ON DUPLICATE KEY UPDATE Quantity = Quantity + :QuantityUpdate';

        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':UserId' => $userId,
                ':ProductId' => $productId,
                ':Quantity' => $quantity,
                ':QuantityUpdate' => $quantity
            ]);
        } catch (\PDOException $e) {
            throw new \Exception("Could not add product to cart: " . $e->getMessage());
        }
    }

    public function getShoppingCart(int $userId): array
    {
        $sql = 'SELECT p.ProductName, p.Price, c.Quantity, (p.Price * c.Quantity) AS subtotal
                FROM shoppingCart c
                JOIN products p ON c.ProductId = p.ProductId
                WHERE c.UserId = :userId';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $grandTotal = 0;
        $totalQuantity = 0;

        foreach ($items as $item) {
            $grandTotal += (float)$item['subtotal'];
            $totalQuantity += (int)$item['Quantity'];
        }

        return [
            'items'      => $items,
            'grandTotal' => $grandTotal,
            'itemCount'  => $totalQuantity
        ];
    }


    public function emptyCart($userId): void
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM shoppingCart WHERE UserId = :UserId");
        $stmt->bindValue(':UserId', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
}