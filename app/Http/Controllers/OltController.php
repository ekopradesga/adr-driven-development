<?php

namespace App\Http\Controllers;

use App\Http\Requests\Olt\ActivateOltRequest;
use App\Http\Requests\Olt\MaintenanceOltRequest;
use App\Http\Requests\Olt\RetireOltRequest;
use App\Http\Requests\Olt\StoreOltRequest;
use App\Http\Requests\Olt\UpdateOltRequest;
use App\Models\Olt;
use App\Services\Network\OltService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OltController extends Controller
{
    public function __construct(
        private readonly OltService $oltService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Olt::class);

        return view('olts.index', $this->oltService->buildIndexData(
            $request->only('search', 'status')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Olt::class);

        return view('olts.create', $this->oltService->buildCreateData());
    }

    public function store(StoreOltRequest $request): RedirectResponse
    {
        $olt = $this->oltService->create($request->validated());

        return redirect()->route('olts.show', $olt)
            ->with('success', 'OLT created.');
    }

    public function show(Olt $olt): View
    {
        $this->authorize('view', $olt);

        return view('olts.show', $this->oltService->findForShow($olt));
    }

    public function edit(Olt $olt): View
    {
        $this->authorize('update', $olt);

        return view('olts.edit', $this->oltService->buildEditData($olt));
    }

    public function update(UpdateOltRequest $request, Olt $olt): RedirectResponse
    {
        $this->oltService->update($olt, $request->validated());

        return redirect()->route('olts.show', $olt)
            ->with('success', 'OLT updated.');
    }

    public function destroy(Olt $olt): RedirectResponse
    {
        $this->authorize('delete', $olt);

        $this->oltService->delete($olt);

        return redirect()->route('olts.index');
    }

    public function activate(ActivateOltRequest $request, Olt $olt): RedirectResponse
    {
        $this->oltService->activate($olt);

        return redirect()->route('olts.show', $olt)
            ->with('success', 'OLT activated.');
    }

    public function maintenance(MaintenanceOltRequest $request, Olt $olt): RedirectResponse
    {
        $this->oltService->markMaintenance($olt);

        return redirect()->route('olts.show', $olt)
            ->with('success', 'OLT marked for maintenance.');
    }

    public function retire(RetireOltRequest $request, Olt $olt): RedirectResponse
    {
        $this->oltService->retire($olt);

        return redirect()->route('olts.show', $olt)
            ->with('success', 'OLT retired.');
    }
}
