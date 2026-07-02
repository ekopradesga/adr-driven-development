<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InvoiceItem — Billing module.
 *
 * Line item detail under an Invoice.
 * May be added or removed only while the parent Invoice is in Draft status.
 * Once the parent Invoice is published, InvoiceItems are permanently immutable.
 * No SoftDeletes — hard delete only while Invoice is Draft.
 *
 * Reference: docs/database/entities.md — InvoiceItem entity
 */
class InvoiceItem extends Model
{
    use HasFactory;

    // No SoftDeletes — hard delete only, and only while Invoice is Draft.

    protected $fillable = [
        'invoice_id',
        'description',
        'item_type',
        'quantity',
        'unit_price',
        'total_amount',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'unit_price'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sort_order'   => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function itemTypeLabel(): string
    {
        return match ($this->item_type) {
            'subscription'  => 'Subscription',
            'installation'  => 'Installation',
            'addon'         => 'Add-on',
            'discount'      => 'Discount',
            'adjustment'    => 'Adjustment',
            default         => ucfirst($this->item_type),
        };
    }
}
