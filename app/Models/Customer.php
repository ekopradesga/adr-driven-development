<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Customer — Customer Management module (Aggregate Root).
 *
 * Lifecycle: Prospect → Active → Suspended → Terminated
 * Lifecycle transitions are governed by CustomerService and customer-workflow.md.
 *
 * This model is a persistence object only.
 * Business logic, transaction management, and event dispatch belong in CustomerService.
 *
 * Reference: docs/database/entities.md — Customer entity
 *            docs/workflows/customer-workflow.md
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_number',
        'name',
        'customer_type',
        'email',
        'phone',
        'whatsapp_phone',
        'alt_phone',
        'address',
        'latitude',
        'longitude',
        'notes',
        'cluster_id',
        'service_area_id',
        'user_id',
        'status',
    ];

    protected $casts = [
        'status'        => CustomerStatus::class,
        'customer_type' => CustomerType::class,
        'latitude'      => 'decimal:7',
        'longitude'     => 'decimal:7',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The Cluster this Customer belongs to.
     * ERD: Customer N → 1 Cluster
     */
    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    /**
     * The ServiceArea this Customer belongs to.
     * ERD: Customer N → 1 ServiceArea
     */
    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class);
    }

    /**
     * Optional portal User account.
     * ERD: Customer 0..1 → 1 User (nullable FK)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All Subscriptions owned by this Customer.
     * ERD: Customer 1 → N Subscription
     * Access: $customer->subscriptions() — full list
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Invoices billed to this Customer.
     * ERD: Customer 1 → N Invoice (direct FK — intentional denormalization for Customer 360).
     * Access: $customer->invoices() — direct query, no Subscription traversal needed.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Payments made by this Customer.
     * ERD: Customer 1 → N Payment (direct FK — intentional denormalization for Customer 360).
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** Filter to Prospect customers only. */
    public function scopeProspect($query)
    {
        return $query->where('status', CustomerStatus::Prospect->value);
    }

    /** Filter to Active customers only. */
    public function scopeActive($query)
    {
        return $query->where('status', CustomerStatus::Active->value);
    }

    /** Filter to Suspended customers only. */
    public function scopeSuspended($query)
    {
        return $query->where('status', CustomerStatus::Suspended->value);
    }

    /** Filter to Terminated customers only. */
    public function scopeTerminated($query)
    {
        return $query->where('status', CustomerStatus::Terminated->value);
    }

    // -------------------------------------------------------------------------
    // State checks
    // -------------------------------------------------------------------------

    public function isProspect(): bool
    {
        return $this->status === CustomerStatus::Prospect;
    }

    public function isActive(): bool
    {
        return $this->status === CustomerStatus::Active;
    }

    public function isSuspended(): bool
    {
        return $this->status === CustomerStatus::Suspended;
    }

    public function isTerminated(): bool
    {
        return $this->status === CustomerStatus::Terminated;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns display name with customer number for use in dropdowns and labels.
     * Example: "CUST-000001 — John Smith"
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->customer_number} — {$this->name}";
    }
}
