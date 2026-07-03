<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FatCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $fatId;
    public readonly int $actorId;
    public readonly Carbon $occurredAt;

    public function __construct(int $fatId, int $actorId)
    {
        $this->fatId = $fatId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
