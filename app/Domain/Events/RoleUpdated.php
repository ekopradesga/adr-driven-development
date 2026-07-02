<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * RoleUpdated Domain Event
 *
 * Dispatched when a role's properties or permission assignments are updated.
 * Listeners may invalidate authorization caches or log the change.
 */
class RoleUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * The ID of the updated role.
     */
    public readonly int $roleId;

    /**
     * Timestamp when the event occurred.
     */
    public readonly Carbon $occurredAt;

    /**
     * ID of the user who performed the update (null if system-initiated).
     */
    public readonly ?int $actorId;

    public function __construct(int $roleId, ?int $actorId = null)
    {
        $this->roleId = $roleId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
