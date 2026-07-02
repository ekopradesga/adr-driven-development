# Platform Logging Standard

**Architecture Version:** 1.0  
**Last Updated:** 2026-07-02  
**Status:** Authoritative

---

## 1. Overview

This document establishes the authoritative logging standard for the ISP Management Platform. All application logging must follow these conventions to ensure consistent observability, security auditing, compliance, and operational troubleshooting.

### Purpose

- **Security Auditing**: Track authentication, authorization failures, and suspicious activity
- **Compliance**: Maintain audit trails for financial transactions and configuration changes
- **Operational Intelligence**: Debug production issues and monitor system health
- **Business Visibility**: Track significant business events and user actions

### Scope

This standard covers:
- Application logging via Laravel's `Log` facade
- Security event logging
- Authentication and authorization events
- Configuration change auditing
- Business event correlation with Domain Events
- Structured logging conventions

**Out of Scope:**
- Infrastructure logs (web server, database, OS)
- Application Performance Monitoring (APM) instrumentation
- Error tracking service integration (Sentry, Bugsnag)

---

## 2. Log Levels

Laravel provides eight RFC 5424 log levels. Use them consistently across the platform.

### Emergency

**When to Use:** System is unusable. Immediate action required.

**Examples:**
- Database connection completely lost
- Critical configuration missing (encryption key, database credentials)
- File system full preventing writes

```php
Log::emergency('Database connection failed and no fallback available', [
    'error' => $exception->getMessage(),
]);
```

**Action Required:** Immediate on-call alert. Production incident.

---

### Alert

**When to Use:** Critical action must be taken immediately. System may fail soon.

**Examples:**
- Queue worker stopped processing critical jobs
- Cache system completely unavailable
- Payment gateway unreachable

```php
Log::alert('Payment gateway unreachable for 5 consecutive attempts', [
    'gateway' => 'StripeGateway',
    'attempts' => 5,
    'last_error' => $lastError,
]);
```

**Action Required:** Immediate investigation. May escalate to Emergency.

---

### Critical

**When to Use:** Critical conditions that don't require immediate action but need urgent attention.

**Examples:**
- Unexpected exception in critical business flow
- Data integrity violation detected
- Security constraint bypass attempt

```php
Log::critical('Invoice total mismatch detected', [
    'invoice_id' => $invoice->id,
    'calculated_total' => $calculated,
    'stored_total' => $invoice->total,
]);
```

**Action Required:** Urgent review within business hours.

---

### Error

**When to Use:** Runtime errors that don't require immediate action but should be monitored.

**Examples:**
- Failed email delivery after retries
- Third-party API call failure (non-critical)
- Validation failure in background job

```php
Log::error('Failed to send notification after 3 attempts', [
    'notification_id' => $notification->id,
    'channel' => 'email',
    'recipient' => $notification->recipient,
    'error' => $exception->getMessage(),
]);
```

**Action Required:** Monitor and investigate patterns. May require fix.

---

### Warning

**When to Use:** Exceptional occurrences that are not errors. Deprecated usage. Undesirable situations.

**Examples:**
- User login with inactive role (blocked by authentication hardening)
- Deprecated API endpoint usage
- Near-limit resource consumption

```php
Log::warning('User attempted login without active role', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip' => $request->ip(),
]);
```

**Action Required:** Review periodically. May indicate configuration issue.

---

### Notice

**When to Use:** Normal but significant events.

**Examples:**
- Configuration setting updated
- User role assignment changed
- Scheduled task completed successfully

```php
Log::notice('Setting updated', [
    'setting_id' => $setting->id,
    'key' => $setting->key,
    'old_value' => $oldValue,
    'new_value' => $setting->value,
    'actor_id' => auth()->id(),
]);
```

**Action Required:** None. Informational audit trail.

---

### Info

**When to Use:** Interesting events. User actions. Business flow milestones.

**Examples:**
- User login successful
- Subscription activated
- Invoice published

```php
Log::info('User login successful', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip' => $request->ip(),
]);
```

**Action Required:** None. Operational visibility.

---

### Debug

**When to Use:** Detailed diagnostic information. Development and staging only.

**Examples:**
- Query execution details
- API request/response payloads
- Workflow state transitions

```php
Log::debug('Subscription workflow state transition', [
    'subscription_id' => $subscription->id,
    'from_state' => $oldState,
    'to_state' => $newState,
    'trigger' => $trigger,
]);
```

**Action Required:** None. Disabled in production by default.

