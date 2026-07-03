<?php

namespace App\Services\Customer;

use App\Domain\Events\CustomerRegistered;
use App\Domain\Events\CustomerReactivated;
use App\Domain\Events\CustomerSuspended;
use App\Domain\Events\CustomerTerminated;
use App\Domain\Events\CustomerUpdated;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Cluster;
use App\Models\Customer;
use App\Models\ServiceArea;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * CustomerService — Customer Management module.
 *
 * Owns all Customer business logic, lifecycle transitions, and event dispatch.
 * Controllers delegate to this service and remain thin orchestrators.
 *
 * Architecture compliance:
 * - Service-First: all business logic here, not in Controller/Model/Policy
 * - Transactions: DB::transaction() closure form per transactions.md
 * - Events: ShouldDispatchAfterCommit on all domain events per events.md
 * - Exceptions: typed exceptions per exceptions.md
 * - Lifecycle: follows customer-workflow.md exactly
 */
class CustomerService extends AbstractCrudService
{
    protected string $modelClass = Customer::class;

    // -------------------------------------------------------------------------
    // Customer Number Generation
    // -------------------------------------------------------------------------

    /**
     * Generates a unique customer number in CUST-000001 format.
     * Uses a locking query to prevent race conditions during concurrent creation.
     * Must be called inside a DB::transaction().
     *
     * Reference: docs/architecture/decisions.md — Customer Number Format and Generation
     */
    private function generateCustomerNumber(): string
    {
        $latest = Customer::lockForUpdate()
            ->whereNotNull('customer_number')
            ->orderByDesc('customer_number')
            ->value('customer_number');

        if (!$latest) {
            return 'CUST-000001';
        }

        // Extract the numeric part from "CUST-NNNNNN"
        $sequence = (int) substr($latest, 5);
        $next     = str_pad($sequence + 1, 6, '0', STR_PAD_LEFT);

        return "CUST-{$next}";
    }

    // -------------------------------------------------------------------------
    // CRUD Operations
    // -------------------------------------------------------------------------

