<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * UserCreated Domain Event
 *
 * Dispatched when a new user account is created in the system.
 * Listeners may send welcome notifications, create initial activity logs,
 * or trigger onboarding workflows.
 */
class UserCreated
{
    use Dispatchable, SerializesModels;

    /**
     * The ID of the created user.
     */
    public readonly int $userId;

    /**
     * Timestamp when the event occurred.
     */
    public readonly Carbon $occurredAt;

    /**
     * ID of the user who created this user (null if system-initiated).
     */
    public readonly ?int $actorId;

    public function __construct(int $userId, ?int $actorId = null)
    {
        $this->userId = $userId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
