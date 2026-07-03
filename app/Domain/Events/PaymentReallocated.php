<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReallocated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $paymentId;
    public readonly int $customerId;
    public readonly int $fromAllocationId;
    public readonly int $toAllocationId;
    public readonly int $fromInvoiceId;
    public readonly int $toInvoiceId;
    public readonly float $amount;
    public readonly ?int $actorId;
    public readonly Carbon $occurredAt;

    public function __construct(
        int $paymentId,
        int $customerId,
        int $fromAllocationId,
        int $toAllocationId,
        int $fromInvoiceId,
        int $toInvoiceId,
        float $amount,
        ?int $actorId = null
    ) {
        $this->paymentId = $paymentId;
        $this->customerId = $customerId;
        $this->fromAllocationId = $fromAllocationId;
        $this->toAllocationId = $toAllocationId;
        $this->fromInvoiceId = $fromInvoiceId;
        $this->toInvoiceId = $toInvoiceId;
        $this->amount = $amount;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