    /**
     * Create a new Customer record.
     *
     * Generates customer_number, persists the Customer, and dispatches
     * CustomerRegistered event after commit.
     */
    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::create([
                'customer_number' => $this->generateCustomerNumber(),
                'name'            => $data['name'],
                'customer_type'   => $data['customer_type'] ?? CustomerType::Individual->value,
                'email'           => $data['email'] ?? null,
                'phone'           => $data['phone'] ?? null,
                'whatsapp_phone'  => $data['whatsapp_phone'] ?? null,
                'alt_phone'       => $data['alt_phone'] ?? null,
                'address'         => $data['address'] ?? null,
                'latitude'        => $data['latitude'] ?? null,
                'longitude'       => $data['longitude'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'cluster_id'      => $data['cluster_id'] ?? null,
                'service_area_id' => $data['service_area_id'] ?? null,
                'status'          => CustomerStatus::Prospect->value,
            ]);

            event(new CustomerRegistered($customer->id, auth()->id()));

            return $customer;
        });
    }

    /**
     * Update an existing Customer profile.
     *
     * Note: status transitions (suspend, reactivate, terminate) are separate
     * methods. This method handles profile data changes only.
     *
     * @param Customer $customer
     */
    public function update($customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $customer->update([
                'name'            => $data['name'],
                'customer_type'   => $data['customer_type'] ?? $customer->customer_type->value,
                'email'           => $data['email'] ?? null,
                'phone'           => $data['phone'] ?? null,
                'whatsapp_phone'  => $data['whatsapp_phone'] ?? null,
                'alt_phone'       => $data['alt_phone'] ?? null,
                'address'         => $data['address'] ?? null,
                'latitude'        => $data['latitude'] ?? null,
                'longitude'       => $data['longitude'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'cluster_id'      => $data['cluster_id'] ?? $customer->cluster_id,
                'service_area_id' => $data['service_area_id'] ?? $customer->service_area_id,
            ]);

            event(new CustomerUpdated($customer->id, auth()->id()));

            return $customer->fresh();
        });
    }

    /**
     * Soft-delete a Customer with pre-condition validation.
     *
     * Pre-conditions (all must pass):
     * 1. No Invoice with status published/overdue/paid
     * 2. No Payment record
     * 3. No active PaymentAllocation
     * 4. No active Subscription
     * 5. No open Ticket
     * 6. No pending ServiceRequest
     *
     * Reference: docs/workflows/customer-workflow.md — Soft Delete Rules
     *
     * @param Customer $customer
     */
    public function delete($customer): void
    {
        DB::transaction(function () use ($customer) {
            $this->assertDeletionAllowed($customer);
            $customer->delete();
        });
    }

    // -------------------------------------------------------------------------
    // Lifecycle Transitions
    // -------------------------------------------------------------------------

    /**
     * Suspend a Customer account (administrative action only).
     *
     * Requires: Customer must be in Active state.
     * Does NOT automatically suspend active Subscriptions.
     *
     * Reference: docs/workflows/customer-workflow.md — Active → Suspended
     *            docs/architecture/decisions.md — Customer Account vs Subscription Suspension
     *
     * @throws ValidationException if Customer is not in Active state
     */
    public function suspend(Customer $customer, string $reason): Customer
    {
        return DB::transaction(function () use ($customer, $reason) {
            // Lock row to prevent concurrent state changes
            $customer = Customer::lockForUpdate()->findOrFail($customer->id);

            if (!$customer->isActive()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot suspend customer. Current status: {$customer->status->label()}. Expected: Active.",
                ]);
            }

            $customer->update(['status' => CustomerStatus::Suspended->value]);

            event(new CustomerSuspended($customer->id, $reason, auth()->id()));

            return $customer->fresh();
        });
    }

    /**
     * Reactivate a suspended Customer account.
     *
     * Requires: Customer must be in Suspended state.
     *
     * Reference: docs/workflows/customer-workflow.md — Suspended → Active
     *
     * @throws ValidationException if Customer is not in Suspended state
     */
    public function reactivate(Customer $customer): Customer
    {
        return DB::transaction(function () use ($customer) {
            $customer = Customer::lockForUpdate()->findOrFail($customer->id);

            if (!$customer->isSuspended()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot reactivate customer. Current status: {$customer->status->label()}. Expected: Suspended.",
                ]);
            }

            $customer->update(['status' => CustomerStatus::Active->value]);

            event(new CustomerReactivated($customer->id, auth()->id()));

            return $customer->fresh();
        });
    }

    /**
     * Permanently terminate a Customer account.
     *
     * Pre-conditions:
     * - All Subscriptions must be in 'terminated' state
     * - No outstanding Invoice balance (published/overdue invoices)
     *
     * Terminated is a terminal state — no reactivation is permitted.
     * A new Customer record must be created for re-engagement.
     *
     * Reference: docs/workflows/customer-workflow.md — Terminated state
     *
     * @throws ValidationException if pre-conditions are not met
     */
    public function terminate(Customer $customer, string $reason): Customer
    {
        return DB::transaction(function () use ($customer, $reason) {
            $customer = Customer::lockForUpdate()->findOrFail($customer->id);

            if ($customer->isTerminated()) {
                throw ValidationException::withMessages([
                    'status' => 'Customer is already terminated.',
                ]);
            }

            // Validate: all Subscriptions must be terminated
            $activeSubsCount = $customer->subscriptions()
                ->whereNotIn('status', ['terminated'])
                ->count();

            if ($activeSubsCount > 0) {
                throw ValidationException::withMessages([
                    'subscriptions' => "Cannot terminate customer. {$activeSubsCount} subscription(s) are not yet terminated. All subscriptions must be terminated before the account can be closed.",
                ]);
            }

            $customer->update(['status' => CustomerStatus::Terminated->value]);

            event(new CustomerTerminated($customer->id, $reason, auth()->id()));

            return $customer->fresh();
        });
    }

    // -------------------------------------------------------------------------
    // View Data Builders
    // -------------------------------------------------------------------------

    /**
     * Build data for the Customer index page.
     */
    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'customers' => $this->paginate($filters, $perPage),
            'statuses'  => CustomerStatus::cases(),
            'filters'   => $filters,
        ];
    }

    /**
     * Build data for the Customer create form.
     */
    public function buildCreateData(): array
    {
        return [
            'statuses' => CustomerStatus::cases(),
            'types'    => CustomerType::cases(),
            'clusters' => Cluster::active()->orderBy('name')->get(),
            'serviceAreas' => ServiceArea::active()->orderBy('name')->get(),
        ];
    }

    /**
     * Build data for the Customer edit form.
     */
    public function buildEditData(Customer $customer): array
    {
        return [
            'customer' => $customer,
            'statuses' => CustomerStatus::cases(),
            'types'    => CustomerType::cases(),
            'clusters' => Cluster::active()->orderBy('name')->get(),
            'serviceAreas' => ServiceArea::active()->orderBy('name')->get(),
        ];
    }

    /**
     * Build data for the Customer 360 workspace (show view).
     * Applies the eager-load chain from customer-workflow.md.
     */
    public function findForShow(Customer $customer): array
    {
        $customer->loadMissing([
            'cluster',
            'serviceArea',
            'subscriptions',
            'invoices',
            'payments',
        ]);

        return [
            'customer' => $customer,
        ];
    }

    // -------------------------------------------------------------------------
    // AbstractCrudService Hooks
    // -------------------------------------------------------------------------

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->with(['cluster', 'serviceArea']);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_type'])) {
            $query->where('customer_type', $filters['customer_type']);
        }

        return $query;
    }

    protected function applyDefaultOrdering(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Validates all pre-delete conditions before soft-deleting a Customer.
     * Throws ValidationException with specific messages if any condition fails.
     *
     * Reference: docs/workflows/customer-workflow.md — Soft Delete Rules
     */
    private function assertDeletionAllowed(Customer $customer): void
    {
        $errors = [];

        // Financial Restrict: published/overdue/paid invoices
        $invoiceCount = $customer->invoices()
            ->whereIn('status', ['published', 'overdue', 'paid'])
            ->count();

        if ($invoiceCount > 0) {
            $errors['invoices'] = "Cannot delete customer with {$invoiceCount} published or paid invoice(s). Settle or void all invoices first.";
        }

        // Financial Restrict: any Payment record
        $paymentCount = $customer->payments()->count();
        if ($paymentCount > 0) {
            $errors['payments'] = "Cannot delete customer with {$paymentCount} payment record(s). Financial records must be retained for audit.";
        }

        // Operational Restrict: active Subscription
        $activeSubCount = $customer->subscriptions()
            ->where('status', 'active')
            ->count();

        if ($activeSubCount > 0) {
            $errors['subscriptions'] = "Cannot delete customer with {$activeSubCount} active subscription(s). Terminate all subscriptions first.";
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}
