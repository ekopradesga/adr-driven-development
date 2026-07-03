<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WireRouterMaintenanceStarted implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $routerId;
    public readonly int $actorId;
    public readonly Carbon $occurredAt;

    public function __construct(int $routerId, int $actorId)
    {
        $this->routerId = $routerId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}