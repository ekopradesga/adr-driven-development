<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * SubscriptionCreated Domain Event
 * Dispatched when a new Subscription is created (status: pending).
 * Reference: docs/architecture/business-events.md — SubscriptionCreated
 */
class SubscriptionCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $subscriptionId;
    public readonly int $customerId;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;

    public function __construct(int $subscriptionId, int $customerId, ?int $actorId = null)
    {
        $this->subscriptionId = $subscriptionId;
        $this->customerId     = $customerId;
        $this->actorId        = $actorId;
        $this->occurredAt     = now();
    }
}
