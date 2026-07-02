<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Dispatched when a draft Invoice is generated. */
class InvoiceGenerated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $invoiceId;
    public readonly int $customerId;
    public readonly int $subscriptionId;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;

    public function __construct(int $invoiceId, int $customerId, int $subscriptionId, ?int $actorId = null)
    {
        $this->invoiceId      = $invoiceId;
        $this->customerId     = $customerId;
        $this->subscriptionId = $subscriptionId;
        $this->actorId        = $actorId;
        $this->occurredAt     = now();
    }
}
