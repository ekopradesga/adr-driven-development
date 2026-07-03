<?php

namespace App\Enums;

enum WireRouterType: string
{
    case Core = 'core';
    case Distribution = 'distribution';

    public function label(): string
    {
        return match ($this) {
            self::Core => 'Core Router',
            self::Distribution => 'Distribution Router',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Core => 'dark',
            self::Distribution => 'info',
        };
    }

    public function isCore(): bool
    {
        return $this === self::Core;
    }

    public function isDistribution(): bool
    {
        return $this === self::Distribution;
    }

    public static function values(): array
    {
        return array_map(static fn (self $type) => $type->value, self::cases());
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}