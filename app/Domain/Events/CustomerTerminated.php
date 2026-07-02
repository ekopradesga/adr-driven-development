<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * CustomerTerminated Domain Event
 *
 * Dispatched when a Customer account is permanently closed.
 * Termination requires all Subscriptions to be in terminated state.
 *
 * Consumers: Notification Engine, Timeline, Activity Log, Customer Portal, Billing
 * Reference: docs/architecture/business-events.md — CustomerTerminated
 *            docs/workflows/customer-workflow.md — Active/Suspended → Terminated transition
 */
class CustomerTerminated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $customerId;
    public readonly string $reason;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;

    public function __construct(int $customerId, string $reason, ?int $actorId = null)
    {
        $this->customerId = $customerId;
        $this->reason     = $reason;
        $this->actorId    = $actorId;
        $this->occurredAt = now();
    }
}
