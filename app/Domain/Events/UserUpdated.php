<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * UserUpdated Domain Event
 *
 * Dispatched when a user's profile information is updated.
 * Listeners may log the update to activity logs or invalidate cached data.
 */
class UserUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * The ID of the updated user.
     */
    public readonly int $userId;

    /**
     * Timestamp when the event occurred.
     */
    public readonly Carbon $occurredAt;

    /**
     * ID of the user who performed the update (null if system-initiated).
     */
    public readonly ?int $actorId;

    public function __construct(int $userId, ?int $actorId = null)
    {
        $this->userId = $userId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
