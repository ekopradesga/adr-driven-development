<?php

namespace App\Enums;

enum WireRouterStatus: string
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
            self::Retired => 'danger',
        };
    }

    public function isPlanned(): bool
    {
        return $this === self::Planned;
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isMaintenance(): bool
    {
        return $this === self::Maintenance;
    }

    public function isRetired(): bool
    {
        return $this === self::Retired;
    }

    public static function values(): array
    {
        return array_map(static fn (self $status) => $status->value, self::cases());
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}