<?php
namespace App\Controllers;

use App\Framework\Flash;
use App\Framework\Http;
use App\Framework\Redirect;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\Interfaces\IRestaurantService;

class AdminRestaurantController
{
    private IRestaurantService $restaurantService;

    public function __construct(IRestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
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
        $form = $this->restaurantService->buildAdminFormModel($_POST);
        if ($error = ($form['error'] ?? $form['uploadError'])) {
            $this->renderForm($form['restaurant'], $error, 'New restaurant');
        }
        $this->restaurantService->create($form['restaurant']);
        $this->saved('Restaurant created.');
    }

    public function edit(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $restaurant = $this->restaurantService->getById((int)($vars['id'] ?? 0));
        if ($restaurant === null) {
            Http::notFound('Restaurant not found');
        }
        View::renderAdmin('Admin/restaurants/form', ['restaurant' => $restaurant], 'Edit restaurant');
    }

    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        $form = $this->restaurantService->buildAdminFormModel($_POST);
        $restaurant = $form['restaurant'];
        if (($error = ($form['error'] ?? $form['uploadError'])) || $restaurant->id <= 0) {
            $this->renderForm($restaurant, $error ?? 'Invalid restaurant.', 'Edit restaurant');
        }
        $this->restaurantService->update($restaurant);
        $this->saved('Restaurant updated.');
    }

    public function delete(): void
    {
        AuthMiddleware::requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->restaurantService->delete($id);
            Flash::success('Restaurant deleted.');
        }
        Redirect::to('/admin/restaurants');
    }

    /** Re-render the form with an error and stop. */
    private function renderForm(?object $restaurant, string $error, string $title): never
    {
        Flash::error($error);
        View::renderAdmin('Admin/restaurants/form', ['restaurant' => $restaurant], $title);
        exit();
    }

    private function saved(string $message): never
    {
        Flash::success($message);
        Redirect::to('/admin/restaurants');
    }
}
