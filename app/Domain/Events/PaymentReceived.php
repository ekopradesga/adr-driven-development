<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $paymentId;
    public readonly int $customerId;
    public readonly float $amount;
    public readonly Carbon $occurredAt;

    public function __construct(int $paymentId, int $customerId, float $amount)
    {
        $this->paymentId  = $paymentId;
        $this->customerId = $customerId;
        $this->amount     = $amount;
        $this->occurredAt = now();
    }
}
