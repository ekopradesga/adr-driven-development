<?php

namespace App\Http\Controllers;

use App\Http\Requests\Fat\ActivateFatRequest;
use App\Http\Requests\Fat\MaintenanceFatRequest;
use App\Http\Requests\Fat\RetireFatRequest;
use App\Http\Requests\Fat\StoreFatRequest;
use App\Http\Requests\Fat\UpdateFatRequest;
use App\Models\Fat;
use App\Services\Network\FatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FatController extends Controller
{
    public function __construct(
        private readonly FatService $fatService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Fat::class);

        return view('fats.index', $this->fatService->buildIndexData(
            $request->only('search', 'status')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Fat::class);

        return view('fats.create', $this->fatService->buildCreateData());
    }

    public function store(StoreFatRequest $request): RedirectResponse
    {
        $fat = $this->fatService->create($request->validated());

        return redirect()->route('fats.show', $fat)
            ->with('success', 'FAT created.');
    }

    public function show(Fat $fat): View
    {
        $this->authorize('view', $fat);

        return view('fats.show', $this->fatService->findForShow($fat));
    }

    public function edit(Fat $fat): View
    {
        $this->authorize('update', $fat);

        return view('fats.edit', $this->fatService->buildEditData($fat));
    }

    public function update(UpdateFatRequest $request, Fat $fat): RedirectResponse
    {
        $this->fatService->update($fat, $request->validated());

        return redirect()->route('fats.show', $fat)
            ->with('success', 'FAT updated.');
    }

    public function destroy(Fat $fat): RedirectResponse
    {
        $this->authorize('delete', $fat);

        $this->fatService->delete($fat);

        return redirect()->route('fats.index');
    }

    public function activate(ActivateFatRequest $request, Fat $fat): RedirectResponse
    {
        $this->fatService->activate($fat);

        return redirect()->route('fats.show', $fat)
            ->with('success', 'FAT activated.');
    }

    public function maintenance(MaintenanceFatRequest $request, Fat $fat): RedirectResponse
    {
        $this->fatService->markMaintenance($fat);

        return redirect()->route('fats.show', $fat)
            ->with('success', 'FAT moved to maintenance.');
    }

    public function retire(RetireFatRequest $request, Fat $fat): RedirectResponse
    {
        $this->fatService->retire($fat);

        return redirect()->route('fats.show', $fat)
            ->with('success', 'FAT retired.');
    }
}
