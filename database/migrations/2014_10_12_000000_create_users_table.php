<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the users table.
 *
 * User is an Aggregate Root of the Identity & Access domain (entities.md).
 *
 * Lifecycle: Active → Suspended → Inactive (entities.md — Identity & Access).
 * Operational status values:
 *   - active    (default): user can authenticate and perform operations.
 *   - suspended:           temporarily blocked; reversible by an administrator.
 *   - inactive:            decommissioned; account no longer operational.
 *
 * Deletion behavior: Soft Delete with Restrict when referenced by financial
 * or audit records (erd.md — Identity & Access).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status', 20)->default('active')->index();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

