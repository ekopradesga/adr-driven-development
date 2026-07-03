<?php

namespace App\Models;

use App\Enums\WireRouterStatus;
use App\Enums\WireRouterType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WireRouter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'routers';

    protected $fillable = [
        'router_code',
        'name',
        'router_type',
        'vendor',
        'model',
        'ip_address',
        'snmp_community',
        'api_username',
        'api_password',
        'location_name',
        'latitude',
        'longitude',
        'parent_router_id',
        'status',
        'last_seen_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'router_type' => WireRouterType::class,
        'status' => WireRouterStatus::class,
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'last_seen_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_router_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_router_id');
    }

    public function scopePlanned($query)
    {
        return $query->where('status', WireRouterStatus::Planned->value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', WireRouterStatus::Active->value);
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', WireRouterStatus::Maintenance->value);
    }

    public function scopeRetired($query)
    {
        return $query->where('status', WireRouterStatus::Retired->value);
    }

    public function scopeCore($query)
    {
        return $query->where('router_type', WireRouterType::Core->value);
    }

    public function scopeDistribution($query)
    {
        return $query->where('router_type', WireRouterType::Distribution->value);
    }

    public function isPlanned(): bool
    {
        return $this->status === WireRouterStatus::Planned;
    }

    public function isActive(): bool
    {
        return $this->status === WireRouterStatus::Active;
    }

    public function isMaintenance(): bool
    {
        return $this->status === WireRouterStatus::Maintenance;
    }

    public function isRetired(): bool
    {
        return $this->status === WireRouterStatus::Retired;
    }

    public function isCore(): bool
    {
        return $this->router_type === WireRouterType::Core;
    }

    public function isDistribution(): bool
    {
        return $this->router_type === WireRouterType::Distribution;
    }
}