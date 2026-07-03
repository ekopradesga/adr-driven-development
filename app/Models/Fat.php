<?php

namespace App\Models;

use App\Enums\FatStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'odf_id',
        'service_area_id',
        'fat_code',
        'name',
        'capacity_ports',
        'used_ports',
        'splitter_ratio',
        'location_name',
        'latitude',
        'longitude',
        'status',
        'last_onu_ping_at',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => FatStatus::class,
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'last_onu_ping_at' => 'datetime',
    ];

    public function odf(): BelongsTo
    {
        return $this->belongsTo(Odf::class);
    }

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    public function scopePlanned($query)
    {
        return $query->where('status', FatStatus::Planned->value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', FatStatus::Active->value);
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', FatStatus::Maintenance->value);
    }

    public function scopeRetired($query)
    {
        return $query->where('status', FatStatus::Retired->value);
    }

    public function isPlanned(): bool
    {
        return $this->status === FatStatus::Planned;
    }

    public function isActive(): bool
    {
        return $this->status === FatStatus::Active;
    }

    public function isMaintenance(): bool
    {
        return $this->status === FatStatus::Maintenance;
    }

    public function isRetired(): bool
    {
        return $this->status === FatStatus::Retired;
    }
}
