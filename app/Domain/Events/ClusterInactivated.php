<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClusterInactivated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $clusterId;
    public readonly int $actorId;
    public readonly Carbon $occurredAt;

    public function __construct(int $clusterId, int $actorId)
    {
        $this->clusterId = $clusterId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}