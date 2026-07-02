<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * CustomerRegistered Domain Event
 *
 * Dispatched when a new Customer record is created in the system.
 * Implements ShouldDispatchAfterCommit to ensure the event fires only after
 * the transaction commits and the customer record is persisted.
 *
 * Consumers: Notification Engine, Timeline, Activity Log, Customer Portal
 * Reference: docs/architecture/business-events.md — CustomerRegistered
 */
class CustomerRegistered implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $customerId;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;

    public function __construct(int $customerId, ?int $actorId = null)
    {
        $this->customerId = $customerId;
        $this->actorId    = $actorId;
        $this->occurredAt = now();
    }
}
