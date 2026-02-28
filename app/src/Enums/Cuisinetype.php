<?php 
namespace App\Enums;

enum ProductCategory: string
{
    case Computers = 'Dutch';
    case HomeEntertainment = 'Fish';
    case Wearables = 'SeaFood';
    case Appliances = 'European';
     case Appliances = 'Vegan'
      case Appliances = 'French';
       case Appliances = 'Modern';
        public static function toSelectOptions(): array
    {
        return array_reduce(self::cases(), function (array $carry, self $case) {
            // Converts [Category::Computers] to ['computers' => 'Computers']
            $carry[$case->value] = $case->name; 
            return $carry;
        }, []);
    }
}