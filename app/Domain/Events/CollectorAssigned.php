<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CollectorAssigned implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $collectionTaskId,
        public readonly int $customerId,
        public readonly int $employeeId,
        public readonly Carbon $occurredAt = new Carbon()
    ) {}
}