**⚠ Never log sensitive data at debug level:** passwords, tokens, API keys, PII.

---

## 3. Security Events

All security-relevant events must be logged for audit and compliance.

### Failed Authentication

**Level:** `warning`

**When:** Invalid credentials provided during login.

```php
Log::warning('Failed login attempt', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

**Context Required:**
- Email address (attempted)
- IP address
- User agent

---

### Account Status Login Block

**Level:** `warning`

**When:** Valid credentials but account is inactive/suspended.

```php
Log::warning('Login blocked due to account status', [
    'user_id' => $user->id,
    'status' => $user->status->value,
    'ip' => $request->ip(),
]);
```

**Context Required:**
- User ID
- Account status
- IP address

---

### Missing Active Role

**Level:** `warning`

**When:** User has no active role assignment.

```php
Log::warning('Login blocked due to missing active role', [
    'user_id' => $user->id,
    'ip' => $request->ip(),
]);
```

---

### Authorization Failure

**Level:** `notice`

**When:** User attempts action without required permission.

```php
Log::notice('Authorization denied', [
    'user_id' => auth()->id(),
    'permission' => $permission,
    'resource_type' => get_class($resource),
    'resource_id' => $resource->id,
    'ip' => request()->ip(),
]);
```

**Context Required:**
- User ID
- Required permission
- Resource type and ID
- IP address

---

### Impersonation Started

**Level:** `notice`

**When:** Administrator begins impersonating another user.

```php
Log::notice('User impersonation started', [
    'admin_id' => auth()->id(),
    'target_user_id' => $targetUser->id,
    'ip' => request()->ip(),
]);
```

---

### Impersonation Stopped

**Level:** `notice`

**When:** Administrator stops impersonating.

```php
Log::notice('User impersonation stopped', [
    'admin_id' => $originalUserId,
    'impersonated_user_id' => auth()->id(),
    'duration_seconds' => $duration,
]);
```

---

### Suspicious Activity

**Level:** `warning` or `alert` depending on severity

**When:** Rate limit exceeded, enumeration detected, suspicious pattern.

```php
Log::alert('Possible account enumeration detected', [
    'ip' => $request->ip(),
    'attempts' => $attemptCount,
    'time_window' => '60 seconds',
]);
```

---

## 4. Authentication Events

Track all authentication lifecycle events.

### Successful Login

**Level:** `info`

```php
Log::info('User login successful', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip' => $request->ip(),
]);
```

---

### Logout

**Level:** `info`

```php
Log::info('User logout', [
    'user_id' => auth()->id(),
]);
```

---

### Password Reset Requested

**Level:** `info`

```php
Log::info('Password reset requested', [
    'email' => $request->email,
    'ip' => $request->ip(),
]);
```

---

### Password Reset Completed

**Level:** `notice`

```php
Log::notice('Password reset completed', [
    'user_id' => $user->id,
    'ip' => $request->ip(),
]);
```

---

### Email Verification Sent

**Level:** `info`

```php
Log::info('Email verification sent', [
    'user_id' => $user->id,
    'email' => $user->email,
]);
```

---

### Email Verified

**Level:** `notice`

```php
Log::notice('Email verified', [
    'user_id' => $user->id,
]);
```

---

## 5. Configuration Changes

All configuration and setting changes must be logged for audit compliance.

### Setting Value Updated

**Level:** `notice`

```php
Log::notice('Setting updated', [
    'setting_id' => $setting->id,
    'key' => $setting->key,
    'scope' => $setting->scope->value,
    'scope_id' => $setting->scope_id,
    'old_value' => $oldValue,
    'new_value' => $setting->value,
    'actor_id' => auth()->id(),
]);
```

**Context Required:**
- Setting ID and key
- Scope and scope_id
- Old and new values
- Actor (who made the change)

---

### Setting Status Changed

**Level:** `notice`

```php
Log::notice('Setting status changed', [
    'setting_id' => $setting->id,
    'key' => $setting->key,
    'old_status' => $oldStatus,
    'new_status' => $setting->active ? 'active' : 'inactive',
    'actor_id' => auth()->id(),
]);
```

---

### Registry Entry Modified

**Level:** `warning` (registry is governance)

```php
Log::warning('Setting registry entry modified', [
    'registry_entry_id' => $entry->id,
    'key' => $entry->key,
    'changes' => $changes,
    'actor_id' => auth()->id(),
]);
```

**Note:** Registry changes should be rare and controlled. Log at `warning` level to ensure visibility.

---

### Environment Configuration Changed

**Level:** `alert`

**When:** `.env` file modified in production (should be rare).

```php
Log::alert('Environment configuration changed', [
    'file' => '.env',
    'changed_by' => 'deployment',
]);
```

---

## 6. Business Events

Business events from the Domain Event Architecture (`docs/architecture/events.md`) should generate corresponding log entries.

### Principle

**Every Domain Event dispatch should generate a log entry.**

This provides a complete audit trail independent of event listeners and allows troubleshooting even if listeners fail.

### Pattern

```php
// In Service after dispatching Domain Event
event(new UserCreated($user->id, auth()->id()));

