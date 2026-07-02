<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * CustomerConverted Domain Event
 *
 * Dispatched when a Customer transitions from Prospect to Active for the
 * first time (i.e., on their first Subscription activation).
 *
 * This event is produced by CustomerService when it listens to SubscriptionActivated
 * and detects the Customer is in Prospect state.
 *
 * Consumers: Notification Engine, Timeline, Activity Log, Reporting
 * Reference: docs/architecture/business-events.md — CustomerConverted
 *            docs/workflows/customer-workflow.md — Prospect → Active transition
 */
class CustomerConverted implements ShouldDispatchAfterCommit
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
