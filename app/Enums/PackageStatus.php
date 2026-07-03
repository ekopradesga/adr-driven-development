<?php

namespace App\Enums;

enum PackageStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Deprecated = 'deprecated';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Deprecated => 'Deprecated',
            self::Retired => 'Retired',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::Active => 'success',
            self::Deprecated => 'warning',
            self::Retired => 'dark',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