Log::info('User created', [
    'user_id' => $user->id,
    'email' => $user->email,
    'status' => $user->status->value,
    'actor_id' => auth()->id(),
]);
```

### Correlation

Domain Events and Logs serve different purposes:

| Aspect | Domain Event | Log Entry |
|---|---|---|
| **Purpose** | Trigger side effects (listeners) | Audit trail and troubleshooting |
| **Audience** | Application code (listeners) | Developers and operators |
| **Lifetime** | Transient (dispatch and consume) | Persistent (retained per policy) |
| **Failure Impact** | Listener may fail; event still dispatched | Log write failure is silent |

**Both are required.** Events drive automation. Logs provide observability.

### Business Event Log Mapping

| Domain Event | Log Level | Log Message Pattern |
|---|---|---|
| `UserCreated` | `info` | `User created` |
| `UserUpdated` | `info` | `User updated` |
| `RoleCreated` | `info` | `Role created` |
| `RoleUpdated` | `info` | `Role updated` |
| `RoleDeleted` | `notice` | `Role deleted` |
| `SettingUpdated` | `notice` | `Setting updated` |
| `SubscriptionActivated` | `info` | `Subscription activated` |
| `InvoicePublished` | `info` | `Invoice published` |
| `PaymentReceived` | `info` | `Payment received` |
| `TicketCreated` | `info` | `Ticket created` |

---

## 7. Log Structure Standard

All log entries must follow a consistent structure.

### Required Context

Every log entry should include:

```php
Log::info('Action performed', [
    'entity_type' => 'Subscription',  // Class name or entity type
    'entity_id' => $subscription->id,  // Primary key
    'actor_id' => auth()->id(),        // Who performed the action (nullable)
    'ip' => request()->ip(),           // Source IP (if HTTP request)
]);
```

### Optional Context

Include when relevant:

```php
[
    'old_value' => $before,
    'new_value' => $after,
    'reason' => $reason,
    'metadata' => $additionalContext,
]
```

### Forbidden Context

**Never log:**
- Passwords (plaintext or hashed)
- API keys or tokens
- Credit card numbers
- Full PII in production logs (use IDs instead)

---

## 8. ActivityLog and TimelineEvent Integration

The platform has two entity-specific audit mechanisms:

### ActivityLog

**Purpose:** Detailed activity history attached to entities.

**Use When:**
- Action requires structured audit trail
- Activity needs to be displayed in UI
- Queryable history required

**Example:**
```php
$user->activityLogs()->create([
    'actor_id' => auth()->id(),
    'action' => 'status_changed',
    'description' => 'User status changed from active to suspended',
    'properties' => ['old' => 'active', 'new' => 'suspended'],
]);
```

---

### TimelineEvent

**Purpose:** High-level lifecycle milestones attached to aggregates.

**Use When:**
- Significant state transition
- User-facing timeline display needed
- Workflow checkpoint

**Example:**
```php
$subscription->timelineEvents()->create([
    'event_type' => 'subscription.activated',
    'title' => 'Subscription Activated',
    'description' => 'Subscription activated by administrator',
    'actor_id' => auth()->id(),
]);
```

---

### Relationship to Application Logs

| Mechanism | Visibility | Query Pattern | Retention |
|---|---|---|---|
| **Application Log** | Developers, operators | Text search, log aggregation | Per infrastructure policy |
| **ActivityLog** | Administrators, audit | Database query by entity | Indefinite (immutable) |
| **TimelineEvent** | End users, admins | Database query by aggregate | Indefinite (immutable) |

**Guideline:**
- **Application Log:** Always write for security/business events
- **ActivityLog:** Write for actions requiring structured audit
- **TimelineEvent:** Write for user-visible lifecycle milestones

**All three may be written for the same event.**

Example: User status change writes all three:
```php
// 1. Application Log
Log::notice('User suspended', ['user_id' => $user->id, 'actor_id' => auth()->id()]);

