<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 30)->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('status', 30)->default('intent_created')->index();
            $table->date('payment_date')->index();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('IDR');
            $table->string('method', 30)->index();
            $table->string('channel_reference', 100)->nullable()->index();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('customer_id');
            $table->index(['payment_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
