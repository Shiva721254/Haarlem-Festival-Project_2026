<?php

namespace App\Controllers;

use App\Framework\Http;
use App\Framework\View;
use App\Services\Interfaces\IRestaurantService;

class RestaurantController
{
    private IRestaurantService $restaurantService;

    public function __construct(IRestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    // GET: /restaurant/{id}
    public function show(array $vars = []): void
    {
        $restaurant = $this->restaurantService->getById((int)($vars['id'] ?? 0));
        if ($restaurant === null) {
            Http::notFound('Restaurant not found');
        }
        View::render('Restaurants/detail', [
            'restaurant' => $restaurant,
            'sessions'   => $this->restaurantService->getSessions($restaurant->id),
        ], $restaurant->name);
    }
}
