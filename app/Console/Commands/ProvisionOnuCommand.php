<?php

namespace App\Console\Commands;

use App\Jobs\Provisioning\ProvisionOnuJob;
use App\Models\Onu;
use App\Models\Subscription;
use Illuminate\Console\Command;
use InvalidArgumentException;

class ProvisionOnuCommand extends Command
{
    protected $signature = 'provision:onu
                            {--subscription-id= : Subscription ID that owns the target ONU}
                            {--onu-id= : ONU ID to provision directly}
                            {--actor-id= : Optional actor ID for audit attribution}
                            {--force : Allow requeue for non-active subscriptions and already-active ONUs}';

    protected $description = 'Queue ONU provisioning by subscription or direct ONU target';

    public function handle(): int
    {
        try {
            $subscriptionId = $this->readPositiveIntOption('subscription-id');
            $onuId = $this->readPositiveIntOption('onu-id');
            $actorId = $this->readPositiveIntOption('actor-id');
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $subscription = null;

        if ($subscriptionId === null && $onuId === null) {
            $this->error('Provide --subscription-id or --onu-id.');

            return self::FAILURE;
        }

        if ($subscriptionId !== null) {
            $subscription = Subscription::query()->find($subscriptionId);

            if ($subscription === null) {
                $this->error("Subscription #{$subscriptionId} was not found.");

                return self::FAILURE;
            }

            if ($subscription->onu_id === null) {
                $this->error("Subscription #{$subscription->id} has no assigned ONU.");

                return self::FAILURE;
            }

            if (!$force && !$subscription->isActive()) {
                $this->error('Subscription must be active to queue provisioning. Use --force to override.');

                return self::FAILURE;
            }

            $resolvedOnuId = (int) $subscription->onu_id;

            if ($onuId !== null && $onuId !== $resolvedOnuId) {
                $this->error("Provided --onu-id ({$onuId}) does not match subscription ONU ({$resolvedOnuId}).");

                return self::FAILURE;
            }
        } else {
            $resolvedOnuId = $onuId;
        }

        $onu = Onu::query()->find($resolvedOnuId);

        if ($onu === null) {
            $this->error("ONU #{$resolvedOnuId} was not found.");

            return self::FAILURE;
        }

        if (!$force && $onu->isRetired()) {
            $this->error('Cannot queue provisioning for retired ONU. Use --force to override.');

            return self::FAILURE;
        }

        if (!$force && $onu->isActive()) {
            $this->warn("ONU #{$onu->id} is already active; no provisioning job queued. Use --force to requeue.");

            return self::SUCCESS;
        }

        $queueName = (string) config('queue.provisioning.queue', 'default-provisioning');

        ProvisionOnuJob::dispatch(
            $onu->id,
            $subscription?->id,
            $actorId,
        )->onQueue($queueName);

        $this->info(
            "Provisioning job queued on [{$queueName}] for ONU #{$onu->id}".
            ($subscription ? " via subscription #{$subscription->id}." : '.')
        );

        return self::SUCCESS;
    }

    private function readPositiveIntOption(string $name): ?int
    {
        $value = $this->option($name);

        if ($value === null || $value === '') {
            return null;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($parsed === false) {
            throw new InvalidArgumentException("Option --{$name} must be a positive integer.");
        }

        return (int) $parsed;
    }
}
