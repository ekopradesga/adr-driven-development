<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentPartiallyAllocated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $paymentId;
    public readonly int $customerId;
    public readonly float $allocatedAmount;
    public readonly float $unallocatedAmount;
    public readonly Carbon $occurredAt;

    public function __construct(int $paymentId, int $customerId, float $allocatedAmount, float $unallocatedAmount)
    {
        $this->paymentId        = $paymentId;
        $this->customerId       = $customerId;
        $this->allocatedAmount  = $allocatedAmount;
        $this->unallocatedAmount = $unallocatedAmount;
        $this->occurredAt       = now();
    }
}
