<?php

namespace App\Models;

use App\Enums\CollectionTaskInvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionTaskInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'collection_task_id',
        'invoice_id',
        'status',
        'inclusion_reason',
        'resolution_outcome',
        'resolved_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'status' => CollectionTaskInvoiceStatus::class,
        'resolved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function collectionTask(): BelongsTo
    {
        return $this->belongsTo(CollectionTask::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeCreated($query)
    {
        return $query->where('status', CollectionTaskInvoiceStatus::Created->value);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', CollectionTaskInvoiceStatus::Resolved->value);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', CollectionTaskInvoiceStatus::Cancelled->value);
    }

    public function isCreated(): bool
    {
        return $this->status === CollectionTaskInvoiceStatus::Created;
    }

    public function isResolved(): bool
    {
        return $this->status === CollectionTaskInvoiceStatus::Resolved;
    }

    public function isCancelled(): bool
    {
        return $this->status === CollectionTaskInvoiceStatus::Cancelled;
    }
}