<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the setting_registry_entries table.
 *
 * Defines the approved settings schema: allowed keys, data types, default values,
 * and validation rules. Only registered entries may have Setting values created.
 * This prevents configuration sprawl (entities.md — Settings).
 *
 * Deletion behavior: Restrict when in-use settings exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_registry_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('setting_categories');
            $table->string('key', 150)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('data_type', 20)->default('string');
            $table->text('default_value')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_registry_entries');
    }
};
