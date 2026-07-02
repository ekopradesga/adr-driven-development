<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olts')->restrictOnDelete();
            $table->string('onu_sn', 100)->unique();
            $table->unsignedInteger('onu_index');
            $table->string('pon_port', 50);
            $table->string('model', 100)->nullable();
            $table->string('customer_label')->nullable();
            $table->string('status', 30)->default('unknown')->index();
            $table->decimal('rx_power_dbm', 8, 3)->nullable();
            $table->decimal('tx_power_dbm', 8, 3)->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('provisioned_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['olt_id', 'pon_port', 'onu_index']);
            $table->index('customer_label');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onus');
    }
};
