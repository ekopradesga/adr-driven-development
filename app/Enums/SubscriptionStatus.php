<?php

namespace App\Enums;

/**
 * SubscriptionStatus — Customer Management module.
 *
 * Values are authoritative from the subscriptions.status migration column.
 *
 * Lifecycle: Pending → Active → Suspended → Reactivation Pending → Terminated
 *   pending:              pre-activation; survey, installation, provisioning phases.
 *   active:               service live; billing and monitoring running.
 *   suspended:            service restricted; see suspension_type for sub-type.
 *   reactivation_pending: payment confirmed; service restoration in progress.
 *   terminated:           permanently closed; terminal state.
 *
 * Pre-activation sub-phases are tracked through child entities (Survey,
 * Installation, ProvisioningRequest) while the Subscription stays in pending.
 *
 * See: docs/architecture/decisions.md — Subscription Lifecycle Canonical States
 *      docs/workflows/subscription-lifecycle.md
 */
enum SubscriptionStatus: string
{
    case Pending             = 'pending';
    case Active              = 'active';
    case Suspended           = 'suspended';
    case ReactivationPending = 'reactivation_pending';
    case Terminated          = 'terminated';

    // -------------------------------------------------------------------------
    // Display helpers
    // -------------------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::Pending             => 'Pending',
            self::Active              => 'Active',
            self::Suspended           => 'Suspended',
            self::ReactivationPending => 'Reactivation Pending',
            self::Terminated          => 'Terminated',
        };
    }

    /** Bootstrap contextual color for AdminLTE status badges. */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending             => 'secondary',
            self::Active              => 'success',
            self::Suspended           => 'warning',
            self::ReactivationPending => 'info',
            self::Terminated          => 'danger',
        };
    }

    // -------------------------------------------------------------------------
    // State checks
    // -------------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isSuspended(): bool
    {
        return $this === self::Suspended;
    }

    public function isReactivationPending(): bool
    {
        return $this === self::ReactivationPending;
    }

    public function isTerminated(): bool
    {
        return $this === self::Terminated;
    }

    // -------------------------------------------------------------------------
    // Static utilities
    // -------------------------------------------------------------------------

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
