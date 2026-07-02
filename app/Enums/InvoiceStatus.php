<?php

namespace App\Enums;

/**
 * InvoiceStatus — Billing module.
 *
 * Values are authoritative from the invoices.status migration column.
 *
 * Lifecycle: Draft → Published → (Partially Paid / Paid / Overdue) → [terminal]
 *   draft:          generated; editable; not payable; not customer-visible.
 *   published:      frozen; payable; customer-visible; permanently immutable.
 *   partially_paid: at least one payment allocation; balance remains.
 *   paid:           fully settled; balance is zero; terminal.
 *   overdue:        past due date + grace period without full settlement.
 *   cancelled:      cancelled before publication; terminal (draft only).
 *
 * IMPORTANT: Invoices are NEVER deleted. The invoices table has no deleted_at.
 * After publication, only paid_amount, balance_amount, and status may change
 * (via Payment Workflow events only).
 *
 * See: docs/architecture/decisions.md — Invoice Lifecycle Canonical States
 *      docs/workflows/billing-workflow.md
 */
enum InvoiceStatus: string
{
    case Draft         = 'draft';
    case Published     = 'published';
    case PartiallyPaid = 'partially_paid';
    case Paid          = 'paid';
    case Overdue       = 'overdue';
    case Cancelled     = 'cancelled';

    // -------------------------------------------------------------------------
    // Display helpers
    // -------------------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::Draft         => 'Draft',
            self::Published     => 'Published',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid          => 'Paid',
            self::Overdue       => 'Overdue',
            self::Cancelled     => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft         => 'secondary',
            self::Published     => 'primary',
            self::PartiallyPaid => 'info',
            self::Paid          => 'success',
            self::Overdue       => 'danger',
            self::Cancelled     => 'dark',
        };
    }

    // -------------------------------------------------------------------------
    // State checks
    // -------------------------------------------------------------------------

    public function isDraft(): bool         { return $this === self::Draft; }
    public function isPublished(): bool     { return $this === self::Published; }
    public function isPartiallyPaid(): bool { return $this === self::PartiallyPaid; }
    public function isPaid(): bool          { return $this === self::Paid; }
    public function isOverdue(): bool       { return $this === self::Overdue; }
    public function isCancelled(): bool     { return $this === self::Cancelled; }

    /** Whether the invoice is editable (items can be added/removed). */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    /** Whether the invoice is in any payable state (published, partially paid, overdue). */
    public function isPayable(): bool
    {
        return in_array($this, [self::Published, self::PartiallyPaid, self::Overdue]);
    }

    /** Whether the invoice has any payment against it. */
    public function hasPayment(): bool
    {
        return in_array($this, [self::PartiallyPaid, self::Paid]);
    }

    /** Whether the invoice is in a terminal state (no further transitions). */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Paid, self::Cancelled]);
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
