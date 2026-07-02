<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * SubscriptionReactivationPending Domain Event
 * Dispatched when a suspended Subscription enters Reactivation Pending state.
 * Reference: docs/architecture/business-events.md — SubscriptionReactivationPending
 */
class SubscriptionReactivationPending implements ShouldDispatchAfterCommit
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
