<?php

namespace App\Models;

use App\Enums\OnuStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Onu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'olt_id',
        'fat_id',
        'onu_sn',
        'onu_index',
        'pon_port',
        'model',
        'customer_label',
        'status',
        'rx_power_dbm',
        'tx_power_dbm',
        'last_seen_at',
        'provisioned_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => OnuStatus::class,
        'rx_power_dbm' => 'decimal:3',
        'tx_power_dbm' => 'decimal:3',
        'last_seen_at' => 'datetime',
        'provisioned_at' => 'datetime',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    public function fat(): BelongsTo
    {
        return $this->belongsTo(Fat::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeUnprovisioned($query)
    {
        return $query->where('status', OnuStatus::Unprovisioned->value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', OnuStatus::Active->value);
    }

    public function scopeOffline($query)
    {
        return $query->where('status', OnuStatus::Offline->value);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', OnuStatus::Suspended->value);
    }

    public function scopeRetired($query)
    {
        return $query->where('status', OnuStatus::Retired->value);
    }

    public function isUnprovisioned(): bool
    {
        return $this->status === OnuStatus::Unprovisioned;
    }

    public function isActive(): bool
    {
        return $this->status === OnuStatus::Active;
    }

    public function isOffline(): bool
    {
        return $this->status === OnuStatus::Offline;
    }

    public function isSuspended(): bool
    {
        return $this->status === OnuStatus::Suspended;
    }

    public function isRetired(): bool
    {
        return $this->status === OnuStatus::Retired;
    }
}
