<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class       => \App\Policies\UserPolicy::class,
        \App\Models\Role::class       => \App\Policies\RolePolicy::class,
        \App\Models\Permission::class => \App\Policies\PermissionPolicy::class,
        \App\Models\Setting::class    => \App\Policies\SettingPolicy::class,
        \App\Models\Customer::class     => \App\Policies\CustomerPolicy::class,
        \App\Models\Subscription::class  => \App\Policies\SubscriptionPolicy::class,
        \App\Models\Invoice::class       => \App\Policies\InvoicePolicy::class,
        \App\Models\Payment::class       => \App\Policies\PaymentPolicy::class,
        \App\Models\PaymentAllocation::class => \App\Policies\PaymentAllocationPolicy::class,
        \App\Models\Employee::class      => \App\Policies\EmployeePolicy::class,
        \App\Models\CollectionTask::class => \App\Policies\CollectionTaskPolicy::class,
        \App\Models\CollectionTaskInvoice::class => \App\Policies\CollectionTaskInvoicePolicy::class,
        \App\Models\Cluster::class       => \App\Policies\ClusterPolicy::class,
        \App\Models\ServiceArea::class   => \App\Policies\ServiceAreaPolicy::class,
        \App\Models\Package::class       => \App\Policies\PackagePolicy::class,
    ];

    public function boot(): void
    {
        // Super-admin gate bypass — applies globally before any policy check.
        // Individual policies also implement before() for defence-in-depth.
        \Illuminate\Support\Facades\Gate::before(function (\App\Models\User $user, string $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });
    }
}
