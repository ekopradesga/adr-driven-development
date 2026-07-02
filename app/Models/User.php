<?php

namespace App\Models;

use App\Enums\PermissionStatus;
use App\Enums\RoleStatus;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User — Identity & Access module (Aggregate Root).
 *
 * Authorization contract (decisions.md — "Authorization Requires Active Role and Permission Lifecycle"):
 *   - hasRole()       → only returns true for Active roles.
 *   - hasPermission() → only returns true when BOTH the role and the permission are Active.
 *   - hasActiveRole() → returns true when at least one Active role is assigned.
 *
 * Policies, Gates, Middleware, and Services must call these helpers directly.
 * They must never check RoleStatus or PermissionStatus independently.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'status'            => UserStatus::class,
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Direct permission grants (User N <-> N Permission — ERD: direct_grant).
     * These are permissions assigned to the user independently of any role.
     */
    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::Active->value);
    }

    // -------------------------------------------------------------------------
    // Authorization Helpers
    // -------------------------------------------------------------------------

    /**
     * Returns true only if the named role (by slug) is assigned to this user
     * AND that role's status is Active.
     *
     * Per architecture decision: Draft and Deprecated roles never grant authorization.
     *
     * Uses the loaded collection when available to avoid redundant DB queries.
     * Callers in loops should eager-load: $user->load('roles').
     */
    public function hasRole(string $slug): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(
                fn (Role $role) => $role->slug === $slug
                    && $role->status === RoleStatus::Active
            );
        }

        return $this->roles()
            ->where('slug', $slug)
            ->where('status', RoleStatus::Active->value)
            ->exists();
    }

    /**
     * Returns true if at least one Active role is assigned to this user.
     *
     * Used by the authentication flow to ensure access is only granted when
     * the user has a valid operational role (decisions.md — IAM lifecycle decision).
     *
     * Uses the loaded collection when available to avoid redundant DB queries.
     */
    public function hasActiveRole(): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(
                fn (Role $role) => $role->status === RoleStatus::Active
            );
        }

        return $this->roles()
            ->where('status', RoleStatus::Active->value)
            ->exists();
    }

    /**
     * Returns true only if the given permission key is held by this user AND
     * all participating security objects are Active:
     *
     *   Path 1 — Direct grant:
     *     permission.status = Active
     *
     *   Path 2 — Role-based grant:
     *     role.status = Active AND permission.status = Active
     *
     * Per architecture decision: Draft and Deprecated roles and permissions
     * never grant authorization regardless of assignment.
     *
     * Uses loaded collections when available to avoid redundant DB queries.
     * Callers in loops should eager-load: $user->load('roles.permissions', 'directPermissions').
     */
    public function hasPermission(string $key): bool
    {
        // --- Path 1: Direct Active permission grant ---
        $hasDirectGrant = $this->relationLoaded('directPermissions')
            ? $this->directPermissions->contains(
                fn (Permission $p) => $p->key === $key
                    && $p->status === PermissionStatus::Active
              )
            : $this->directPermissions()
                ->where('key', $key)
                ->where('status', PermissionStatus::Active->value)
                ->exists();

        if ($hasDirectGrant) {
            return true;
        }

        // --- Path 2: Via an Active role that holds the Active permission ---
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(function (Role $role) use ($key) {
                if ($role->status !== RoleStatus::Active) {
                    return false;
                }

                return $role->relationLoaded('permissions')
                    ? $role->permissions->contains(
                        fn (Permission $p) => $p->key === $key
                            && $p->status === PermissionStatus::Active
                      )
                    : $role->permissions()
                        ->where('key', $key)
                        ->where('status', PermissionStatus::Active->value)
                        ->exists();
            });
        }

        return $this->roles()
            ->where('status', RoleStatus::Active->value)
            ->whereHas(
                'permissions',
                fn ($q) => $q->where('key', $key)
                             ->where('status', PermissionStatus::Active->value)
            )
            ->exists();
    }

    // -------------------------------------------------------------------------
    // Status Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }
}

