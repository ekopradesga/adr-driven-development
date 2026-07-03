<?php

namespace App\Jobs\Provisioning;

use App\Enums\OnuStatus;
use App\Models\Onu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProvisionOnuJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 300];

    public string $queue = 'default-provisioning';

    public function __construct(
        public readonly int $onuId,
        public readonly ?int $subscriptionId = null,
        public readonly ?int $actorId = null,
    ) {
        $this->queue = (string) config('queue.provisioning.queue', 'default-provisioning');
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $onu = Onu::lockForUpdate()->findOrFail($this->onuId);

            if ($onu->isRetired()) {
                return;
            }

            if ($onu->isActive()) {
                return;
            }

            $onu->update([
                'status' => OnuStatus::Active->value,
                'provisioned_at' => $onu->provisioned_at ?? now(),
                'updated_by' => $this->actorId,
            ]);
        });
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ONU provisioning job failed', [
            'onu_id' => $this->onuId,
            'subscription_id' => $this->subscriptionId,
            'actor_id' => $this->actorId,
            'error' => $exception->getMessage(),
        ]);
    }
}