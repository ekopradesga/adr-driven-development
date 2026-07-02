<?php

namespace App\Models;

use App\Enums\SettingDataType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SettingRegistryEntry — Settings module (Platform classification).
 *
 * Defines the approved schema for each setting key: data type, default value,
 * and validation rules. Only registered entries may have Setting values created,
 * preventing configuration sprawl (entities.md — SettingRegistryEntry).
 *
 * Deletion: Restrict when in-use settings exist.
 */
class SettingRegistryEntry extends Model
{
    protected $fillable = [
        'category_id',
        'key',
        'name',
        'description',
        'data_type',
        'default_value',
        'validation_rules',
        'is_visible',
    ];

    protected $casts = [
        'data_type'        => SettingDataType::class,
        'validation_rules' => 'array',
        'is_visible'       => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(SettingCategory::class, 'category_id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'registry_entry_id');
    }
}
