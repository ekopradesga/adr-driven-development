# Domain Event Architecture

**Version:** 1.0  
**Status:** Active  
**Last Updated:** 2026-07-02

---

## Overview

Domain Events are immutable records of significant business state changes within the ISP Management Platform. They enable decoupled communication between modules, provide audit trails, and support event-driven workflows.

This document defines the authoritative event architecture for the platform.

---

## Event Classification

### Domain Events

**Definition:** Events representing business-significant state changes within a single bounded context.

**Characteristics:**
- Past tense naming (e.g., `UserCreated`, `InvoicePublished`)
- Immutable once dispatched
- Contain only essential state identifiers and metadata
- Synchronous or asynchronous dispatch via Laravel Event system
- Consumed by Listeners within the same application

**Examples:**
- `UserCreated` — A new user account was created
- `SubscriptionActivated` — A subscription transitioned to Active state
- `PaymentReceived` — A payment was recorded and allocated
- `SettingUpdated` — A configuration setting value was changed

**Use Cases:**
- Triggering side effects (send notification, update derived data)
- Maintaining audit logs (Timeline, ActivityLog)
- Cross-module coordination (provisioning triggered by subscription activation)

### Integration Events

**Definition:** Events intended for external system consumption or inter-service communication in a distributed architecture.

**Characteristics:**
- Past tense naming with context prefix (e.g., `ISP.UserCreated`)
- Published to message broker (e.g., RabbitMQ, Kafka) — not implemented in v1.0
- Schema versioned
- Durable and replayable
- Include full event payload for external consumers

**Status in Version 1.0:** Reserved for future use. All events in v1.0 are Domain Events dispatched via Laravel's event system.

---

## Event Naming Convention

**Pattern:** `{Entity}{PastTenseVerb}`

**Examples:**
- `UserCreated`, `UserUpdated`, `UserSuspended`
- `RoleCreated`, `RoleUpdated`, `RoleDeleted`
- `InvoicePublished`, `InvoicePaid`, `InvoiceVoided`
- `SubscriptionActivated`, `SubscriptionSuspended`, `SubscriptionTerminated`
- `PaymentReceived`, `PaymentAllocated`, `PaymentRefunded`

**Rules:**
1. Use PascalCase
2. Always past tense
3. Entity name first, action second
4. Be specific — prefer `InvoicePublished` over `InvoiceChanged`
5. Avoid generic verbs — prefer `UserSuspended` over `UserStatusChanged`

---

## Event Structure

All domain events must implement a consistent structure.

### Required Properties

| Property | Type | Description |
|---|---|---|
| `entityId` | `int` | The primary key of the affected entity |
| `entityType` | `string` | Fully-qualified class name of the entity (e.g., `App\Models\User`) |
| `occurredAt` | `Carbon` | Timestamp when the event occurred (immutable) |
| `actorId` | `int\|null` | ID of the user who triggered the event (null for system-initiated) |

### Optional Context Properties

Events may include additional **immutable** context required by listeners:

- State transition context (e.g., `fromStatus`, `toStatus`)
- Related entity IDs (e.g., `subscriptionId` for a payment event)
- Action metadata (e.g., `reason` for suspension)

**What NOT to include:**
- ❌ Full entity models (pass ID only)
- ❌ Collections or query results
- ❌ Mutable state or computed values
- ❌ Business logic or validation rules

---

## Event Lifecycle

```
┌─────────────┐
│   Service   │  Business logic executes, state changes committed
└──────┬──────┘
       │ event(new UserCreated($user->id, auth()->id()))
       ▼
┌─────────────┐
│    Event    │  Immutable record dispatched via Laravel Event system
└──────┬──────┘
       │ Laravel dispatches to registered listeners
       ▼
┌─────────────┐
│  Listener   │  Handles side effects (notifications, logs, derived state)
└──────┬──────┘
       │ Calls infrastructure (Notification, ActivityLog, Job queue)
       ▼
┌─────────────┐
│Infrastructure│ Notification sent, log written, job queued
└─────────────┘
```

### Dispatch Rules

1. **After database commit** — Events must be dispatched AFTER the transaction commits successfully. Use `DB::afterCommit()` or dispatch from Service methods after `save()`.

2. **From Services only** — Controllers must never dispatch events directly. Services own business logic and event dispatch.

3. **One event per state change** — Each significant state transition dispatches exactly one event. Avoid event storms.

4. **Idempotent listeners** — Listeners must be idempotent. The same event may be processed multiple times (e.g., job retries).

5. **No cascading events** — Listeners must not dispatch additional domain events. Use Jobs for complex workflows.

---

## Listener Responsibilities

Listeners handle side effects triggered by domain events.

### Allowed Operations

✅ **Send notifications** — Email, SMS, push notifications  
✅ **Write audit logs** — TimelineEvent, ActivityLog entries  
✅ **Update derived state** — Caches, counters, read models  
✅ **Queue jobs** — Provisioning, external API calls, reports  
✅ **Trigger workflows** — State machine transitions in other aggregates  

