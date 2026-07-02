<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the setting_categories table.
 *
 * Groups settings by operational domain for governance and discoverability.
 * Examples: billing, notification, monitoring, security, collections.
 *
 * Deletion behavior: Restrict when category has active settings or registry entries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('label', 150);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_categories');
    }
};
