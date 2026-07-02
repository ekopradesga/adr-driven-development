<?php

namespace App\Enums;

/**
 * CustomerStatus — Customer Management module.
 *
 * Values are authoritative from the customers.status migration column.
 * Allowed values: prospect (default), active, suspended, terminated.
 *
 * Lifecycle: Prospect → Active → Suspended → Terminated
 *   prospect:   new enquiry; no active service contract yet.
 *   active:     at least one active subscription exists; service is live.
 *   suspended:  account suspended by administrative action (fraud, policy
 *               violation, contractual breach). Not triggered by billing policy.
 *               Billing-driven suspension operates on Subscription, not Customer.
 *   terminated: account permanently closed; no future service expected.
 *
 * Transition rules and business logic belong to CustomerService / state machine.
 * This enum represents state only.
 */
enum CustomerStatus: string
{
    case Prospect   = 'prospect';
    case Active     = 'active';
    case Suspended  = 'suspended';
    case Terminated = 'terminated';

    // -------------------------------------------------------------------------
    // Display helpers
    // -------------------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::Prospect   => 'Prospect',
            self::Active     => 'Active',
            self::Suspended  => 'Suspended',
            self::Terminated => 'Terminated',
        };
    }

    /** Bootstrap contextual color for AdminLTE status badges. */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Prospect   => 'secondary',
            self::Active     => 'success',
            self::Suspended  => 'warning',
            self::Terminated => 'danger',
        };
    }

    // -------------------------------------------------------------------------
    // State checks
    // -------------------------------------------------------------------------

    public function isProspect(): bool
    {
        return $this === self::Prospect;
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isSuspended(): bool
    {
        return $this === self::Suspended;
    }

    public function isTerminated(): bool
    {
        return $this === self::Terminated;
    }

    // -------------------------------------------------------------------------
    // Static utilities
    // -------------------------------------------------------------------------

    /**
     * Returns all database values as a flat array.
     * Example: ['prospect', 'active', 'suspended', 'terminated']
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Returns an associative array suitable for HTML <select> inputs.
     * Example: ['prospect' => 'Prospect', 'active' => 'Active', ...]
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases())
        );
    }
}
