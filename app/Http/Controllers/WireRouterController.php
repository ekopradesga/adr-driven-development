<?php

namespace App\Http\Controllers;

use App\Http\Requests\WireRouter\ActivateWireRouterRequest;
use App\Http\Requests\WireRouter\MaintenanceWireRouterRequest;
use App\Http\Requests\WireRouter\RetireWireRouterRequest;
use App\Http\Requests\WireRouter\StoreWireRouterRequest;
use App\Http\Requests\WireRouter\UpdateWireRouterRequest;
use App\Models\WireRouter;
use App\Services\Network\WireRouterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WireRouterController extends Controller
{
    public function __construct(
        private readonly WireRouterService $routerService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WireRouter::class);

        return view('routers.index', $this->routerService->buildIndexData(
            $request->only('search', 'status', 'router_type')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', WireRouter::class);

        return view('routers.create', $this->routerService->buildCreateData());
    }

    public function store(StoreWireRouterRequest $request): RedirectResponse
    {
        $router = $this->routerService->create($request->validated());

        return redirect()->route('routers.show', $router)
            ->with('success', 'Router created.');
    }

    public function show(WireRouter $router): View
    {
        $this->authorize('view', $router);

        return view('routers.show', $this->routerService->findForShow($router));
    }

    public function edit(WireRouter $router): View
    {
        $this->authorize('update', $router);

        return view('routers.edit', $this->routerService->buildEditData($router));
    }

    public function update(UpdateWireRouterRequest $request, WireRouter $router): RedirectResponse
    {
        $this->routerService->update($router, $request->validated());

        return redirect()->route('routers.show', $router)
            ->with('success', 'Router updated.');
    }

    public function destroy(WireRouter $router): RedirectResponse
    {
        $this->authorize('delete', $router);

        $this->routerService->delete($router);

        return redirect()->route('routers.index');
    }

    public function activate(ActivateWireRouterRequest $request, WireRouter $router): RedirectResponse
    {
        $this->routerService->activate($router);

        return redirect()->route('routers.show', $router)
            ->with('success', 'Router activated.');
    }

    public function maintenance(MaintenanceWireRouterRequest $request, WireRouter $router): RedirectResponse
    {
        $this->routerService->markMaintenance($router);

        return redirect()->route('routers.show', $router)
            ->with('success', 'Router marked for maintenance.');
    }

    public function retire(RetireWireRouterRequest $request, WireRouter $router): RedirectResponse
    {
        $this->routerService->retire($router);

        return redirect()->route('routers.show', $router)
            ->with('success', 'Router retired.');
    }
}