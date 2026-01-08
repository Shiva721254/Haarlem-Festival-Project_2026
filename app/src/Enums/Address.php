<?
namespace App\Enums;
enum Address: string
{
    case Amsterdam = 'amsterdam';
    case Rotterdam = 'rotterdam';
    case TheHague = 'theHague';

    public static function toSelectOptions(): array
    {
        return array_reduce(self::cases(), function (array $carry, self $case) {
            // Converts [Address::Amsterdam] to ['amsterdam' => 'Amsterdam']
            $carry[$case->value] = $case->name; 
            return $carry;
        }, []);
    }
}