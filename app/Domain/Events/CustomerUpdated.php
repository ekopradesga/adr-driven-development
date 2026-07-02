<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * CustomerUpdated Domain Event
 *
 * Dispatched when a Customer profile is modified by an authorized actor.
 *
 * Consumers: Timeline, Activity Log, Notification Engine (if contact fields changed)
 * Reference: docs/architecture/business-events.md — CustomerUpdated
 */
class CustomerUpdated implements ShouldDispatchAfterCommit
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
