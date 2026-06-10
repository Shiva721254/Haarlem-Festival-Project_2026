<?php
namespace App\Controllers;

use App\Framework\Flash;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Models\RestaurantModel;
use App\Services\Interfaces\IRestaurantService;
use App\Services\RestaurantService;

class AdminRestaurantController
{
    private IRestaurantService $restaurantService;

    public function __construct()
    {
        $this->restaurantService = new RestaurantService();
    }

    public function index(): void
    {
        AuthMiddleware::requireAdmin();
        View::renderAdmin('Admin/restaurants/index', [
            'restaurants' => $this->restaurantService->getAll(),
        ], 'Restaurants');
    }

    public function create(): void
    {
        AuthMiddleware::requireAdmin();
        View::renderAdmin('Admin/restaurants/form', ['restaurant' => null], 'New restaurant');
    }

    public function store(): void
    {
        AuthMiddleware::requireAdmin();
        $restaurant = $this->buildFromPost();

        if ($error = $this->validate($restaurant)) {
            Flash::error($error);
            View::renderAdmin('Admin/restaurants/form', ['restaurant' => $restaurant], 'New restaurant');
            return;
        }

        $this->restaurantService->create($restaurant);
        Flash::success('Restaurant created.');
        header('Location: /admin/restaurants');
        exit();
    }

    public function edit(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $restaurant = $this->restaurantService->getById((int)($vars['id'] ?? 0));
        if ($restaurant === null) {
            http_response_code(404);
            echo 'Restaurant not found';
            return;
        }

        View::renderAdmin('Admin/restaurants/form', ['restaurant' => $restaurant], 'Edit restaurant');
    }

    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        $restaurant = $this->buildFromPost();
        $restaurant->id = (int)($_POST['id'] ?? 0);

        if (($error = $this->validate($restaurant)) || $restaurant->id <= 0) {
            Flash::error($error ?? 'Invalid restaurant.');
            View::renderAdmin('Admin/restaurants/form', ['restaurant' => $restaurant], 'Edit restaurant');
            return;
        }

        $this->restaurantService->update($restaurant);
        Flash::success('Restaurant updated.');
        header('Location: /admin/restaurants');
        exit();
    }

    public function delete(): void
    {
        AuthMiddleware::requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->restaurantService->delete($id);
            Flash::success('Restaurant deleted.');
        }
        header('Location: /admin/restaurants');
        exit();
    }

    private function buildFromPost(): RestaurantModel
    {
        $restaurant = new RestaurantModel();
        $restaurant->name = trim($_POST['name'] ?? '');
        $restaurant->cuisine = trim($_POST['cuisine'] ?? '') ?: null;
        $restaurant->description = trim($_POST['description'] ?? '') ?: null;
        $restaurant->address = trim($_POST['address'] ?? '') ?: null;
        $restaurant->stars = ($_POST['stars'] ?? '') !== '' ? (int)$_POST['stars'] : null;
        $restaurant->price_per_seat = ($_POST['price_per_seat'] ?? '') !== '' ? (float)$_POST['price_per_seat'] : null;
        $restaurant->image = trim($_POST['image'] ?? '') ?: null;
        $upload = \App\Framework\ImageUpload::handle('image_file', 'restaurants');
        if (!empty($upload['path'])) {
            $restaurant->image = $upload['path'];
        } elseif (!$upload['ok']) {
            \App\Framework\Flash::error($upload['message']);
        }
        return $restaurant;
    }

    private function validate(RestaurantModel $restaurant): ?string
    {
        if ($restaurant->name === '') {
            return 'Restaurant name is required.';
        }
        if ($restaurant->stars !== null && ($restaurant->stars < 0 || $restaurant->stars > 5)) {
            return 'Stars must be between 0 and 5.';
        }
        if ($restaurant->price_per_seat !== null && $restaurant->price_per_seat < 0) {
            return 'Price per seat cannot be negative.';
        }
        return null;
    }
}
