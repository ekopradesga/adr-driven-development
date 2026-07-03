<?php

namespace App\Enums;

enum ClusterStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Planned => 'secondary',
            self::Active => 'success',
            self::Inactive => 'dark',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}