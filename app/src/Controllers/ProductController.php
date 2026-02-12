<?php  

namespace App\Controllers;
use App\Services\Interfaces\IProductService;
use App\Services\Interfaces\IOrderService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Repositories\Interfaces\IShoppingCartRepository;
use App\Repositories\Interfaces\IProductRatingRepository;
use App\Repositories\ProductRatingRepository;
use App\Repositories\ShoppingCartRepository;
use App\ViewModels\ManageProductViewModel;
use App\ViewModels\ProductsViewModel;
use App\Models\ProductModel;
use App\Models\ProductRating;

use App\Middleware\AuthMiddleware;
use App\ViewModels\ManageRatingViewModel;
use Exception;

class ProductController
{
    private IProductService $productService;
    private IOrderService $orderService;
    private IShoppingCartRepository $cartRepository;
    private IProductRatingRepository $productRatingRepository;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->orderService = new OrderService();
        $this->cartRepository = new ShoppingCartRepository();
        $this->productRatingRepository = new ProductRatingRepository();
    }

    // This method is used for the main product listing page, 
    // which also supports filtering based on search term, category, type, and price.
    public function index2($vars = [])
    {
        $searchTerm = $_GET['q'] ?? null;
        $category   = $_GET['category'] ?? null;
        $type       = $_GET['type'] ?? null;
        $price      = isset($_GET['price']) ? (int)$_GET['price'] : null;

        $products = $this->productService->getProducts($searchTerm, $category, $type, $price);

        foreach ($products as $product) {
            $product->AverageRating = $this->productRatingRepository->getAverageRatingByProductId($product->ProductId);
            $product->TotalReviews = $this->productRatingRepository->getNumberOfRatingsByProductId($product->ProductId);
        }
        $vm = new ProductsViewModel($products, $searchTerm, $category, $type, $price);
        require __DIR__ . "/../Views/Products/index.php";
    }

    public function shoppingCart() 
    {
        
        $userId = $_SESSION['UserId'] ?? null;
        AuthMiddleware::requireAdminOrOwner($userId);
        if (!$userId) {
            header("Location: /showLogin?error=auth_required");
            exit;
        }
        $cartData = $this->cartRepository->getShoppingCart($userId);

        require __DIR__ . "/../Views/Products/shoppingCart.php";
    }

    // GET
    public function updateProduct($vars = [])
    {
        $id = (int)($vars['id'] ?? 0);
        AuthMiddleware::requireAdmin();
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

        if (!$product) {
            header("Location: /products");
            exit;
        }

        $avgRating = $this->productRatingRepository->getAverageRatingByProductId($id);
        $totalReviews = $this->productRatingRepository->getNumberOfRatingsByProductId($id);
        $reviews = $this->productRatingRepository->getAllRatingsByProductId($id);

        require __DIR__ . "/../Views/Products/displayProduct.php";
    }

    public function getRatingApi($vars = []) {
        $id = (int)($vars['id'] ?? 0);
        
        $avgRating = $this->productRatingRepository->getAverageRatingByProductId($id);
        $totalReviews = $this->productRatingRepository->getNumberOfRatingsByProductId($id);
        $reviews = $this->productRatingRepository->getAllRatingsByProductId($id);

        header('Content-Type: application/json');
        
        echo json_encode([
            'averageRating' => (float)$avgRating,
            'totalReviews' => (int)$totalReviews,
            'reviews' => $reviews
        ]);
    }

    // GET
    public function showEditRating($vars = []) {
        $rating = $this->productRatingRepository->getRatingByProductId((int)($vars['id'] ?? 0));
        $vm = new ManageRatingViewModel($rating);
        require __DIR__ . "/../Views/Products/editRating.php";
    }

    // POST
    public function handleUpdateRating($vars = []) {
        $userId = $_SESSION['UserId'] ?? null;
        if (!$userId) exit("Unauthorized");

        $rating = new \App\Models\ProductRating();
        $rating->ProductId = (int)($vars['id'] ?? 0);
        $rating->UserId = $userId;
        $rating->Rating = (int)$_POST['rating'];
        $rating->Review = $_POST['review'];

        $this->productRatingRepository->updateRating($rating);
        header("Location: /product/" . $rating->ProductId);
    }

    // POST
    public function handleDeleteRating($vars = []) {
        $userId = $_SESSION['UserId'] ?? null;
        $productId = (int)($vars['id'] ?? 0);

        $success = $this->productRatingRepository->deleteRating($productId, $userId);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit;
        }
        header("Location: /product/" . $productId);
    }

    // GET
    public function createProduct($vars = [])
    {
        AuthMiddleware::requireAdmin();
        $product = null;           
        $vm = new ManageProductViewModel($product);
        require __DIR__ . "/../Views/Products/createProduct.php";       
    }    
    
    // POST
    public function saveProduct($vars = [])
    {
        AuthMiddleware::requireAdmin();
        $product = (new ProductModel())->fromPost();
        if ($product->ProductId > 0) {
            $this->productService->update($product);
        } else {
            $this->productService->create($product);
        }
        header('Location: /products');
        exit();
    }

    // GET
    public function showAddRating($vars = [])
    {
        if (!isset($_SESSION['UserId'])) {
            header("Location: /showLogin");
            exit();
        }
        /* $userId = $_SESSION['UserId'];
        AuthMiddleware::requireOwner($userId); */

        $productId = (int)($vars['id'] ?? 0);
        $product = $this->productService->getById($productId);

        if (!$product) {
            header("Location: /products");
            exit();
        }

        require __DIR__ . "/../Views/Products/addRating.php";
    }

    // POST
    public function rateProdcut($vars = [])
    {
        if (!isset($_SESSION['UserId'])){
            header("Location: /showLogin");
            exit();
        }

        $userId = $_SESSION['UserId'];
        $productId = (int)($_POST['id'] ?? 0);
        $ratingValue = (int)($_POST['rating'] ?? 0);
        $reviewText = isset($_POST['review']) ? htmlspecialchars(trim($_POST['review']), ENT_QUOTES, 'UTF-8') : null;
        
        $rating = new ProductRating();
        $rating->ProductId = $productId;
        $rating->UserId = $userId;
        $rating->Rating = $ratingValue > 0 ? $ratingValue : null;
        $rating->Review = !empty($reviewText) ? $reviewText : null;

        try {
            $this->productRatingRepository->addRating($rating);
            // Redirect back to the product page with a success message
            header("Location: /product/" . $productId . "?rated=success#reviews-section");
        } catch (\Exception $e) {
            // Handle error (e.g., duplicate rating)
            header("Location: /product/" . $productId . "?error=already_rated");
        }
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
        AuthMiddleware::requireOwner($userId);
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($productId > 0) {
            try {
                $this->cartRepository->addProductToShoppingCart($userId, $productId, $quantity);
                
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
        $cartData = $this->cartRepository->getShoppingCart($userId);

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
            $this->orderService->sendOrderConfirmationEmail($orderId);
            header("Location: /orderSuccess?id=" . $orderId);
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