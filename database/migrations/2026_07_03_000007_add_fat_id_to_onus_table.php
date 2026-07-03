<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onus', function (Blueprint $table) {
            $table->foreignId('fat_id')->nullable()->after('olt_id')->constrained('fats')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('onus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fat_id');
        });
    }
};
