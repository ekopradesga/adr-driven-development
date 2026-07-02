<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code', 50)->unique();
            $table->string('name');
            $table->unsignedInteger('downstream_kbps');
            $table->unsignedInteger('upstream_kbps');
            $table->unsignedInteger('contention_ratio')->default(1);
            $table->decimal('monthly_price', 12, 2);
            $table->decimal('setup_fee', 12, 2)->default(0);
            $table->string('billing_cycle_type', 30)->default('monthly');
            $table->unsignedSmallInteger('billing_cycle_days')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index(['downstream_kbps', 'upstream_kbps']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
