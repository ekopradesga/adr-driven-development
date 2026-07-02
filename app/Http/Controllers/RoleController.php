<?php

namespace App\Http\Controllers;

use App\Enums\RoleStatus;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Services\Identity\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        return view('roles.index', $this->roleService->buildIndexData(
            $request->only('search', 'status')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('roles.create', $this->roleService->buildCreateData());
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = $this->roleService->create($request->validated());

        return redirect()->route('roles.show', $role)
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role): View
    {
        $this->authorize('view', $role);

        return view('roles.show', $this->roleService->findForShow($role));
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('roles.edit', $this->roleService->buildEditData($role));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roleService->update($role, $request->validated());

        if ($request->has('permissions')) {
            $this->roleService->syncPermissions($role, $request->input('permissions', []));
        } else {
            $this->roleService->syncPermissions($role, []);
        }

        return redirect()->route('roles.show', $role)
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if (!$this->roleService->canDelete($role)) {
            return back()->with('error', "Cannot delete '{$role->name}' — it is currently assigned to users.");
        }

        $this->roleService->delete($role);

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted.');
    }
}
