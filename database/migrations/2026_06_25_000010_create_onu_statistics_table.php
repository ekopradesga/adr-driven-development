<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onu_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onu_id')->constrained('onus')->restrictOnDelete();
            $table->timestamp('sampled_at')->index();
            $table->string('status', 30)->index();
            $table->decimal('rx_power_dbm', 8, 3)->nullable();
            $table->decimal('tx_power_dbm', 8, 3)->nullable();
            $table->decimal('temperature_c', 6, 2)->nullable();
            $table->decimal('voltage_v', 6, 2)->nullable();
            $table->decimal('cpu_usage_percent', 5, 2)->nullable();
            $table->decimal('memory_usage_percent', 5, 2)->nullable();
            $table->unsignedBigInteger('upstream_bps')->default(0);
            $table->unsignedBigInteger('downstream_bps')->default(0);
            $table->unsignedBigInteger('uptime_seconds')->default(0);
            $table->decimal('packet_loss_percent', 5, 2)->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->json('raw_payload')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['onu_id', 'sampled_at']);
            $table->index(['status', 'sampled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onu_statistics');
    }
};
