<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * RoleDeleted Domain Event
 *
 * Dispatched when a role is deleted from the RBAC system.
 * Listeners may log the deletion or clean up related data.
 */
class RoleDeleted
{
    use Dispatchable, SerializesModels;

    /**
     * The ID of the deleted role.
     */
    public readonly int $roleId;

    /**
     * Timestamp when the event occurred.
     */
    public readonly Carbon $occurredAt;

    /**
     * ID of the user who deleted this role (null if system-initiated).
     */
    public readonly ?int $actorId;

    public function __construct(int $roleId, ?int $actorId = null)
    {
        $this->roleId = $roleId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
