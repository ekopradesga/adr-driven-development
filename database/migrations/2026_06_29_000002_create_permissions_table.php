<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the permissions table.
 *
 * Permissions define granular system capabilities using a dot-notation key
 * convention (e.g. customer.view, billing.publish).
 *
 * Lifecycle: Draft → Active → Deprecated (entities.md — Identity & Access).
 * Deletion behavior: Restrict when bound to roles or direct user assignments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 150)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable()->index();
            $table->string('status', 20)->default('draft')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