// 2. ActivityLog (structured audit)
$user->activityLogs()->create([...]);

// 3. TimelineEvent (user-visible)
$user->timelineEvents()->create([...]);
```

---

## 9. Performance Considerations

### Synchronous Logging

Laravel's `Log` facade writes **synchronously** by default. Log writes block request processing.

**Guidelines:**
- Keep log context arrays small (avoid large objects)
- Use `debug` level for verbose logging (disabled in production)
- Avoid logging in tight loops

---

### Asynchronous Logging

For high-throughput scenarios, queue log writes:

```php
dispatch(new LogBusinessEventJob('User created', $context))->onQueue('logging');
```

**Use When:**
- Logging non-critical events in high-traffic endpoints
- Log context requires expensive serialization

**Do NOT queue:**
- Security events (must be synchronous)
- Error logs (may indicate issue with queue itself)

---

### Database Logging Performance

If using `database` log channel, ensure:
- Table is indexed on `created_at` and `level`
- Log rotation/archival policy is in place
- Consider dedicated database connection for logs

---

## 10. Log Channels

Laravel supports multiple log channels. Use appropriate channel for each context.

### Recommended Channels

```php
// config/logging.php
'channels' => [
    'stack' => [...],          // Default: file + stderr
    'single' => [...],         // Single file for all logs
    'daily' => [...],          // Daily rotating files
    
    'security' => [            // Security events only
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'notice',
        'days' => 90,          // Retain 90 days minimum
    ],
    
    'business' => [            // Business events
        'driver' => 'daily',
        'path' => storage_path('logs/business.log'),
        'level' => 'info',
        'days' => 30,
    ],
    
    'audit' => [               // Configuration changes
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'notice',
        'days' => 365,         // Retain 1 year minimum
    ],
],
```

### Channel Usage

```php
// Security event
Log::channel('security')->warning('Failed login attempt', [...]);

// Business event
Log::channel('business')->info('Subscription activated', [...]);

// Configuration change
Log::channel('audit')->notice('Setting updated', [...]);
```

---

## 11. Testing Guidelines

### Unit Tests

**Do NOT assert log calls in unit tests.** Logging is a side effect, not business logic.

**Exception:** Security-critical logging (e.g., suspicious activity detection) may be tested.

```php
// Acceptable security test
Log::shouldReceive('warning')
    ->once()
    ->with('Account enumeration detected', Mockery::subset(['ip' => '1.2.3.4']));

$response = $this->post('/login', ['email' => 'test@example.com']);
```

---

### Feature Tests

Use `Log::fake()` to prevent log pollution during tests:

```php
use Illuminate\Support\Facades\Log;

public function test_user_creation()
{
    Log::fake();
    
    $this->post('/users', $userData);
    
    Log::assertLogged('info', fn ($message, $context) 
        => $message === 'User created' && $context['email'] === 'test@example.com'
    );
}
```

---

## 12. Compliance and Retention

### Retention Policies

| Log Type | Minimum Retention | Rationale |
|---|---|---|
| Security logs | 90 days | Incident investigation |
| Audit logs (configuration) | 1 year | Compliance requirement |
| Business logs | 30 days | Operational troubleshooting |
| Debug logs | 7 days | Development only |

### PII Handling

**Log only entity IDs, not full PII.**

❌ **Incorrect:**
```php
Log::info('Customer updated', [
    'customer' => $customer->toArray(), // Contains email, phone, address
]);
```

✅ **Correct:**
```php
Log::info('Customer updated', [
    'customer_id' => $customer->id,
    'actor_id' => auth()->id(),
]);
```

---

## 13. Migration Path

### Current State

- `LoginRequest` logs authentication events
- `SettingService` does not log configuration changes
- No structured security logging
- No business event logging

### Phase 1: Security Logging (Immediate)

- Add security channel to `config/logging.php`
- Log all authentication failures
- Log authorization denials in `UserPolicy`, `RolePolicy`, `PermissionPolicy`
- Log impersonation events (when implemented)

### Phase 2: Configuration Logging (Sprint 1.4)

- Add audit channel to `config/logging.php`
- Log all setting updates in `SettingService::set()`
- Log registry changes (if exposed via UI)

### Phase 3: Business Event Logging (Sprint 1.5+)

- Add business channel to `config/logging.php`
- Log all Domain Event dispatches in Services
- Correlate log entries with `TimelineEvent` and `ActivityLog`

### Phase 4: Operational Logging (Ongoing)

- Log critical workflow transitions
- Log external API failures
- Log background job failures

---

## 14. Architecture Decisions

| Decision | Rationale |
|---|---|
| **Use Laravel Log facade, not direct PSR-3** | Framework consistency; supports channels and context |
| **Log Domain Events separately from event dispatch** | Events drive automation; logs provide audit trail |
| **Synchronous logging for security events** | Cannot risk loss; must be written immediately |
| **PII by reference (ID), not value** | Compliance and log storage efficiency |
| **Multiple channels (security, audit, business)** | Separate retention policies and access controls |
| **Do not log at debug level in production** | Performance and log volume |

---

## 15. Examples

### Example: User Service Logging

```php
class UserService extends AbstractCrudService
{
    public function create(array $data): User
    {
        $user = parent::create($data);
        
        // Dispatch Domain Event
        event(new UserCreated($user->id, auth()->id()));
        
        // Log business event
        Log::info('User created', [
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => $user->status->value,
            'actor_id' => auth()->id(),
        ]);
        
        return $user;
    }
    
