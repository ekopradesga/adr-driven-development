<?php

namespace App\Services\Identity;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

/**
 * UserService — Identity & Access module.
 *
 * Owns all user management business logic.
 * Controllers delegate to this service and remain thin.
 */
class UserService extends AbstractCrudService
{
    protected string $modelClass = User::class;

    /**
     * Alias for paginate() to maintain backward compatibility.
     */
    public function list(array $filters = [], int $perPage = 25)
    {
        return $this->paginate($filters, $perPage);
    }

    public function create(array $data): User
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'status'   => $data['status'] ?? UserStatus::Active->value,
        ]);
    }

    public function update($user, array $data): User
    {
        $user->name   = $data['name'];
        $user->email  = $data['email'];
        $user->status = $data['status'] ?? $user->status->value;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return $user;
    }

    public function suspend(User $user): User
    {
        $user->update(['status' => UserStatus::Suspended->value]);

        return $user;
    }

    public function activate(User $user): User
    {
        $user->update(['status' => UserStatus::Active->value]);

        return $user;
    }

    /**
     * Soft-deletes the user and marks status as Inactive.
     * Per entities.md: deletion behavior is Soft Delete with Restrict on financial records.
     * Setting status to Inactive before soft-delete signals that the account is
     * decommissioned and not expected to return to active state.
     */
    public function softDelete(User $user): void
    {
        $user->update(['status' => UserStatus::Inactive->value]);
        $user->delete();
    }

    public function assignRole(User $user, Role $role): void
    {
        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    public function removeRole(User $user, Role $role): void
    {
        $user->roles()->detach($role->id);
    }

    public function syncRoles(User $user, array $roleIds): void
    {
        $user->roles()->sync($roleIds);
    }

    /**
     * Load user with roles and permissions for display.
     */
    public function findForShow(User $user): array
    {
        return [
            'user' => $user->loadMissing('roles.permissions', 'directPermissions'),
        ];
    }

    /**
     * Load user with roles for edit form.
     */
    public function findForEdit(User $user): User
    {
        return $user->loadMissing('roles');
    }

    /**
     * Build data for index page.
     */
    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'users'    => $this->paginate($filters, $perPage),
            'statuses' => UserStatus::cases(),
        ];
    }

    /**
     * Build data for create form.
     */
    public function buildCreateData(): array
    {
        return [
            'roles'    => $this->activeRoles(),
            'statuses' => UserStatus::cases(),
        ];
    }

    /**
     * Build data for edit form.
     */
    public function buildEditData(User $user): array
    {
        return [
            'user'     => $this->findForEdit($user),
            'roles'    => $this->activeRoles(),
            'statuses' => UserStatus::cases(),
        ];
    }

    /**
     * Get all active roles for assignment.
     */
    public function activeRoles()
    {
        return Role::active()->orderBy('name')->get();
    }

    // -------------------------------------------------------------------------
    // AbstractCrudService Customization
    // -------------------------------------------------------------------------

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->with('roles');
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    protected function applyDefaultOrdering(Builder $query): Builder
    {
        return $query->orderBy('name');
    }
}
