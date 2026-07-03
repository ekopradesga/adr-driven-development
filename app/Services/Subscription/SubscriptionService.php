<?php

namespace App\Services\Subscription;

use App\Domain\Events\CustomerConverted;
use App\Domain\Events\SubscriptionActivated;
use App\Domain\Events\SubscriptionCreated;
use App\Domain\Events\SubscriptionReactivated;
use App\Domain\Events\SubscriptionReactivationPending;
use App\Domain\Events\SubscriptionSuspended;
use App\Domain\Events\SubscriptionTerminated;
use App\Enums\CustomerStatus;
use App\Enums\PackageStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Subscription;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * SubscriptionService — Customer Management module.
 *
 * Owns all Subscription business logic, lifecycle transitions, and event dispatch.
 * Controllers delegate to this service and remain thin orchestrators.
 *
 * Reference: docs/workflows/subscription-lifecycle.md
 *            docs/architecture/decisions.md — Subscription Lifecycle Canonical States
 */
class SubscriptionService extends AbstractCrudService
{
    protected string $modelClass = Subscription::class;

    // -------------------------------------------------------------------------
    // CRUD Operations
    // -------------------------------------------------------------------------

    public function create(array $data): Subscription
    {
        return DB::transaction(function () use ($data) {
            $subscription = Subscription::create([
                'customer_id'       => $data['customer_id'],
                'package_id'        => $data['package_id'],
                'subscription_type' => $data['subscription_type'] ?? SubscriptionType::Primary->value,
                'billing_day'       => $data['billing_day'] ?? 1,
                'notes'             => $data['notes'] ?? null,
                'status'            => SubscriptionStatus::Pending->value,
            ]);

            event(new SubscriptionCreated(
                $subscription->id,
                $subscription->customer_id,
                auth()->id()
            ));

            return $subscription;
        });
    }

