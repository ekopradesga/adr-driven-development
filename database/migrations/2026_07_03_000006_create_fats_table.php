<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odf_id')->constrained('odfs')->restrictOnDelete();
            $table->foreignId('service_area_id')->nullable()->constrained('service_areas')->nullOnDelete();
            $table->string('fat_code', 50)->unique();
            $table->string('name');
            $table->unsignedInteger('capacity_ports');
            $table->unsignedInteger('used_ports')->default(0);
            $table->string('splitter_ratio', 30)->nullable();
            $table->string('location_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->string('status', 30)->default('planned')->index();
            $table->timestamp('last_onu_ping_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fats');
    }
};
