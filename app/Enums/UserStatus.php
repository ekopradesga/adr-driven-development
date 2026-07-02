<?php

namespace App\Enums;

/**
 * UserStatus — Identity & Access module.
 *
 * Values are authoritative from the users.status migration column.
 * Allowed values: active (default), inactive, suspended.
 *
 * Lifecycle: Active → Suspended → Inactive
 *   active:    user can authenticate and perform operations.
 *   suspended: temporarily blocked; reversible by an administrator.
 *   inactive:  decommissioned; account no longer expected to return to active.
 */
enum UserStatus: string
{
    case Active    = 'active';
    case Inactive  = 'inactive';
    case Suspended = 'suspended';

    // -------------------------------------------------------------------------
    // Display helpers
    // -------------------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::Active    => 'Active',
            self::Inactive  => 'Inactive',
            self::Suspended => 'Suspended',
        };
    }

    /** Bootstrap contextual color for status badges. */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Active    => 'success',
            self::Inactive  => 'secondary',
            self::Suspended => 'warning',
        };
    }

    // -------------------------------------------------------------------------
    // State checks
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isInactive(): bool
    {
        return $this === self::Inactive;
    }

    public function isSuspended(): bool
    {
        return $this === self::Suspended;
    }

    /** Whether the user is allowed to authenticate. Only Active users may. */
    public function canAuthenticate(): bool
    {
        return $this->isActive();
    }

    // -------------------------------------------------------------------------
    // Static utilities
    // -------------------------------------------------------------------------

    /**
     * Returns all database values as a flat array.
     * Example: ['active', 'inactive', 'suspended']
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Returns an associative array suitable for HTML <select> inputs.
     * Example: ['active' => 'Active', 'inactive' => 'Inactive', ...]
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases())
        );
    }
}

