<?php
namespace App\ViewModels;
use App\Models\ProductRating;

class ManageRatingViewModel
{
    public ?ProductRating $ratingModel;

    public function __construct(?ProductRating $ratingModel = null)
    {
        $this->ratingModel = $ratingModel;
    }
}