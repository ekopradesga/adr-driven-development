<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_task_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_task_id')->constrained('collection_tasks')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->string('status', 20)->default('created')->index();
            $table->text('inclusion_reason')->nullable();
            $table->string('resolution_outcome', 100)->nullable()->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('collection_task_id');
            $table->index('invoice_id');
            $table->unique(['collection_task_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_task_invoices');
    }
};