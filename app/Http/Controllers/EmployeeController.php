<?php

namespace App\Http\Controllers;

use App\Http\Requests\Collector\StoreEmployeeRequest;
use App\Http\Requests\Collector\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\Collector\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        return view('employees.index', $this->employeeService->buildIndexData(
            $request->only('search', 'status')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);

        return view('employees.create', $this->employeeService->buildCreateData());
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = $this->employeeService->create($request->validated());

        return redirect()->route('employees.show', $employee)
            ->with('success', "Employee {$employee->name} created.");
    }

    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);

        return view('employees.show', $this->employeeService->findForShow($employee));
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        return view('employees.edit', $this->employeeService->buildEditData($employee));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->employeeService->update($employee, $request->validated());

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $this->employeeService->delete($employee);

        return redirect()->route('employees.index');
    }
}