<?php

namespace App\Models;

use App\Enums\PermissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Permission — Identity & Access module.
 *
 * Defines granular system capabilities using dot-notation keys (e.g. customer.view).
 * Assigned to users via roles (permission_role) or directly (permission_user).
 *
 * Lifecycle: Draft → Active → Deprecated (entities.md).
 * Deletion: Restrict when bound to roles or direct assignments.
 */
class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'status',
    ];

    protected $casts = [
        'status' => PermissionStatus::class,
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Users who have this permission granted directly (not via role).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', PermissionStatus::Active->value);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
