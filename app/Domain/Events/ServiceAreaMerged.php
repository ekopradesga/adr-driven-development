<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceAreaMerged implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $serviceAreaId;
    public readonly int $destinationServiceAreaId;
    public readonly int $actorId;
    public readonly Carbon $occurredAt;

    public function __construct(int $serviceAreaId, int $destinationServiceAreaId, int $actorId)
    {
        $this->serviceAreaId = $serviceAreaId;
        $this->destinationServiceAreaId = $destinationServiceAreaId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}