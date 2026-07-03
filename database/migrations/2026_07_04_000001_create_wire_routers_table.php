<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routers', function (Blueprint $table) {
            $table->id();
            $table->string('router_code', 50)->unique();
            $table->string('name');
            $table->string('router_type', 30)->index();
            $table->string('vendor', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('ip_address', 45)->unique();
            $table->string('snmp_community')->nullable();
            $table->string('api_username')->nullable();
            $table->string('api_password')->nullable();
            $table->string('location_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->foreignId('parent_router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->string('status', 30)->default('planned')->index();
            $table->timestamp('last_seen_at')->nullable()->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index(['vendor', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};