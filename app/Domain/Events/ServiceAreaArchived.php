<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceAreaArchived implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $serviceAreaId;
    public readonly int $actorId;
    public readonly Carbon $occurredAt;

    public function __construct(int $serviceAreaId, int $actorId)
    {
        $this->serviceAreaId = $serviceAreaId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}