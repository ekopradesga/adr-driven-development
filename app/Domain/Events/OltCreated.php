<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OltCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $oltId;
    public readonly int $actorId;
    public readonly Carbon $occurredAt;

    public function __construct(int $oltId, int $actorId)
    {
        $this->oltId = $oltId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
