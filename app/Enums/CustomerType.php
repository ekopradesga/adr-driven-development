<?php

namespace App\Enums;

/**
 * CustomerType — Customer Management module.
 *
 * Values are authoritative from the customers.customer_type migration column.
 * Allowed values: individual (default), business.
 *
 * v1.0 scope: This field is passive in Version 1.0.
 * No differential billing, contact, or tax behavior is applied based on
 * customer_type in v1.0. The field exists for data capture and future
 * v1.1 extension.
 *
 * See: docs/architecture/decisions.md — Customer Type Classification for v1.0
 */
enum CustomerType: string
{
    case Individual = 'individual';
    case Business   = 'business';

    // -------------------------------------------------------------------------
    // Display helpers
    // -------------------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::Business   => 'Business',
        };
    }

    /** Bootstrap contextual color for type badges. */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Individual => 'info',
            self::Business   => 'primary',
        };
    }

    // -------------------------------------------------------------------------
    // State checks
    // -------------------------------------------------------------------------

    public function isIndividual(): bool
    {
        return $this === self::Individual;
    }

    public function isBusiness(): bool
    {
        return $this === self::Business;
    }

    // -------------------------------------------------------------------------
    // Static utilities
    // -------------------------------------------------------------------------

    /**
     * Returns all database values as a flat array.
     * Example: ['individual', 'business']
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Returns an associative array suitable for HTML <select> inputs.
     * Example: ['individual' => 'Individual', 'business' => 'Business']
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases())
        );
    }
}
