<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
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
    Route::resource('invoices', InvoiceController::class)->except(['destroy']);
    Route::post('invoices/{invoice}/publish', [InvoiceController::class, 'publish'])->name('invoices.publish');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');

    // -------------------------------------------------------------------------
    // Subscription Management
    // -------------------------------------------------------------------------
    Route::resource('subscriptions', SubscriptionController::class);
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
