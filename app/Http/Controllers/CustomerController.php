<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\SuspendCustomerRequest;
use App\Http\Requests\Customer\TerminateCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CustomerController — Customer Management module.
 *
 * Orchestration only. Zero business logic.
 * Delegates all business operations to CustomerService.
 *
 * Per Service-First Application Layer decision:
 * - Authorize via Policy ($this->authorize)
 * - Validate via FormRequest
 * - Invoke CustomerService
 * - Return response
 */
class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        return view('customers.index', $this->customerService->buildIndexData(
            $request->only('search', 'status', 'customer_type')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('customers.create', $this->customerService->buildCreateData());
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = $this->customerService->create($request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', "Customer {$customer->customer_number} created successfully.");
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        return view('customers.show', $this->customerService->findForShow($customer));
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('customers.edit', $this->customerService->buildEditData($customer));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->update($customer, $request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer profile updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $this->customerService->delete($customer);

        return redirect()->route('customers.index')
            ->with('success', "Customer {$customer->customer_number} deleted.");
    }

    // -------------------------------------------------------------------------
    // Lifecycle Actions
    // -------------------------------------------------------------------------

    /**
     * Suspend a Customer account (administrative action only).
     * Requires a documented reason.
     */
    public function suspend(SuspendCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->suspend($customer, $request->validated('reason'));

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer account suspended.');
    }

    /**
     * Reactivate a suspended Customer account.
     */
    public function reactivate(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('reactivate', $customer);

        $this->customerService->reactivate($customer);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer account reactivated.');
    }

    /**
     * Permanently terminate a Customer account.
     * Requires all Subscriptions to be terminated.
     */
    public function terminate(TerminateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->terminate($customer, $request->validated('reason'));

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer account terminated.');
    }
}
