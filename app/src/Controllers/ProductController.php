<?php  

namespace App\Controllers;
use App\Services\IProductService;
use App\Services\ProductService;
use App\Services\IOrderService;
use App\ViewModels\ManageProductViewModel;
use App\ViewModels\ProductsViewModel;
use App\Models\ProductModel;
use App\Services\OrderService;
use Exception;

class ProductController
{
    private IProductService $productService;
    private IOrderService $orderService;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->orderService = new OrderService();
    }

    public function index()
    {
        $searchTerm = $_GET['q'] ?? null;

        if ($searchTerm) {
            // If searching, we get the results (wrapped in an array for the ViewModel)
            $products = $this->productService->getSearchMatches($searchTerm);
        } else {
            // Otherwise, get everything
            $products = $this->productService->getAll();
        }

        $vm = new ProductsViewModel($products);
        
        // Pass the search term to the view so we can show "Results for '...'"
        $vm->searchTerm = $searchTerm; 

        require __DIR__ . "/../Views/Products/index.php";       
    }

    public function shoppingCart() 
    {
        $userId = $_SESSION['UserId'] ?? null;
        if (!$userId) {
            header("Location: /showLogin?error=auth_required");
            exit;
        }
        $cartData = $this->productService->getShoppingCart($userId);

        require __DIR__ . "/../Views/Products/shoppingCart.php";
    }

    // GET
    public function updateProduct($vars = [])
    {
        $id = (int)($vars['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /products');
            exit();
        }

        $product = $this->productService->getById($id);

        if (!$product) {
            // If product doesn't exist, redirect back to list
            header('Location: /products?error=notfound');
            exit();
        }

        $vm = new ManageProductViewModel($product);        
        require __DIR__ . "/../Views/Products/updateProduct.php"; 
    }

    public function displayProduct($vars = [])
    {
        $id = (int)($vars['id'] ?? 0);
        $product = $this->productService->getById($id);
        require __DIR__ . "/../Views/Products/displayProduct.php";
    }

    // GET
    public function createProduct($vars = [])
    {
        $product = null;           
        $vm = new ManageProductViewModel($product);
        require __DIR__ . "/../Views/Products/createProduct.php";       
    }    
    
    // POST
    public function saveProduct($vars = [])
    {
        $product = (new ProductModel())->fromPost();
        if ($product->ProductId > 0) {
            $this->productService->update($product);
        } else {
            $this->productService->create($product);
        }
        header('Location: /products');
        exit();
    }
    
    // POST
    public function addProductToShoppingCart()
    {
        if (!isset($_SESSION['UserId'])) {
            header("Location: /showLogin");
            exit();
        }

        $userId = $_SESSION['UserId'];
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($productId > 0) {
            try {
                $this->productService->addProductToShoppingCart($userId, $productId, $quantity);
                
                header("Location: /shoppingCart?success=1");
                exit();
            } catch (\Exception $e) {
                header("Location: /product/" . $productId . "?error=failed");
                exit();
            }
        }
    }  

    // --- TRANSACTION LOGIC --- 

    // GET
    public function showCheckout()
    {
        if (!isset($_SESSION['UserId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['UserId'];
        $cartData = $this->productService->getShoppingCart($userId);

        if (empty($cartData)) {
            header("Location: /shoppingCart?error=empty_cart");
            exit; 
        }

        require __DIR__ . "/../Views/Products/checkout.php";
    }

    // POST
    public function processCheckout()
    {
        $userId = $_SESSION['UserId'] ?? null;
        $address = $_POST['address'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? '';

        if (!$userId || empty($address) || empty($paymentMethod)) {
            header("Location: /checkout?error=missing_info");
            exit;
        }

        try {
            $orderId = $this->orderService->checkout($userId, $address, $paymentMethod);
            header("Location: /orderSucess?id=" . $orderId);
            exit;

        } catch (Exception $e) {
            header("Location: /checkout?error=failed");
            exit;
        }
    }

    // GET
    public function orderSuccess() 
    {
        $orderId = $_GET['id'] ?? null;
        require __DIR__ . "/../Views/Products/orderSuccess.php";
    }
}

?>