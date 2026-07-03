<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceAreaCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $serviceAreaId;
    public readonly int $clusterId;
    public readonly int $actorId;
    public readonly Carbon $occurredAt;

    public function __construct(int $serviceAreaId, int $clusterId, int $actorId)
    {
        $this->serviceAreaId = $serviceAreaId;
        $this->clusterId = $clusterId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}