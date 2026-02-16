<?php 
namespace App\Enums;

enum UserRole: string
{
    case Admin = 'Admin';
    case Employee = 'Employee';
    case Customer = 'Customer';

    // Static method to prepare data for a select box
    public static function toSelectOptions(): array
    {
        return array_reduce(self::cases(), function (array $carry, self $case) {
            // Converts [UserRole::Admin] to ['admin' => 'Admin']
            $carry[$case->value] = $case->name; 
            return $carry;
        }, []);
    }
}