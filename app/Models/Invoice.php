<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Invoice — Billing module.
 *
 * Aggregate Root. Invoices are NEVER deleted — no SoftDeletes.
 * After publication, only paid_amount, balance_amount, and status
 * may change (driven by Payment Workflow events and billing policy only).
 *
 * Reference: docs/database/entities.md — Invoice entity
 *            docs/workflows/billing-workflow.md
 */
class Invoice extends Model
{
    use HasFactory;

    // No SoftDeletes — invoices are permanent financial records.

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'subscription_id',
        'status',
        'period_start',
        'period_end',
        'issue_date',
        'due_date',
        'subtotal_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'published_at',
        'overdue_at',
        'cancelled_at',
        'cancellation_reason',
        'notes',
    ];

    protected $casts = [
        'status'          => InvoiceStatus::class,
        'period_start'    => 'date',
        'period_end'      => 'date',
        'issue_date'      => 'date',
        'due_date'        => 'date',
        'subtotal_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'balance_amount'  => 'decimal:2',
        'published_at'    => 'datetime',
        'overdue_at'      => 'datetime',
        'cancelled_at'    => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeDraft($query)
    {
        return $query->where('status', InvoiceStatus::Draft->value);
    }

    public function scopePublished($query)
    {
        return $query->where('status', InvoiceStatus::Published->value);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', InvoiceStatus::Overdue->value);
    }

    public function scopePaid($query)
    {
        return $query->where('status', InvoiceStatus::Paid->value);
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('status', InvoiceStatus::PartiallyPaid->value);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', InvoiceStatus::Cancelled->value);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [
            InvoiceStatus::Published->value,
            InvoiceStatus::PartiallyPaid->value,
            InvoiceStatus::Overdue->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // State checks
    // -------------------------------------------------------------------------

    public function isDraft(): bool         { return $this->status->isDraft(); }
    public function isPublished(): bool     { return $this->status->isPublished(); }
    public function isPartiallyPaid(): bool { return $this->status->isPartiallyPaid(); }
    public function isPaid(): bool          { return $this->status->isPaid(); }
    public function isOverdue(): bool       { return $this->status->isOverdue(); }
    public function isCancelled(): bool     { return $this->status->isCancelled(); }

    /** Invoice items may be added/removed and metadata may be edited. */
    public function isEditable(): bool { return $this->status->isEditable(); }

    /** Invoice may receive payment allocations. */
    public function isPayable(): bool { return $this->status->isPayable(); }

    /** Invoice has reached a terminal state (Paid or Cancelled). */
    public function isTerminal(): bool { return $this->status->isTerminal(); }
}

