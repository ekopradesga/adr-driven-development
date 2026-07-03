<?php

namespace App\Enums;

enum OnuStatus: string
{
    case Unprovisioned = 'unprovisioned';
    case Active = 'active';
    case Offline = 'offline';
    case Suspended = 'suspended';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Unprovisioned => 'Unprovisioned',
            self::Active => 'Active',
            self::Offline => 'Offline',
            self::Suspended => 'Suspended',
            self::Retired => 'Retired',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Unprovisioned => 'secondary',
            self::Active => 'success',
            self::Offline => 'warning',
            self::Suspended => 'info',
            self::Retired => 'danger',
        };
    }

    public function isUnprovisioned(): bool
    {
        return $this === self::Unprovisioned;
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isOffline(): bool
    {
        return $this === self::Offline;
    }

    public function isSuspended(): bool
    {
        return $this === self::Suspended;
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