    /**
     * @param Subscription $subscription
     */
    public function update($subscription, array $data): Subscription
    {
        return DB::transaction(function () use ($subscription, $data) {
            $subscription->update([
                'package_id'        => $data['package_id'] ?? $subscription->package_id,
                'subscription_type' => $data['subscription_type'] ?? $subscription->subscription_type->value,
                'billing_day'       => $data['billing_day'] ?? $subscription->billing_day,
                'notes'             => $data['notes'] ?? $subscription->notes,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Soft-delete a Subscription.
     * Pre-condition: no published/overdue/paid Invoices; no Payment records.
     *
     * @param Subscription $subscription
     */
    public function delete($subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $this->assertDeletionAllowed($subscription);
            $subscription->delete();
        });
    }

    // -------------------------------------------------------------------------
    // Lifecycle Transitions
    // -------------------------------------------------------------------------

    /**
     * Activate a pending Subscription.
     *
     * Enforces the one-active-primary-subscription rule using lockForUpdate().
     * If the Customer is in Prospect status, transitions Customer to Active
     * and dispatches CustomerConverted (inline, no listener required for v1.0).
     *
     * Reference: docs/workflows/subscription-lifecycle.md — Pending → Active
     */
    public function activate(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            $subscription = Subscription::lockForUpdate()->findOrFail($subscription->id);

            if (!$subscription->isPending() && !$subscription->isReactivationPending()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot activate subscription. Current status: {$subscription->status->label()}. Expected: Pending or Reactivation Pending.",
                ]);
            }

            // Enforce one-active-primary per customer
            if ($subscription->isPrimary()) {
                $activeExists = Subscription::lockForUpdate()
                    ->where('customer_id', $subscription->customer_id)
                    ->where('subscription_type', SubscriptionType::Primary->value)
                    ->where('status', SubscriptionStatus::Active->value)
                    ->where('id', '!=', $subscription->id)
                    ->exists();

                if ($activeExists) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'Customer already has an active primary subscription.',
                    ]);
                }
            }

            $wasReactivation = $subscription->isReactivationPending();

            $subscription->update([
                'status'                    => SubscriptionStatus::Active->value,
                'activated_at'              => $subscription->activated_at ?? now(),
                'reactivation_requested_at' => null,
                'suspension_type'           => null,
                'suspension_reason'         => null,
                'suspended_at'              => null,
            ]);

            // Inline customer conversion for v1.0 (listener pattern deferred)
            $customer = Customer::lockForUpdate()->find($subscription->customer_id);
            if ($customer && $customer->isProspect()) {
                $customer->update(['status' => CustomerStatus::Active->value]);
                event(new CustomerConverted($customer->id, auth()->id()));
            }

            if ($wasReactivation) {
                event(new SubscriptionReactivated(
                    $subscription->id,
                    $subscription->customer_id,
                    auth()->id()
                ));
            } else {
                event(new SubscriptionActivated(
                    $subscription->id,
                    $subscription->customer_id,
                    auth()->id()
                ));
            }

            return $subscription->fresh();
        });
    }

    /**
     * Suspend an active Subscription.
     *
     * Reference: docs/architecture/decisions.md — Subscription Suspension Data Model
     */
    public function suspend(Subscription $subscription, string $type, string $reason): Subscription
    {
        return DB::transaction(function () use ($subscription, $type, $reason) {
            $subscription = Subscription::lockForUpdate()->findOrFail($subscription->id);

            if (!$subscription->isActive()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot suspend subscription. Current status: {$subscription->status->label()}. Expected: Active.",
                ]);
            }

            $subscription->update([
                'status'           => SubscriptionStatus::Suspended->value,
                'suspension_type'  => $type,
                'suspension_reason'=> $reason,
                'suspended_at'     => now(),
            ]);

            event(new SubscriptionSuspended(
                $subscription->id,
                $subscription->customer_id,
                $type,
                $reason,
                auth()->id()
            ));

            return $subscription->fresh();
        });
    }

    /**
     * Request reactivation of a suspended Subscription.
     * Transitions Suspended → Reactivation Pending.
     */
    public function requestReactivation(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            $subscription = Subscription::lockForUpdate()->findOrFail($subscription->id);

            if (!$subscription->isSuspended()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot request reactivation. Current status: {$subscription->status->label()}. Expected: Suspended.",
                ]);
            }

            $subscription->update([
                'status'                    => SubscriptionStatus::ReactivationPending->value,
                'reactivation_requested_at' => now(),
            ]);

            event(new SubscriptionReactivationPending(
                $subscription->id,
                $subscription->customer_id,
                auth()->id()
            ));

            return $subscription->fresh();
        });
    }

    /**
     * Fully reactivate a Subscription from Reactivation Pending → Active.
     * Delegates to activate() which handles all state enforcement.
     */
    public function reactivate(Subscription $subscription): Subscription
    {
        return $this->activate($subscription);
    }

    /**
     * Permanently terminate a Subscription.
     */
    public function terminate(Subscription $subscription, string $reason): Subscription
    {
        return DB::transaction(function () use ($subscription, $reason) {
            $subscription = Subscription::lockForUpdate()->findOrFail($subscription->id);

            if ($subscription->isTerminated()) {
                throw ValidationException::withMessages([
                    'status' => 'Subscription is already terminated.',
                ]);
            }

            // Pre-condition: no published/overdue invoices
            $openInvoices = $subscription->invoices()
                ->whereIn('status', ['published', 'overdue'])
                ->count();

            if ($openInvoices > 0) {
                throw ValidationException::withMessages([
                    'invoices' => "Cannot terminate subscription with {$openInvoices} open invoice(s). Settle or void all invoices first.",
                ]);
            }

            $subscription->update([
                'status'           => SubscriptionStatus::Terminated->value,
                'terminated_at'    => now(),
                'terminated_reason'=> $reason,
            ]);

            event(new SubscriptionTerminated(
                $subscription->id,
                $subscription->customer_id,
                $reason,
                auth()->id()
            ));

            return $subscription->fresh();
        });
    }

    // -------------------------------------------------------------------------
    // View Data Builders
    // -------------------------------------------------------------------------

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'subscriptions' => $this->paginate($filters, $perPage),
            'statuses'      => SubscriptionStatus::cases(),
            'filters'       => $filters,
        ];
    }

    public function buildCreateData(array $filters = []): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'customer_number', 'name']),
            'packages'  => Package::query()
                ->where('status', PackageStatus::Active->value)
                ->orderBy('name')
                ->get(['id', 'name', 'monthly_price', 'downstream_kbps', 'upstream_kbps']),
            'types'     => SubscriptionType::cases(),
            'prefill'   => $filters,
        ];
    }

    public function buildEditData(Subscription $subscription): array
    {
        $activePackages = Package::query()
            ->where('status', PackageStatus::Active->value)
            ->orderBy('name')
            ->get(['id', 'name', 'monthly_price', 'downstream_kbps', 'upstream_kbps']);

        $currentPackage = $subscription->package;
        if ($currentPackage && $activePackages->where('id', $currentPackage->id)->isEmpty()) {
            $activePackages->prepend($currentPackage);
        }

        return [
            'subscription' => $subscription->loadMissing(['customer', 'package']),
            'packages'     => $activePackages,
            'types'        => SubscriptionType::cases(),
            'statuses'     => SubscriptionStatus::cases(),
        ];
    }

    public function findForShow(Subscription $subscription): array
    {
        $subscription->loadMissing(['customer', 'package', 'invoices']);

        return [
            'subscription' => $subscription,
        ];
    }

    // -------------------------------------------------------------------------
    // AbstractCrudService Hooks
    // -------------------------------------------------------------------------

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->with(['customer', 'package']);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('customer', fn ($cq) =>
                    $cq->where('name', 'like', "%{$search}%")
                       ->orWhere('customer_number', 'like', "%{$search}%")
                );
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
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

    private function assertDeletionAllowed(Subscription $subscription): void
    {
        $errors = [];

        $openInvoices = $subscription->invoices()
            ->whereIn('status', ['published', 'overdue', 'paid'])
            ->count();

        if ($openInvoices > 0) {
            $errors['invoices'] = "Cannot delete subscription with {$openInvoices} published or paid invoice(s).";
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}
