<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscriptions table — Customer Management module.
 *
 * Full architecture-aligned implementation replacing Sprint 0 scaffold and
 * the intermediate stub migration (2026_07_02_000004).
 *
 * Reference: docs/database/entities.md — Subscription entity
 *            docs/workflows/subscription-lifecycle.md
 *            docs/architecture/decisions.md — Subscription Lifecycle Canonical States,
 *              Subscription Type Classification for v1.0,
 *              Subscription Suspension Data Model
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            // ----------------------------------------------------------------
            // Identity
            // ----------------------------------------------------------------
            $table->id();

            // ----------------------------------------------------------------
            // Foreign keys
            // ----------------------------------------------------------------
            $table->foreignId('customer_id')
                ->constrained('customers')->restrictOnDelete();

            $table->foreignId('package_id')
                ->constrained('packages')->restrictOnDelete();

            $table->foreignId('onu_id')->nullable()
                ->constrained('onus')->nullOnDelete()
                ->comment('Assigned ONT/ONU endpoint. Set on provisioning; cleared on termination.');

            // ----------------------------------------------------------------
            // Lifecycle
            // ----------------------------------------------------------------
            $table->enum('status', ['pending', 'active', 'suspended', 'reactivation_pending', 'terminated'])
                ->default('pending')
                ->index()
                ->comment('Canonical subscription status. pending = pre-activation (survey/install/provisioning). See SubscriptionStatus enum.');

            $table->enum('subscription_type', ['primary', 'addon'])
                ->default('primary')
                ->index()
                ->comment('Primary or addon. One active primary per customer enforced by application. No differential rules for addon in v1.0.');

            // ----------------------------------------------------------------
            // Suspension sub-type
            // ----------------------------------------------------------------
            $table->enum('suspension_type', ['overdue', 'manual'])->nullable()
                ->comment('NULL unless status = suspended. overdue = billing-triggered (auto-reactivatable). manual = operator-triggered (explicit override required).');

            $table->text('suspension_reason')->nullable()
                ->comment('Human-readable reason recorded on suspension.');

            // ----------------------------------------------------------------
            // Lifecycle timestamps
            // ----------------------------------------------------------------
            $table->timestamp('activated_at')->nullable()
                ->comment('When status first became active. Used as billing start reference.');

            $table->timestamp('suspended_at')->nullable()
                ->comment('When status last became suspended.');

            $table->timestamp('reactivation_requested_at')->nullable()
                ->comment('When reactivation_pending state was entered.');

            $table->timestamp('terminated_at')->nullable()
                ->comment('When status became terminated.');

            $table->text('terminated_reason')->nullable()
                ->comment('Human-readable reason for termination.');

            // ----------------------------------------------------------------
            // Billing
            // ----------------------------------------------------------------
            $table->unsignedTinyInteger('billing_day')->default(1)
                ->comment('Billing anchor day of the month (1-28).');

            // ----------------------------------------------------------------
            // Operational
            // ----------------------------------------------------------------
            $table->text('notes')->nullable()
                ->comment('Internal operational notes.');

            // ----------------------------------------------------------------
            // Timestamps & soft delete
            // ----------------------------------------------------------------
            $table->timestamps();
            $table->softDeletes();

            // ----------------------------------------------------------------
            // Indexes
            // ----------------------------------------------------------------
            $table->index(['customer_id', 'status']);
            $table->index(['customer_id', 'subscription_type', 'status'], 'sub_customer_type_status');
            $table->index('package_id');
            $table->index('onu_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
