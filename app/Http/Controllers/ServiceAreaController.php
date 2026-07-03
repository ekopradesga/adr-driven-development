<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceArea\ActivateServiceAreaRequest;
use App\Http\Requests\ServiceArea\ArchiveServiceAreaRequest;
use App\Http\Requests\ServiceArea\AssignEmployeeToServiceAreaRequest;
use App\Http\Requests\ServiceArea\MergeServiceAreaRequest;
use App\Http\Requests\ServiceArea\RemoveEmployeeFromServiceAreaRequest;
use App\Http\Requests\ServiceArea\StoreServiceAreaRequest;
use App\Http\Requests\ServiceArea\UpdateServiceAreaRequest;
use App\Models\Employee;
use App\Models\ServiceArea;
use App\Services\ServiceArea\ServiceAreaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceAreaController extends Controller
{
    public function __construct(
        private readonly ServiceAreaService $serviceAreaService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceArea::class);

        return view('service_areas.index', $this->serviceAreaService->buildIndexData(
            $request->only('search', 'status', 'cluster_id', 'level')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', ServiceArea::class);

        return view('service_areas.create', $this->serviceAreaService->buildCreateData());
    }

    public function store(StoreServiceAreaRequest $request): RedirectResponse
    {
        $serviceArea = $this->serviceAreaService->create($request->validated());

        return redirect()->route('service-areas.show', $serviceArea)
            ->with('success', 'Service area created.');
    }

    public function show(ServiceArea $serviceArea): View
    {
        $this->authorize('view', $serviceArea);

        return view('service_areas.show', $this->serviceAreaService->findForShow($serviceArea));
    }

    public function edit(ServiceArea $serviceArea): View
    {
        $this->authorize('update', $serviceArea);

        return view('service_areas.edit', $this->serviceAreaService->buildEditData($serviceArea));
    }

    public function update(UpdateServiceAreaRequest $request, ServiceArea $serviceArea): RedirectResponse
    {
        $this->serviceAreaService->update($serviceArea, $request->validated());

        return redirect()->route('service-areas.show', $serviceArea)
            ->with('success', 'Service area updated.');
    }

    public function destroy(ServiceArea $serviceArea): RedirectResponse
    {
        $this->authorize('delete', $serviceArea);

        $this->serviceAreaService->delete($serviceArea);

        return redirect()->route('service-areas.index');
    }

    public function activate(ActivateServiceAreaRequest $request, ServiceArea $serviceArea): RedirectResponse
    {
        $this->serviceAreaService->activate($serviceArea);

        return redirect()->route('service-areas.show', $serviceArea)
            ->with('success', 'Service area activated.');
    }

    public function merge(MergeServiceAreaRequest $request, ServiceArea $serviceArea): RedirectResponse
    {
        $this->serviceAreaService->merge($serviceArea, (int) $request->validated('merged_into_service_area_id'));

        return redirect()->route('service-areas.show', $serviceArea)
            ->with('success', 'Service area merged.');
    }

    public function archive(ArchiveServiceAreaRequest $request, ServiceArea $serviceArea): RedirectResponse
    {
        $this->serviceAreaService->archive($serviceArea);

        return redirect()->route('service-areas.show', $serviceArea)
            ->with('success', 'Service area archived.');
    }

    public function assignEmployee(AssignEmployeeToServiceAreaRequest $request, ServiceArea $serviceArea): RedirectResponse
    {
        $this->serviceAreaService->assignEmployee(
            $serviceArea,
            (int) $request->validated('employee_id'),
            (bool) $request->validated('is_primary', false)
        );

        return redirect()->route('service-areas.show', $serviceArea)
            ->with('success', 'Employee assigned to service area.');
    }

    public function removeEmployee(RemoveEmployeeFromServiceAreaRequest $request, ServiceArea $serviceArea, Employee $employee): RedirectResponse
    {
        $this->serviceAreaService->removeEmployee($serviceArea, $employee->id);

        return redirect()->route('service-areas.show', $serviceArea)
            ->with('success', 'Employee removed from service area.');
    }
}