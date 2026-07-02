<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * InvoiceCancelled Domain Event
 *
 * Dispatched when a draft invoice is cancelled before publication.
 * Cancellation reason is preserved for audit.
 *
 * Consumers: Timeline, Activity Log, Notification (internal)
 * Reference: docs/architecture/business-events.md — InvoiceCancelled
 */
class InvoiceCancelled implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $invoiceId;
    public readonly int $customerId;
    public readonly int $subscriptionId;
    public readonly string $reason;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;

    public function __construct(
        int $invoiceId,
        int $customerId,
        int $subscriptionId,
        string $reason,
        ?int $actorId = null
    ) {
        $this->invoiceId      = $invoiceId;
        $this->customerId     = $customerId;
        $this->subscriptionId = $subscriptionId;
        $this->reason         = $reason;
        $this->actorId        = $actorId;
        $this->occurredAt     = now();
    }
}
