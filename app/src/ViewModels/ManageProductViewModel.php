<?php
namespace App\ViewModels;

use App\Models\ProductModel;
use App\Enums\ProductCategory; 
use App\Enums\ProductType;

class ManageProductViewModel
{
    public ?ProductModel $product;
    // The array will hold key-value pairs (value => display name) or objects.
    /** @var array<string|int, string> */
    public array $productCategoryOptions;

    /** @var array<string|int, string> */
    public array $productTypeOptions;

    // Using simple arrays to hold the enum values, replacing C#'s Enum.GetValues().
    /** @var array<ProductCategory> */
    public array $productCategory;

    /** @var array<ProductType> */
    public array $productTypes;
    
    // The constructor is used for initialization logic.
    public function __construct(?ProductModel $product = null)
    {
        $this->product = $product;
        // Populate the arrays with Enum cases
        $this->productCategory = ProductCategory::cases();
        $this->productTypes = ProductType::cases();

        // Populate the select box options (assuming static methods exist on Enums)
        $this->productCategoryOptions = ProductCategory::toSelectOptions();
        $this->productTypeOptions = ProductType::toSelectOptions();
    }
}