### Prohibited Operations

❌ **Modify the originating aggregate** — Listeners must not update the entity that dispatched the event  
❌ **Perform business validation** — Validation belongs in Services, not Listeners  
❌ **Dispatch additional domain events** — Avoid cascading events; use Jobs for workflows  
❌ **Execute long-running operations synchronously** — Queue Jobs instead  

### Listener Naming Convention

**Pattern:** `{Action}{EventName}Listener`

**Examples:**
- `SendUserCreatedNotificationListener`
- `LogUserSuspendedActivityListener`
- `UpdateSubscriptionCacheListener`
- `QueueProvisioningJobListener`

---

## Event Registration

All event-listener mappings are registered in `EventServiceProvider`.

**Example:**

```php
protected $listen = [
    UserCreated::class => [
        SendUserCreatedNotificationListener::class,
        LogUserCreatedActivityListener::class,
    ],
    SubscriptionActivated::class => [
        QueueProvisioningJobListener::class,
        SendActivationNotificationListener::class,
        LogSubscriptionActivatedListener::class,
    ],
];
```

**Rules:**
- Register all listeners explicitly — do not use auto-discovery in production
- Order matters — listeners execute in registration order
- Queued listeners implement `ShouldQueue`

---

## Implementation Guidelines

### Event Class Template

```php
<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCreated
{
    use Dispatchable, SerializesModels;

    public readonly int $userId;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;

    public function __construct(int $userId, ?int $actorId = null)
    {
        $this->userId = $userId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
```

### Listener Class Template

```php
<?php

namespace App\Domain\Listeners;

use App\Domain\Events\UserCreated;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogUserCreatedActivityListener implements ShouldQueue
{
    public function handle(UserCreated $event): void
    {
        ActivityLog::create([
            'activity_loggable_type' => 'App\Models\User',
            'activity_loggable_id'   => $event->userId,
            'actor_id'               => $event->actorId,
            'action'                 => 'created',
            'occurred_at'            => $event->occurredAt,
        ]);
    }
}
```

---

## Event Storage and Audit

### Timeline Events

All domain events representing user-visible state changes must generate a `TimelineEvent` record.

**Responsibility:** Listener writes to `timeline_events` table.

### Activity Logs

All domain events must generate an `ActivityLog` record for audit purposes.

**Responsibility:** Listener writes to `activity_logs` table.

### Event Sourcing

Version 1.0 does **not** implement event sourcing. Events are dispatched for side effects only, not as the source of truth. The database remains the authoritative state store.

**Future consideration:** Event Sourcing may be introduced for specific aggregates (e.g., Invoice, Payment) in future versions.

---

## Testing Events

### Unit Tests

Test that Services dispatch the correct events:

```php
Event::fake([UserCreated::class]);

$this->userService->create($data);

Event::assertDispatched(UserCreated::class, function ($event) use ($user) {
    return $event->userId === $user->id;
});
```

### Listener Tests

Test listeners in isolation:

```php
$event = new UserCreated($user->id, $actor->id);

$listener = new LogUserCreatedActivityListener();
$listener->handle($event);

$this->assertDatabaseHas('activity_logs', [
    'activity_loggable_id' => $user->id,
    'action' => 'created',
]);
```

---

## Architectural Decisions

| Decision | Rationale |
|---|---|
| **Laravel Event System over message broker** | Version 1.0 is single-application. Laravel events provide sufficient decoupling without operational complexity. |
| **Synchronous listeners by default** | Simplifies debugging and maintains transactional consistency. Queue long-running listeners explicitly. |
| **Events dispatched from Services** | Services own business logic. Controllers remain thin orchestrators. |
| **Immutable events** | Events are historical facts. Once dispatched, they cannot be altered. |
| **No cascading events in listeners** | Prevents event storms and maintains clear dependency flow. Use Jobs for workflows. |

---

## Migration Path

**Current State (2026-07-02):** No domain events in use. Services mutate state directly.

**This Story (Sprint 1.3 Story 1):** Establish event architecture, create sample events, document standards. No runtime behavior changes.

**Future Stories:**
1. Refactor Services to dispatch events after state changes
2. Implement Listeners for notifications, audit logs, and workflows
3. Register event-listener mappings in EventServiceProvider
4. Add event dispatch to all CRUD operations in existing Services

---

## Related Documentation

- `docs/architecture/decisions.md` — Architecture Decision 14: Business Events
- `docs/architecture/business-events.md` — Catalog of all platform business events
- `docs/workflows/*.md` — Workflow state transitions trigger domain events

---

## Glossary

| Term | Definition |
|---|---|
| **Domain Event** | Immutable record of a business state change |
| **Integration Event** | Event published for external system consumption |
| **Listener** | Code that executes in response to an event |
| **Event Sourcing** | Architecture pattern where events are the source of truth (not used in v1.0) |
| **Aggregate** | Consistency boundary — events represent aggregate state changes |
