<?php 
namespace App\Enums;

enum ProductCategory: string
{
    case Computers = 'computers';
    case HomeEntertainment = 'home_entertainment';
    case Wearables = 'wearables';
    case Appliances = 'appliances';

    public static function toSelectOptions(): array
    {
        return array_reduce(self::cases(), function (array $carry, self $case) {
            // Converts [Category::Computers] to ['computers' => 'Computers']
            $carry[$case->value] = $case->name; 
            return $carry;
        }, []);
    }
}