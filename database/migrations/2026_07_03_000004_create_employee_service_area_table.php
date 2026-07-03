<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_service_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('service_area_id')->constrained('service_areas')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'service_area_id']);
            $table->index(['employee_id', 'is_primary']);
            $table->index('service_area_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_service_area');
    }
};