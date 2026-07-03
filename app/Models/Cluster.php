<?php

namespace App\Models;

use App\Enums\ClusterStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cluster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'status',
        'description',
        'notes',
    ];

    protected $casts = [
        'status' => ClusterStatus::class,
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function serviceAreas(): HasMany
    {
        return $this->hasMany(ServiceArea::class);
    }

    public function scopePlanned($query)
    {
        return $query->where('status', ClusterStatus::Planned->value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', ClusterStatus::Active->value);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', ClusterStatus::Inactive->value);
    }

    public function isPlanned(): bool
    {
        return $this->status === ClusterStatus::Planned;
    }

    public function isActive(): bool
    {
        return $this->status === ClusterStatus::Active;
    }

    public function isInactive(): bool
    {
        return $this->status === ClusterStatus::Inactive;
    }
}