    public function suspend(User $user, string $reason): void
    {
        $oldStatus = $user->status;
        $user->status = UserStatus::Suspended;
        $user->save();
        
        // Dispatch Domain Event
        event(new UserUpdated($user->id, auth()->id()));
        
        // Log business event
        Log::notice('User suspended', [
            'user_id' => $user->id,
            'old_status' => $oldStatus->value,
            'new_status' => $user->status->value,
            'reason' => $reason,
            'actor_id' => auth()->id(),
        ]);
        
        // Create ActivityLog (structured audit)
        $user->activityLogs()->create([
            'actor_id' => auth()->id(),
            'action' => 'status_changed',
            'description' => "User suspended: {$reason}",
            'properties' => [
                'old_status' => $oldStatus->value,
                'new_status' => $user->status->value,
                'reason' => $reason,
            ],
        ]);
    }
}
```

---

### Example: Setting Service Logging

```php
class SettingService extends AbstractCrudService
{
    public function set(string $key, mixed $value, /* ... */): Setting
    {
        $setting = $this->findOrCreateSetting($key, $scope, $scopeId);
        $oldValue = $setting->value;
        
        $setting->value = $value;
        $setting->save();
        
        // Invalidate cache
        Cache::forget($cacheKey);
        
        // Dispatch Domain Event
        event(new SettingUpdated($setting->id, $key, auth()->id()));
        
        // Log configuration change to audit channel
        Log::channel('audit')->notice('Setting updated', [
            'setting_id' => $setting->id,
            'key' => $key,
            'scope' => $scope->value,
            'scope_id' => $scopeId,
            'old_value' => $oldValue,
            'new_value' => $value,
            'actor_id' => auth()->id(),
        ]);
        
        return $setting;
    }
}
```

---

### Example: Authorization Logging in Policy

```php
class UserPolicy
{
    public function update(User $actor, User $target): bool
    {
        $canUpdate = $actor->hasPermission('user.update');
        
        if (!$canUpdate) {
            Log::channel('security')->notice('Authorization denied', [
                'actor_id' => $actor->id,
                'permission' => 'user.update',
                'resource_type' => 'User',
                'resource_id' => $target->id,
                'ip' => request()->ip(),
            ]);
        }
        
        return $canUpdate;
    }
}
```

---

## 16. Summary

### Key Principles

1. **Use appropriate log levels** — `emergency` through `debug` per RFC 5424
2. **Log security events synchronously** — authentication, authorization, suspicious activity
3. **Log configuration changes to audit channel** — settings, registry, environment
4. **Log business events alongside Domain Event dispatch** — independent audit trail
5. **Use structured context** — entity_id, entity_type, actor_id, ip
6. **Never log sensitive data** — passwords, tokens, full PII
7. **Separate concerns** — Application Log, ActivityLog, TimelineEvent serve different audiences

### Quick Reference

| Event Type | Log Level | Channel | Example |
|---|---|---|---|
| Failed login | `warning` | `security` | Invalid credentials |
| Successful login | `info` | `default` | User authenticated |
| Authorization denied | `notice` | `security` | Missing permission |
| User created | `info` | `business` | Domain Event logged |
| Setting updated | `notice` | `audit` | Configuration change |
| Invoice published | `info` | `business` | Workflow milestone |
| API failure | `error` | `default` | Integration error |
| System failure | `critical` | `default` | Database unavailable |

---

**Next Steps:**

Sprint 1.4+: Implement logging across Services following this standard. Refactor existing ad-hoc logging to use channels and structured context.
