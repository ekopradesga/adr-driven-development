<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * RoleCreated Domain Event
 *
 * Dispatched when a new role is created in the RBAC system.
 * Listeners may log the creation to activity logs or notify administrators.
 */
class RoleCreated
{
    use Dispatchable, SerializesModels;

    /**
     * The ID of the created role.
     */
    public readonly int $roleId;

    /**
     * Timestamp when the event occurred.
     */
    public readonly Carbon $occurredAt;

    /**
     * ID of the user who created this role (null if system-initiated).
     */
    public readonly ?int $actorId;

    public function __construct(int $roleId, ?int $actorId = null)
    {
        $this->roleId = $roleId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
