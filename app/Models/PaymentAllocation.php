<?php

namespace App\Models;

use App\Enums\PaymentAllocationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** PaymentAllocation — append-only allocation records. */
class PaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'invoice_id',
        'allocated_amount',
        'status',
        'allocated_at',
        'reversed_at',
        'reversal_reason',
        'notes',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'status' => PaymentAllocationStatus::class,
        'allocated_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isAllocated(): bool
    {
        return $this->status === PaymentAllocationStatus::Allocated;
    }

    public function isReversed(): bool
    {
        return $this->status === PaymentAllocationStatus::Reversed;
    }
}
