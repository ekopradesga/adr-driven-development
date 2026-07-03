<?php

namespace App\Models;

use App\Enums\CollectionTaskPaymentSubmissionStatus;
use App\Enums\CollectionTaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'employee_id',
        'status',
        'scheduled_for',
        'route_started_at',
        'visited_at',
        'completed_at',
        'follow_up_reason',
        'cancellation_reason',
        'payment_collected_amount',
        'payment_submission_reference',
        'payment_submission_status',
        'notes',
    ];

    protected $casts = [
        'status' => CollectionTaskStatus::class,
        'payment_submission_status' => CollectionTaskPaymentSubmissionStatus::class,
        'scheduled_for' => 'datetime',
        'route_started_at' => 'datetime',
        'visited_at' => 'datetime',
        'completed_at' => 'datetime',
        'payment_collected_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(CollectionTaskInvoice::class);
    }

    public function scopeWaitingAssignment($query)
    {
        return $query->where('status', CollectionTaskStatus::WaitingAssignment->value);
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', CollectionTaskStatus::Assigned->value);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', CollectionTaskStatus::Scheduled->value);
    }

    public function isWaitingAssignment(): bool
    {
        return $this->status === CollectionTaskStatus::WaitingAssignment;
    }

    public function isAssigned(): bool
    {
        return $this->status === CollectionTaskStatus::Assigned;
    }

    public function isScheduled(): bool
    {
        return $this->status === CollectionTaskStatus::Scheduled;
    }

    public function isOnRoute(): bool
    {
        return $this->status === CollectionTaskStatus::OnRoute;
    }

    public function isCustomerVisited(): bool
    {
        return $this->status === CollectionTaskStatus::CustomerVisited;
    }

    public function isCompleted(): bool
    {
        return $this->status === CollectionTaskStatus::Completed;
    }

    public function isFollowUpRequired(): bool
    {
        return $this->status === CollectionTaskStatus::FollowUpRequired;
    }

    public function isCancelled(): bool
    {
        return $this->status === CollectionTaskStatus::Cancelled;
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }
}