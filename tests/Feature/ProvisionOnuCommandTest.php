<?php

namespace Tests\Feature;

use App\Jobs\Provisioning\ProvisionOnuJob;
use App\Models\Customer;
use App\Models\Onu;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProvisionOnuCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_provisioning_job_by_subscription_id(): void
    {
        Queue::fake();

        $customer = Customer::factory()->active()->create();
        $onu = Onu::factory()->unprovisioned()->create();
        $subscription = Subscription::factory()->active()->create([
            'customer_id' => $customer->id,
            'package_id' => 1,
            'onu_id' => $onu->id,
        ]);

        $this->artisan('provision:onu', [
            '--subscription-id' => (string) $subscription->id,
            '--actor-id' => '99',
        ])->assertExitCode(0);

        Queue::assertPushed(ProvisionOnuJob::class, function (ProvisionOnuJob $job) use ($subscription, $onu) {
            return $job->subscriptionId === $subscription->id
                && $job->onuId === $onu->id
                && $job->actorId === 99;
        });
    }

    public function test_fails_when_subscription_has_no_onu_assignment(): void
    {
        Queue::fake();

        $customer = Customer::factory()->active()->create();
        $subscription = Subscription::factory()->active()->create([
            'customer_id' => $customer->id,
            'package_id' => 1,
            'onu_id' => null,
        ]);

        $this->artisan('provision:onu', [
            '--subscription-id' => (string) $subscription->id,
        ])->assertExitCode(1);

        Queue::assertNothingPushed();
    }

    public function test_dispatches_provisioning_job_by_onu_id(): void
    {
        Queue::fake();

        $onu = Onu::factory()->unprovisioned()->create();

        $this->artisan('provision:onu', [
            '--onu-id' => (string) $onu->id,
        ])->assertExitCode(0);

        Queue::assertPushed(ProvisionOnuJob::class, function (ProvisionOnuJob $job) use ($onu) {
            return $job->onuId === $onu->id && $job->subscriptionId === null;
        });
    }
}
