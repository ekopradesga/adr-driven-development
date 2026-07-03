<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCompleted implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $paymentId;
    public readonly int $customerId;
    public readonly Carbon $occurredAt;

    public function __construct(int $paymentId, int $customerId)
    {
        $this->paymentId  = $paymentId;
        $this->customerId = $customerId;
        $this->occurredAt = now();
    }
}
