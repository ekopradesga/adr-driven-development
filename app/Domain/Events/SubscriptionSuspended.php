<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * SubscriptionSuspended Domain Event
 * Dispatched when a Subscription is suspended.
 * Reference: docs/architecture/business-events.md — SubscriptionSuspended
 */
class SubscriptionSuspended implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $subscriptionId;
    public readonly int $customerId;
    public readonly string $suspensionType; // 'overdue' or 'manual'
    public readonly ?string $reason;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;

    public function __construct(
        int $subscriptionId,
        int $customerId,
        string $suspensionType,
        ?string $reason = null,
        ?int $actorId = null
    ) {
        $this->subscriptionId = $subscriptionId;
        $this->customerId     = $customerId;
        $this->suspensionType = $suspensionType;
        $this->reason         = $reason;
        $this->actorId        = $actorId;
        $this->occurredAt     = now();
    }
}
