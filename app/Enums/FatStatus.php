<?php

namespace App\Enums;

enum FatStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Active => 'Active',
            self::Maintenance => 'Maintenance',
            self::Retired => 'Retired',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Planned => 'secondary',
            self::Active => 'success',
            self::Maintenance => 'warning',
            self::Retired => 'dark',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
