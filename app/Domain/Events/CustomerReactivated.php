<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * CustomerReactivated Domain Event
 *
 * Dispatched when a previously suspended Customer account is reactivated
 * by an authorized administrator or Customer Service representative.
 *
 * Consumers: Notification Engine, Timeline, Activity Log, Customer Portal
 * Reference: docs/architecture/business-events.md — CustomerReactivated
 *            docs/workflows/customer-workflow.md — Suspended → Active transition
 */
class CustomerReactivated implements ShouldDispatchAfterCommit
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
