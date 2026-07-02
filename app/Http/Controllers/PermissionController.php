<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Services\Identity\PermissionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PermissionController — Identity & Access module.
 *
 * Permissions are governed by seeders.
 * Create/delete through the UI is disabled per the sprint specification.
 * Only list and view are exposed.
 */
class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Permission::class);

        return view('permissions.index', $this->permissionService->buildIndexData(
            $request->only('search', 'category')
        ));
    }

    public function show(Permission $permission): View
    {
        $this->authorize('view', $permission);

        return view('permissions.show', $this->permissionService->findForShow($permission));
    }
}
