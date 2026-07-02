<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the settings table.
 *
 * Stores actual configuration values keyed by registry entry.
 * Supports three scopes: global, area/cluster, and customer (entities.md — Settings).
 *
 * scope_id = 0 for global scope. Non-zero scope_id identifies the scoped entity.
 * Using a non-nullable default of 0 ensures the composite unique index works
 * correctly across all scopes in MySQL/MariaDB.
 *
 * Deletion behavior: Soft Delete for non-critical keys; Archive for governance history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registry_entry_id')->constrained('setting_registry_entries');
            $table->string('key', 150)->index();
            $table->text('value')->nullable();
            $table->string('scope', 20)->default('global')->index();
            $table->unsignedBigInteger('scope_id')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['key', 'scope', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
