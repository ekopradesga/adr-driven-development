<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the role_user pivot table.
 *
 * Implements the User N <-> N Role relationship defined in the ERD
 * (erd.md — Identity & Access).
 *
 * Design decisions:
 *
 *   No surrogate id — This is a pure pivot table. A composite primary key on
 *   (role_id, user_id) enforces uniqueness at the DB level without a
 *   redundant surrogate key + separate unique constraint.
 *
 *   role_id → restrictOnDelete():
 *     erd.md states "Role uses Restrict when assigned."
 *     The DB must prevent deletion of a role that has active assignments.
 *     Application-layer checks (RoleService) provide the first line of
 *     defence; restrictOnDelete() provides DB-level enforcement.
 *
 *   user_id → cascadeOnDelete():
 *     Users are always soft-deleted in normal operation; this FK is never
 *     triggered by $user->delete(). It fires only on forceDelete(), at which
 *     point cleaning up role assignments via cascade is correct.
 *     The architecture's "Restrict" for User applies only to financial or
 *     audit records (erd.md) — role assignments are neither.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['role_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};

