<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->restrictOnDelete();
            $table->string('status', 30)->default('waiting_assignment')->index();
            $table->timestamp('scheduled_for')->nullable()->index();
            $table->timestamp('route_started_at')->nullable();
            $table->timestamp('visited_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('follow_up_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->decimal('payment_collected_amount', 12, 2)->nullable();
            $table->string('payment_submission_reference', 100)->nullable()->index();
            $table->string('payment_submission_status', 20)->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('employee_id');
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_tasks');
    }
};