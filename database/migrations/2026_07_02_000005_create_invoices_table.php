<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invoices table — Billing module.
 *
 * Full architecture-aligned implementation replacing the Sprint 0 stub.
 * Invoices are NEVER deleted (no softDeletes). Financial immutability is
 * enforced at the Service Layer; only InvoiceService may modify this table.
 *
 * Reference: docs/database/entities.md — Invoice entity
 *            docs/workflows/billing-workflow.md
 *            docs/architecture/decisions.md — Invoice Lifecycle Canonical States,
 *              Invoice Immutability Enforcement, Billing Period Data Model for v1.0
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            // ----------------------------------------------------------------
            // Identity
            // ----------------------------------------------------------------
            $table->id();

            $table->string('invoice_number', 25)->unique()
                ->comment('Format: INV-YYYYMM-000001. Immutable after creation.');

            // ----------------------------------------------------------------
            // Ownership (dual FK — intentional denormalization for Customer 360)
            // ----------------------------------------------------------------
            $table->foreignId('customer_id')
                ->constrained('customers')->restrictOnDelete();

            $table->foreignId('subscription_id')
                ->constrained('subscriptions')->restrictOnDelete();

            // ----------------------------------------------------------------
            // Lifecycle
            // ----------------------------------------------------------------
            $table->enum('status', ['draft', 'published', 'partially_paid', 'paid', 'overdue', 'cancelled'])
                ->default('draft')
                ->index()
                ->comment('See InvoiceStatus enum. draft = editable; published+ = immutable.');

            // ----------------------------------------------------------------
            // Billing period (v1.0: dates on invoice; BillingPeriod entity deferred to v1.1)
            // ----------------------------------------------------------------
            $table->date('period_start')
                ->comment('Start of the billing period this invoice covers.');

            $table->date('period_end')
                ->comment('End of the billing period this invoice covers.');

            $table->date('issue_date')
                ->comment('Date the invoice was generated.');

            $table->date('due_date')->index()
                ->comment('Payment due date.');

            // ----------------------------------------------------------------
            // Financial snapshot (immutable after publication)
            // ----------------------------------------------------------------
            $table->decimal('subtotal_amount', 12, 2)->default(0)
                ->comment('Sum of all invoice item totals.');

            $table->decimal('tax_amount', 12, 2)->default(0)
                ->comment('Tax applied at invoice level.');

            $table->decimal('discount_amount', 12, 2)->default(0)
                ->comment('Discount applied at invoice level.');

            $table->decimal('total_amount', 12, 2)->default(0)
                ->comment('subtotal + tax - discount. The payable amount.');

            // ----------------------------------------------------------------
            // Payment tracking (updated by Payment Workflow only)
            // ----------------------------------------------------------------
            $table->decimal('paid_amount', 12, 2)->default(0)
                ->comment('Total allocated from payments. Updated by InvoiceService::recordPaymentAllocation() only.');

            $table->decimal('balance_amount', 12, 2)->default(0)
                ->comment('total - paid. Recomputed on each payment allocation.');

            // ----------------------------------------------------------------
            // Lifecycle timestamps
            // ----------------------------------------------------------------
            $table->timestamp('published_at')->nullable()
                ->comment('When status became published. Set once; immutable thereafter.');

            $table->timestamp('overdue_at')->nullable()
                ->comment('When billing policy detected overdue condition.');

            $table->timestamp('cancelled_at')->nullable()
                ->comment('When status became cancelled (draft only).');

            $table->text('cancellation_reason')->nullable()
                ->comment('Required when cancelled.');

            // ----------------------------------------------------------------
            // Operational
            // ----------------------------------------------------------------
            $table->text('notes')->nullable()
                ->comment('Internal notes.');

            // ----------------------------------------------------------------
            // Timestamps only — NO softDeletes (Invoices are never deleted)
            // ----------------------------------------------------------------
            $table->timestamps();

            // ----------------------------------------------------------------
            // Indexes
            // ----------------------------------------------------------------
            $table->index(['customer_id', 'status']);
            $table->index(['subscription_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['period_start', 'period_end']);

            // Enforce one invoice per subscription per billing period
            $table->unique(['subscription_id', 'period_start', 'period_end'], 'inv_sub_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
