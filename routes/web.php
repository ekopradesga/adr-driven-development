<?php

use App\Http\Controllers\ClusterController;
use App\Http\Controllers\CollectionTaskController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FatController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OltController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentAllocationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceAreaController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (or login if unauthenticated)
Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // -------------------------------------------------------------------------
    // Profile
    // -------------------------------------------------------------------------
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // -------------------------------------------------------------------------
    // Billing Management
    // -------------------------------------------------------------------------
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/publish', [InvoiceController::class, 'publish'])->name('invoices.publish');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');

    Route::resource('payments', PaymentController::class);
    Route::post('payments/{payment}/receive', [PaymentController::class, 'receive'])->name('payments.receive');
    Route::post('payments/{payment}/validate', [PaymentController::class, 'validatePayment'])->name('payments.validate');
    Route::post('payments/{payment}/record', [PaymentController::class, 'record'])->name('payments.record');
    Route::post('payments/{payment}/allocate', [PaymentController::class, 'allocate'])->name('payments.allocate');
    Route::post('payments/{payment}/complete', [PaymentController::class, 'complete'])->name('payments.complete');
    Route::post('payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
    Route::post('payments/{payment}/fail', [PaymentController::class, 'fail'])->name('payments.fail');

    Route::prefix('payments/{payment}')->group(function () {
        Route::get('allocations', [PaymentAllocationController::class, 'index'])->name('payments.allocations.index');
        Route::get('allocations/{allocation}', [PaymentAllocationController::class, 'show'])->name('payments.allocations.show');
        Route::post('allocations/{allocation}/reverse', [PaymentAllocationController::class, 'reverse'])->name('payments.allocations.reverse');
        Route::post('allocations/{allocation}/reallocate', [PaymentAllocationController::class, 'reallocate'])->name('payments.allocations.reallocate');
    });

    // -------------------------------------------------------------------------
    // Subscription Management
    // -------------------------------------------------------------------------
    Route::resource('subscriptions', SubscriptionController::class);
    Route::resource('packages', PackageController::class);
    Route::resource('olts', OltController::class);
    Route::resource('fats', FatController::class);
    Route::post('packages/{package}/activate', [PackageController::class, 'activate'])->name('packages.activate');
    Route::post('packages/{package}/deprecate', [PackageController::class, 'deprecate'])->name('packages.deprecate');
    Route::post('packages/{package}/retire', [PackageController::class, 'retire'])->name('packages.retire');
    Route::post('olts/{olt}/activate', [OltController::class, 'activate'])->name('olts.activate');
    Route::post('olts/{olt}/maintenance', [OltController::class, 'maintenance'])->name('olts.maintenance');
    Route::post('olts/{olt}/retire', [OltController::class, 'retire'])->name('olts.retire');
    Route::post('fats/{fat}/activate', [FatController::class, 'activate'])->name('fats.activate');
    Route::post('fats/{fat}/maintenance', [FatController::class, 'maintenance'])->name('fats.maintenance');
    Route::post('fats/{fat}/retire', [FatController::class, 'retire'])->name('fats.retire');
    Route::post('subscriptions/{subscription}/activate',            [SubscriptionController::class, 'activate'])->name('subscriptions.activate');
    Route::post('subscriptions/{subscription}/suspend',             [SubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
    Route::post('subscriptions/{subscription}/request-reactivation',[SubscriptionController::class, 'requestReactivation'])->name('subscriptions.request-reactivation');
    Route::post('subscriptions/{subscription}/reactivate',          [SubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');
    Route::post('subscriptions/{subscription}/terminate',           [SubscriptionController::class, 'terminate'])->name('subscriptions.terminate');

    // -------------------------------------------------------------------------
    // Customer Management
    // -------------------------------------------------------------------------
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/suspend',    [CustomerController::class, 'suspend'])->name('customers.suspend');
    Route::post('customers/{customer}/reactivate', [CustomerController::class, 'reactivate'])->name('customers.reactivate');
    Route::post('customers/{customer}/terminate',  [CustomerController::class, 'terminate'])->name('customers.terminate');

    // -------------------------------------------------------------------------
    // Area and Assignment Management
    // -------------------------------------------------------------------------
    Route::resource('clusters', ClusterController::class);
    Route::post('clusters/{cluster}/activate', [ClusterController::class, 'activate'])->name('clusters.activate');
    Route::post('clusters/{cluster}/inactivate', [ClusterController::class, 'inactivate'])->name('clusters.inactivate');

    Route::resource('service-areas', ServiceAreaController::class)->parameters(['service-areas' => 'service_area']);
    Route::post('service-areas/{service_area}/activate', [ServiceAreaController::class, 'activate'])->name('service-areas.activate');
    Route::post('service-areas/{service_area}/merge', [ServiceAreaController::class, 'merge'])->name('service-areas.merge');
    Route::post('service-areas/{service_area}/archive', [ServiceAreaController::class, 'archive'])->name('service-areas.archive');
    Route::post('service-areas/{service_area}/employees', [ServiceAreaController::class, 'assignEmployee'])->name('service-areas.employees.assign');
    Route::delete('service-areas/{service_area}/employees/{employee}', [ServiceAreaController::class, 'removeEmployee'])->name('service-areas.employees.remove');

    // -------------------------------------------------------------------------
    // Collector / Field Collections
    // -------------------------------------------------------------------------
    Route::resource('employees', EmployeeController::class);

    Route::resource('collection-tasks', CollectionTaskController::class);
    Route::post('collection-tasks/{collection_task}/assign', [CollectionTaskController::class, 'assign'])->name('collection-tasks.assign');
    Route::post('collection-tasks/{collection_task}/schedule', [CollectionTaskController::class, 'schedule'])->name('collection-tasks.schedule');
    Route::post('collection-tasks/{collection_task}/start-route', [CollectionTaskController::class, 'startRoute'])->name('collection-tasks.start-route');
    Route::post('collection-tasks/{collection_task}/record-visit', [CollectionTaskController::class, 'recordVisit'])->name('collection-tasks.record-visit');
    Route::post('collection-tasks/{collection_task}/complete', [CollectionTaskController::class, 'complete'])->name('collection-tasks.complete');
    Route::post('collection-tasks/{collection_task}/follow-up-required', [CollectionTaskController::class, 'followUpRequired'])->name('collection-tasks.follow-up-required');
    Route::post('collection-tasks/{collection_task}/cancel', [CollectionTaskController::class, 'cancel'])->name('collection-tasks.cancel');
    Route::post('collection-tasks/{collection_task}/invoices', [CollectionTaskController::class, 'storeInvoice'])->name('collection-tasks.invoices.store');
    Route::post('collection-tasks/{collection_task}/invoices/{collection_task_invoice}/resolve', [CollectionTaskController::class, 'resolveInvoice'])->name('collection-tasks.invoices.resolve');

    // -------------------------------------------------------------------------
    // User Management
    // -------------------------------------------------------------------------
    Route::resource('users', UserController::class);
    Route::post('users/{user}/suspend',  [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');

    // -------------------------------------------------------------------------
    // Role Management
    // -------------------------------------------------------------------------
    Route::resource('roles', RoleController::class);

    // -------------------------------------------------------------------------
    // Permission Management (read-only — permissions are seeder-managed)
    // -------------------------------------------------------------------------
    Route::resource('permissions', PermissionController::class)->only(['index', 'show']);

    // -------------------------------------------------------------------------
    // Settings Management (registry-driven values only)
    // -------------------------------------------------------------------------
    Route::resource('settings', SettingController::class)->only(['index', 'edit', 'update']);
});

require __DIR__ . '/auth.php';
