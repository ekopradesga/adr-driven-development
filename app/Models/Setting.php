<?php

namespace App\Models;

use App\Enums\SettingDataType;
use App\Enums\SettingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Setting — Settings module (Platform classification).
 *
 * Stores typed configuration values by key and scope.
 * Scope hierarchy: customer > area/cluster > global.
 * SettingService resolves scope fallback automatically.
 *
 * scope_id = 0 for global scope; non-zero for area, cluster, or customer scope.
 *
 * Deletion: Soft Delete for non-critical keys; Archive for governance history.
 */
class Setting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'registry_entry_id',
        'key',
        'value',
        'scope',
        'scope_id',
        'is_active',
    ];

    protected $casts = [
        'scope'    => SettingScope::class,
        'scope_id' => 'integer',
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function registryEntry(): BelongsTo
    {
        return $this->belongsTo(SettingRegistryEntry::class, 'registry_entry_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->where('scope', SettingScope::Global->value)->where('scope_id', 0);
    }

    public function scopeForScope($query, SettingScope $scope, int $scopeId = 0)
    {
        return $query->where('scope', $scope->value)->where('scope_id', $scopeId);
    }

    // -------------------------------------------------------------------------
    // Value Resolution
    // -------------------------------------------------------------------------

    /**
     * Returns the stored value cast to the data type defined in the registry entry.
     * Falls back to the registry default if value is null.
     */
    public function getTypedValue(): mixed
    {
        $raw = $this->value ?? $this->registryEntry?->default_value;

        if ($raw === null) {
            return null;
        }

        return match ($this->registryEntry?->data_type) {
            SettingDataType::Integer => (int) $raw,
            SettingDataType::Boolean => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            SettingDataType::Json    => json_decode($raw, true),
            SettingDataType::Decimal => (float) $raw,
            default                  => $raw,
        };
    }
}
