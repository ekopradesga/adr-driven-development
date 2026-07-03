<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public readonly int $packageId;
    public readonly int $actorId;
    public readonly Carbon $occurredAt;

    public function __construct(int $packageId, int $actorId)
    {
        $this->packageId = $packageId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
