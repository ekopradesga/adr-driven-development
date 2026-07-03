<?php

namespace App\Enums;

enum ServiceAreaLevel: string
{
    case Region = 'region';
    case Branch = 'branch';
    case Area = 'area';

    public function label(): string
    {
        return match ($this) {
            self::Region => 'Region',
            self::Branch => 'Branch',
            self::Area => 'Area',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}