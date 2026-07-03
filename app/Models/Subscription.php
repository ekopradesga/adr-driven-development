<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Subscription — Customer Management module (Aggregate Root).
 *
 * Lifecycle: Pending → Active → Suspended → Reactivation Pending → Terminated
 * Lifecycle transitions are governed by SubscriptionService.
 *
 * Reference: docs/database/entities.md — Subscription entity
 *            docs/workflows/subscription-lifecycle.md
 */
class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'package_id',
        'onu_id',
        'status',
        'subscription_type',
        'suspension_type',
        'suspension_reason',
        'suspended_at',
        'activated_at',
        'reactivation_requested_at',
        'terminated_at',
        'terminated_reason',
        'billing_day',
        'notes',
    ];

    protected $casts = [
        'status'                    => SubscriptionStatus::class,
        'subscription_type'         => SubscriptionType::class,
        'suspended_at'              => 'datetime',
        'activated_at'              => 'datetime',
        'reactivation_requested_at' => 'datetime',
        'terminated_at'             => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function onu(): BelongsTo
    {
        return $this->belongsTo(Onu::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('status', SubscriptionStatus::Pending->value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', SubscriptionStatus::Active->value);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', SubscriptionStatus::Suspended->value);
    }

    public function scopeTerminated($query)
    {
        return $query->where('status', SubscriptionStatus::Terminated->value);
    }

    public function scopePrimary($query)
    {
        return $query->where('subscription_type', SubscriptionType::Primary->value);
    }

    // -------------------------------------------------------------------------
    // State checks
    // -------------------------------------------------------------------------

    public function isPending(): bool   { return $this->status === SubscriptionStatus::Pending; }
    public function isActive(): bool    { return $this->status === SubscriptionStatus::Active; }
    public function isSuspended(): bool { return $this->status === SubscriptionStatus::Suspended; }
    public function isReactivationPending(): bool { return $this->status === SubscriptionStatus::ReactivationPending; }
    public function isTerminated(): bool { return $this->status === SubscriptionStatus::Terminated; }
    public function isPrimary(): bool   { return $this->subscription_type === SubscriptionType::Primary; }

    public function isSuspendedOverdue(): bool
    {
        return $this->isSuspended() && $this->suspension_type === 'overdue';
    }

    public function isSuspendedManual(): bool
    {
        return $this->isSuspended() && $this->suspension_type === 'manual';
    }
}
