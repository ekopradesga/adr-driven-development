<?php

namespace App\Enums;

enum ServiceAreaStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Merged = 'merged';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Merged => 'Merged',
            self::Archived => 'Archived',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::Active => 'success',
            self::Merged => 'warning',
            self::Archived => 'dark',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Merged, self::Archived], true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}