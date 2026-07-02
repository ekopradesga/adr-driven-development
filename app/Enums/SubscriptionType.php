<?php

namespace App\Enums;

/**
 * SubscriptionType — Customer Management module.
 *
 * Values are authoritative from the subscriptions.subscription_type column.
 *
 * v1.0 scope: primary is the default and only active type in v1.0.
 * addon type is defined in schema for future use; no differential rules applied.
 *
 * See: docs/architecture/decisions.md — Subscription Type Classification for v1.0
 */
enum SubscriptionType: string
{
    case Primary = 'primary';
    case Addon   = 'addon';

    public function label(): string
    {
        return match ($this) {
            self::Primary => 'Primary',
            self::Addon   => 'Add-on',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Primary => 'primary',
            self::Addon   => 'secondary',
        };
    }

    public function isPrimary(): bool
    {
        return $this === self::Primary;
    }

    public function isAddon(): bool
    {
        return $this === self::Addon;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases())
        );
    }
}
