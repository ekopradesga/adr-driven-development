<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cluster_id')->constrained('clusters')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('service_areas')->restrictOnDelete();
            $table->foreignId('merged_into_service_area_id')->nullable()->constrained('service_areas')->restrictOnDelete();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('level', 30)->default('area')->index();
            $table->longText('boundary_geojson')->nullable();
            $table->decimal('center_latitude', 10, 7)->nullable();
            $table->decimal('center_longitude', 11, 7)->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('merged_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('cluster_id');
            $table->index('parent_id');
            $table->index('merged_into_service_area_id');
            $table->index(['cluster_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};
