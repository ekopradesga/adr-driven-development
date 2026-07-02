<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Dispatched when an Invoice is published (frozen, payable, customer-visible). */
class InvoicePublished implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $invoiceId;
    public readonly int $customerId;
    public readonly int $subscriptionId;
    public readonly float $totalAmount;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;

    public function __construct(int $invoiceId, int $customerId, int $subscriptionId, float $totalAmount, ?int $actorId = null)
    {
        $this->invoiceId      = $invoiceId;
        $this->customerId     = $customerId;
        $this->subscriptionId = $subscriptionId;
        $this->totalAmount    = $totalAmount;
        $this->actorId        = $actorId;
        $this->occurredAt     = now();
    }
}
