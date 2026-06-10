<?php

namespace App\Controllers;

use App\Framework\View;
use App\Services\RestaurantService;
use App\Services\Interfaces\IRestaurantService;

class RestaurantController
{
    private IRestaurantService $restaurantService;

    public function __construct()
    {
        $this->restaurantService = new RestaurantService();
    }

    // GET: /restaurant/{id}
    public function show(array $vars = []): void
    {
        $restaurant = $this->restaurantService->getById((int)($vars['id'] ?? 0));
        if ($restaurant === null) {
            http_response_code(404);
            echo 'Restaurant not found';
            return;
        }

        View::render('Restaurants/detail', [
            'restaurant' => $restaurant,
            'sessions'   => $this->restaurantService->getSessions($restaurant->id),
        ], $restaurant->name);
    }
}
