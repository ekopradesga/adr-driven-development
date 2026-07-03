<?php

namespace App\Enums;

enum OdfStatus: string
{
    case Installed = 'installed';
    case Active = 'active';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Installed => 'Installed',
            self::Active => 'Active',
            self::Retired => 'Retired',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
