<?php

namespace App\Models;

use App\Enums\ServiceAreaLevel;
use App\Enums\ServiceAreaStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cluster_id',
        'parent_id',
        'merged_into_service_area_id',
        'name',
        'code',
        'level',
        'boundary_geojson',
        'center_latitude',
        'center_longitude',
        'status',
        'merged_at',
        'archived_at',
        'notes',
    ];

    protected $casts = [
        'level' => ServiceAreaLevel::class,
        'status' => ServiceAreaStatus::class,
        'center_latitude' => 'decimal:7',
        'center_longitude' => 'decimal:7',
        'merged_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(self::class, 'merged_into_service_area_id');
    }

    public function mergedSources(): HasMany
    {
        return $this->hasMany(self::class, 'merged_into_service_area_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)
            ->withPivot(['is_primary', 'assigned_at'])
            ->withTimestamps();
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ServiceAreaStatus::Draft->value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', ServiceAreaStatus::Active->value);
    }

    public function scopeTerminal($query)
    {
        return $query->whereIn('status', [
            ServiceAreaStatus::Merged->value,
            ServiceAreaStatus::Archived->value,
        ]);
    }

    public function isDraft(): bool
    {
        return $this->status === ServiceAreaStatus::Draft;
    }

    public function isActive(): bool
    {
        return $this->status === ServiceAreaStatus::Active;
    }

    public function isMerged(): bool
    {
        return $this->status === ServiceAreaStatus::Merged;
    }

    public function isArchived(): bool
    {
        return $this->status === ServiceAreaStatus::Archived;
    }
}
