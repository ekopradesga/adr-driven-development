<?php

namespace App\Enums;

enum PermissionStatus: string
{
    case Draft      = 'draft';
    case Active     = 'active';
    case Deprecated = 'deprecated';

    public function label(): string
    {
        return match ($this) {
            self::Draft      => 'Draft',
            self::Active     => 'Active',
            self::Deprecated => 'Deprecated',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft      => 'secondary',
            self::Active     => 'success',
            self::Deprecated => 'danger',
        };
    }
}
