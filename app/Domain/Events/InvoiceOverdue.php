<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * InvoiceOverdue Domain Event
 *
 * Dispatched when a published invoice exceeds its due date and grace period
 * without full settlement.
 *
 * Consumers: Subscription Lifecycle (suspension candidate), Collector Workflow,
 *            Notification, Timeline, Activity Log, Customer Portal
 * Reference: docs/architecture/business-events.md — InvoiceOverdue
 */
class InvoiceOverdue implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $invoiceId;
    public readonly int $customerId;
    public readonly int $subscriptionId;
    public readonly float $balanceAmount;
    public readonly Carbon $occurredAt;

    public function __construct(
        int $invoiceId,
        int $customerId,
        int $subscriptionId,
        float $balanceAmount
    ) {
        $this->invoiceId      = $invoiceId;
        $this->customerId     = $customerId;
        $this->subscriptionId = $subscriptionId;
        $this->balanceAmount  = $balanceAmount;
        $this->occurredAt     = now();
    }
}
