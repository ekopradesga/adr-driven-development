<?php

namespace App\Models;

use App\Enums\OltStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Olt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'olt_code',
        'name',
        'vendor',
        'model',
        'ip_address',
        'snmp_community',
        'api_username',
        'api_password',
        'location_name',
        'latitude',
        'longitude',
        'status',
        'last_seen_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => OltStatus::class,
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'last_seen_at' => 'datetime',
    ];

    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    public function scopePlanned($query)
    {
        return $query->where('status', OltStatus::Planned->value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', OltStatus::Active->value);
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', OltStatus::Maintenance->value);
    }

    public function scopeRetired($query)
    {
        return $query->where('status', OltStatus::Retired->value);
    }

    public function isPlanned(): bool
    {
        return $this->status === OltStatus::Planned;
    }

    public function isActive(): bool
    {
        return $this->status === OltStatus::Active;
    }

    public function isMaintenance(): bool
    {
        return $this->status === OltStatus::Maintenance;
    }

    public function isRetired(): bool
    {
        return $this->status === OltStatus::Retired;
    }
}
