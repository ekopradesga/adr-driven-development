<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invoice items table — Billing module.
 *
 * InvoiceItems are immutable after their parent Invoice is published.
 * Hard delete is permitted only while the Invoice is in Draft status.
 * No softDeletes.
 *
 * Reference: docs/database/entities.md — InvoiceItem entity
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')->restrictOnDelete();

            $table->string('description')
                ->comment('Line item description. Snapshot at creation time.');

            $table->string('item_type', 30)->default('subscription')
                ->comment('subscription, installation, addon, discount, adjustment.');

            $table->unsignedInteger('quantity')->default(1);

            $table->decimal('unit_price', 12, 2)
                ->comment('Price snapshot at billing time. Never changes.');

            $table->decimal('total_amount', 12, 2)
                ->comment('quantity × unit_price.');

            $table->unsignedInteger('sort_order')->default(0)
                ->comment('Display ordering.');

            $table->text('notes')->nullable();

            // No softDeletes — InvoiceItems are hard-deleted only while Invoice is Draft
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
