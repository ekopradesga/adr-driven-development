<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clusters table — Customer Management module.
 *
 * Stub migration providing the clusters table as an FK dependency for the
 * customers table. Full Cluster module (lifecycle, CRUD, reporting, area
 * assignment) is a separate implementation story.
 *
 * Reference: docs/database/entities.md — Cluster entity
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clusters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status', 30)->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clusters');
    }
};
