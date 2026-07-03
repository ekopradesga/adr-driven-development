<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReversed implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $paymentId;
    public readonly int $customerId;
    public readonly string $reason;
    public readonly Carbon $occurredAt;

    public function __construct(int $paymentId, int $customerId, string $reason)
    {
        $this->paymentId = $paymentId;
        $this->customerId = $customerId;
        $this->reason = $reason;
        $this->occurredAt = now();
    }
}
