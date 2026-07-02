<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the roles table.
 *
 * Roles group permissions into reusable authorization sets and are
 * assigned to users through the role_user pivot table.
 *
 * Lifecycle: Draft → Active → Deprecated (entities.md — Identity & Access).
 * Deletion behavior: Restrict when assigned to users; Soft Delete otherwise (erd.md).
 *
 * Columns:
 *   slug — Not listed as a key attribute in entities.md but retained as a stable
 *           machine-readable identifier for programmatic role lookups
 *           (e.g. hasRole('super-admin')). Matching by human-readable name is
 *           fragile; slug provides a stable, developer-facing handle.
 *           This is an implementation convention, not a new business rule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

