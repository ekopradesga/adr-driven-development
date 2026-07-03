<?php

namespace App\Models;

use App\Enums\PackageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'package_code',
        'name',
        'downstream_kbps',
        'upstream_kbps',
        'contention_ratio',
        'monthly_price',
        'setup_fee',
        'billing_cycle_type',
        'billing_cycle_days',
        'status',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'status' => PackageStatus::class,
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', PackageStatus::Draft->value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', PackageStatus::Active->value);
    }

    public function scopeDeprecated($query)
    {
        return $query->where('status', PackageStatus::Deprecated->value);
    }

    public function scopeRetired($query)
    {
        return $query->where('status', PackageStatus::Retired->value);
    }

    public function isDraft(): bool
    {
        return $this->status === PackageStatus::Draft;
    }

    public function isActive(): bool
    {
        return $this->status === PackageStatus::Active;
    }

    public function isDeprecated(): bool
    {
        return $this->status === PackageStatus::Deprecated;
    }

    public function isRetired(): bool
    {
        return $this->status === PackageStatus::Retired;
    }
}
