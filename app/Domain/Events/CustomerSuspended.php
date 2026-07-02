<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * CustomerSuspended Domain Event
 *
 * Dispatched when a Customer account is suspended by an authorized
 * administrator or Customer Service representative.
 *
 * IMPORTANT: Customer account suspension is an administrative action only.
 * It is NEVER triggered by billing overdue policy. Billing-driven service
 * restriction operates at the Subscription level, not the Customer account level.
 *
 * Consumers: Notification Engine, Timeline, Activity Log, Customer Portal
 * Reference: docs/architecture/business-events.md — CustomerSuspended
 *            docs/workflows/customer-workflow.md — Active → Suspended transition
 */
class CustomerSuspended implements ShouldDispatchAfterCommit
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
