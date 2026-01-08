<?php 
namespace App\Enums;

enum ProductType: string
{
    // Computers
    case Laptops = 'laptops';
    case Desktops = 'desktops';
    case Monitors = 'monitors';
    case Keyboards = 'keyboards';

    // Home Entertainment
    case SmartTVs = 'smart_tvs';
    case SoundSystems = 'sound_systems';
    case StreamingDevices = 'streaming_devices';
    case GamingConsoles = 'gaming_consoles';

    // Wearables
    case Smartwatches = 'smartwatches';
    case Smartphones = 'smartphones';
    case FitnessTrackers = 'fitness_trackers';
    case VRHeadsets = 'vr_headsets';

    // Appliances
    case Refrigerators = 'refrigerators';
    case WashingMachines = 'washing_machines';
    case Microwaves = 'microwaves';
    case AirConditioners = 'air_conditioners';

    // In App/Enums/ProductType.php
    public function getCategory(): ProductCategory 
    {
        return match($this) {
            self::Laptops, self::Desktops, self::Monitors, self::Keyboards => ProductCategory::Computers,
            self::SmartTVs, self::SoundSystems, self::StreamingDevices, self::GamingConsoles => ProductCategory::HomeEntertainment,
            self::Smartwatches, self::Smartphones, self::FitnessTrackers, self::VRHeadsets => ProductCategory::Wearables,
            self::Refrigerators, self::WashingMachines, self::Microwaves, self::AirConditioners => ProductCategory::Appliances,
        };
    }

    public static function toSelectOptions(): array
    {
        return array_reduce(self::cases(), function (array $carry, self $case) {
            // Converts [Type::Laptops] to ['laptops' => 'Laptops']
            $carry[$case->value] = $case->name; 
            return $carry;
        }, []);
    }
}