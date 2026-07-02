<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SettingCategory — Settings module (Platform classification).
 *
 * Groups settings by operational domain for governance and discoverability.
 * Categories align with business domains: billing, notification, monitoring, etc.
 *
 * Deletion: Restrict when category has active registry entries or settings.
 */
class SettingCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'label',
        'description',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_visible' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function registryEntries(): HasMany
    {
        return $this->hasMany(SettingRegistryEntry::class, 'category_id');
    }

    /**
     * All settings belonging to this category (via registry entries).
     */
    public function settings(): HasManyThrough
    {
        return $this->hasManyThrough(
            Setting::class,
            SettingRegistryEntry::class,
            'category_id',
            'registry_entry_id'
        );
    }
}
