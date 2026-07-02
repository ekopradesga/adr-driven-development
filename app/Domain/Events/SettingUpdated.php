<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * SettingUpdated Domain Event
 *
 * Dispatched when a configuration setting value or status is updated.
 * Listeners may invalidate setting caches, log the change, or notify administrators.
 */
class SettingUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * The ID of the updated setting.
     */
    public readonly int $settingId;

    /**
     * The setting key (e.g., 'billing.due_days').
     */
    public readonly string $key;

    /**
     * Timestamp when the event occurred.
     */
    public readonly Carbon $occurredAt;

    /**
     * ID of the user who performed the update (null if system-initiated).
     */
    public readonly ?int $actorId;

    public function __construct(int $settingId, string $key, ?int $actorId = null)
    {
        $this->settingId = $settingId;
        $this->key = $key;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
