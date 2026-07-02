<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service Areas table — Customer Management module.
 *
 * Stub migration providing the service_areas table as an FK dependency for
 * the customers table. Full ServiceArea module (lifecycle, CRUD, geo boundary,
 * area-based visibility) is a separate implementation story.
 *
 * Reference: docs/database/entities.md — ServiceArea entity
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cluster_id')->constrained('clusters')->restrictOnDelete();
            $table->string('name');
            $table->string('status', 30)->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('cluster_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};
