<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE onus MODIFY status VARCHAR(30) NOT NULL DEFAULT 'unprovisioned'");

        DB::table('onus')
            ->where('status', 'unknown')
            ->update(['status' => 'unprovisioned']);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE onus MODIFY status VARCHAR(30) NOT NULL DEFAULT 'unknown'");

        DB::table('onus')
            ->where('status', 'unprovisioned')
            ->update(['status' => 'unknown']);
    }
};