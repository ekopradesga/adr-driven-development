<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * SubscriptionTerminated Domain Event
 * Dispatched when a Subscription is permanently terminated.
 * Reference: docs/architecture/business-events.md — SubscriptionTerminated
 */
class SubscriptionTerminated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $subscriptionId;
    public readonly int $customerId;
    public readonly string $reason;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;

    public function __construct(
        int $subscriptionId,
        int $customerId,
        string $reason,
        ?int $actorId = null
    ) {
        $this->subscriptionId = $subscriptionId;
        $this->customerId     = $customerId;
        $this->reason         = $reason;
        $this->actorId        = $actorId;
        $this->occurredAt     = now();
    }
}
