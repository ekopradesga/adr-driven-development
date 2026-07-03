<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Payment — Payments module aggregate root. */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'customer_id',
        'status',
        'payment_date',
        'amount',
        'currency',
        'method',
        'channel_reference',
        'received_by',
        'recorded_at',
        'completed_at',
        'reversed_at',
        'reversal_reason',
        'failure_reason',
        'notes',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'recorded_at' => 'datetime',
        'completed_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [
            PaymentStatus::Completed->value,
            PaymentStatus::Reversed->value,
            PaymentStatus::Failed->value,
        ]);
    }

    public function allocatedAmount(): float
    {
        return (float) $this->allocations()
            ->where('status', 'allocated')
            ->sum('allocated_amount');
    }

    public function unallocatedAmount(): float
    {
        return max(0.0, (float) $this->amount - $this->allocatedAmount());
    }

    public function isIntentCreated(): bool { return $this->status === PaymentStatus::IntentCreated; }
    public function isWaitingPayment(): bool { return $this->status === PaymentStatus::WaitingPayment; }
    public function isReceived(): bool { return $this->status === PaymentStatus::Received; }
    public function isValidated(): bool { return $this->status === PaymentStatus::Validated; }
    public function isRecorded(): bool { return $this->status === PaymentStatus::Recorded; }
    public function isPartiallyAllocated(): bool { return $this->status === PaymentStatus::PartiallyAllocated; }
    public function isFullyAllocated(): bool { return $this->status === PaymentStatus::FullyAllocated; }
    public function isCompleted(): bool { return $this->status === PaymentStatus::Completed; }
    public function isReversed(): bool { return $this->status === PaymentStatus::Reversed; }
    public function isFailed(): bool { return $this->status === PaymentStatus::Failed; }
}
