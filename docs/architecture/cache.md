# Platform Cache Architecture

**Architecture Version:** 1.0  
**Last Updated:** 2026-07-02  
**Status:** Authoritative

---

## 1. Overview

This document establishes the authoritative caching standard for the ISP Management Platform. All application caching must follow these conventions to ensure consistent performance optimization, cache coherence, and predictable invalidation behavior.

### Purpose

- **Performance Optimization**: Reduce database load for frequently accessed data
- **Scalability**: Support horizontal scaling with shared cache infrastructure
- **Consistency**: Ensure cache invalidation follows entity ownership boundaries
- **Predictability**: Standardize TTL and naming conventions across all services

### Scope

This standard covers:
- Application-level caching via Laravel's `Cache` facade
- Cache key naming conventions
- TTL (Time-To-Live) strategy by entity type
- Cache invalidation rules and ownership boundaries
- Tag-based cache management
- Service-layer cache responsibilities

**Out of Scope:**
- HTTP response caching (reverse proxy, CDN)
- Database query caching (MySQL query cache)
- OPcache / bytecode caching
- Session storage

---

## 2. Cache Driver Strategy

Laravel supports multiple cache drivers. Platform cache strategy varies by environment.

### Development & Local

**Driver:** `file`

**Rationale:**
- Zero external dependencies
- Fast iteration (clear cache via `php artisan cache:clear`)
- No setup required

### Staging & Testing

**Driver:** `redis` (dedicated database index)

**Rationale:**
- Mirrors production behavior
- Supports tagging for test isolation
- Fast cache clearing between test runs

### Production

**Driver:** `redis` (shared cluster)

**Rationale:**
- High performance (sub-millisecond reads)
- Supports horizontal scaling (multiple app servers)
- Native support for tagging, expiration, atomic operations
- Persistent across application restarts

**⚠ Important:** Production cache infrastructure must be separate from session storage and queue backends to allow independent scaling and failure isolation.

---

## 3. Cache Naming Convention

All cache keys must follow a consistent hierarchical naming convention.

### Pattern

```
{domain}:{entity}:{scope}:{identifier}[:{attribute}]
```

### Components

| Component | Description | Example |
|---|---|---|
| **domain** | Module or domain area | `identity`, `billing`, `subscription`, `settings` |
| **entity** | Entity type (singular) | `user`, `role`, `invoice`, `setting` |
| **scope** | Cache scope | `global`, `record`, `list`, `aggregate` |
| **identifier** | Entity ID or scope qualifier | Primary key, `all`, scope discriminator |
| **attribute** | Optional specific attribute | `permissions`, `roles`, `total` |

### Examples

```
settings:setting:global:all                     # All settings (global cache)
settings:setting:record:123                     # Single setting by ID
identity:user:record:456:permissions            # User's resolved permissions
identity:user:list:active                       # Active users list
billing:invoice:record:789:pdf                  # Invoice PDF blob
billing:subscription:aggregate:count            # Total subscription count
```

### Rules

1. **Lowercase only** — No uppercase characters in cache keys
2. **Colon separators** — Use `:` to delimit hierarchy levels
3. **No spaces** — Replace spaces with underscores or hyphens
4. **Stable identifiers** — Use primary keys, not slugs or names (entities can be renamed)
5. **Descriptive scope** — Make the cache scope obvious from the key

---

## 4. Cache Scope Classification

Every cached value belongs to one of four scopes.

### Global Cache

**Definition:** Single cached value shared across the entire application.

**Use When:**
- Data rarely changes
- Data is identical for all users and contexts
- Invalidation is straightforward (single point)

**Examples:**
- All active permissions (`identity:permission:global:all`)
- All active roles (`identity:role:global:all`)
- Setting registry entries (`settings:registry:global:all`)
- System configuration constants

**TTL:** Long (1-24 hours depending on volatility)

**Invalidation Trigger:** When any record in the entity set changes

```php
// Cache all active permissions
Cache::remember('identity:permission:global:all', 3600, function () {
    return Permission::where('status', 'active')->get();
});

// Invalidate when any permission changes
Cache::forget('identity:permission:global:all');
```

---

### Per-Record Cache

**Definition:** Cached value specific to a single entity record.

**Use When:**
- Data is entity-specific
- Individual record changes frequently
- Partial invalidation is important (don't clear unrelated records)

**Examples:**
- Single user with relationships (`identity:user:record:123`)
- Single invoice with line items (`billing:invoice:record:456`)
- Setting value by key and scope (`settings:setting:record:key:billing.due_days:scope:global`)

**TTL:** Medium (5-60 minutes depending on volatility)

**Invalidation Trigger:** When the specific record changes

```php
// Cache a single user with roles
Cache::remember("identity:user:record:{$userId}", 1800, function () use ($userId) {
    return User::with('roles.permissions')->findOrFail($userId);
});

// Invalidate when this specific user changes
Cache::forget("identity:user:record:{$userId}");
```

---

### List Cache

**Definition:** Cached collection of records matching specific criteria.

**Use When:**
- Filtered or ordered collections are expensive to compute
- List is used across multiple requests
- List can be invalidated as a unit

**Examples:**
- Active users list (`identity:user:list:active`)
- Unpaid invoices for customer (`billing:invoice:list:customer:123:unpaid`)
- Open tickets assigned to user (`support:ticket:list:assignee:456:open`)

**TTL:** Short (1-15 minutes)

**Invalidation Trigger:** When any record in the filtered set changes

```php
// Cache active users
Cache::remember('identity:user:list:active', 600, function () {
    return User::where('status', 'active')->get();
});

// Invalidate when any user's status changes
Cache::forget('identity:user:list:active');
```

**⚠ Caveat:** List caches require careful invalidation. If a user is created, updated, or deleted, all affected list caches must be cleared.

---

### Aggregate Cache

**Definition:** Cached computed value derived from multiple records.

**Use When:**
- Aggregation query is expensive (COUNT, SUM, AVG)
- Result changes infrequently relative to read frequency
- Stale data is acceptable within TTL window

**Examples:**
- Total subscription count (`subscription:subscription:aggregate:count`)
- Total outstanding balance (`billing:invoice:aggregate:outstanding`)
- Average monthly revenue (`billing:invoice:aggregate:monthly_avg`)

**TTL:** Short to Medium (5-30 minutes)

**Invalidation Trigger:** When any contributing record changes, or allow TTL expiration

```php
// Cache total active subscriptions
Cache::remember('subscription:subscription:aggregate:count', 900, function () {
    return Subscription::where('status', 'active')->count();
});

// Invalidate when subscription count changes
Cache::forget('subscription:subscription:aggregate:count');
```

**Alternative:** Use TTL-only strategy (no explicit invalidation) for aggregates where eventual consistency is acceptable.

---

## 5. TTL Strategy by Entity Type

Time-To-Live (TTL) values should reflect entity volatility and staleness tolerance.

### TTL Guidelines

| Entity Type | Volatility | TTL Range | Example Entities |
|---|---|---|---|
| **Configuration** | Very Low | 1-24 hours | Settings, Permissions, Roles, Registry Entries |
| **Reference Data** | Low | 1-6 hours | Packages, Service Areas, Clusters, OLTs |
| **Core Entities** | Medium | 15-60 minutes | Users, Customers, Subscriptions |
| **Transactional** | High | 5-15 minutes | Invoices, Payments, Tickets |
| **Operational** | Very High | 1-5 minutes | Collection Tasks, Provisioning Requests, Monitoring Events |
| **Aggregate** | N/A | 5-30 minutes | Counts, sums, averages |

### Specific TTL Values (Reference Implementation)

| Cache Key Pattern | TTL | Rationale |
|---|---|---|
| `settings:setting:*` | 3600s (1 hour) | Settings change via admin action only; infrequent |
| `identity:permission:global:all` | 3600s (1 hour) | Permissions are seeder-governed; extremely stable |
| `identity:role:global:all` | 3600s (1 hour) | Roles change infrequently via admin action |
| `identity:user:record:{id}` | 1800s (30 min) | User profile changes moderately; authorization checks frequent |
| `billing:invoice:record:{id}` | 900s (15 min) | Invoices are immutable after publish; safe to cache longer |
| `subscription:subscription:aggregate:count` | 900s (15 min) | Count changes with subscription lifecycle; eventual consistency OK |

**Principle:** Prefer shorter TTLs over complex invalidation logic. Caching is an optimization, not a source of truth.

---

## 6. Cache Invalidation Rules

Cache invalidation follows entity ownership boundaries and aggregate roots.

### Ownership-Based Invalidation

**Rule:** When an aggregate root or its owned children change, invalidate all caches within that ownership boundary.

**Aggregate Roots (from `entities.md`):**
- `Customer` owns: `Subscription`, `Invoice`, `Payment`, `Ticket`
- `Subscription` owns: `InstallationRequest`, `ProvisioningRequest`, `SuspensionCase`
- `Invoice` owns: `InvoiceItem`
- `OLT` owns: `ONU`, `MonitoringEvent`
- `User` owns: `UserSession`, `ImpersonationSession`

**Example:** When a `Subscription` status changes:

```php
// Invalidate subscription record cache
Cache::forget("subscription:subscription:record:{$subscription->id}");

// Invalidate customer's subscription list cache
Cache::forget("subscription:subscription:list:customer:{$subscription->customer_id}");

// Invalidate aggregate count cache
Cache::forget('subscription:subscription:aggregate:count');
```

---

### Service-Layer Responsibility

**Rule:** Services own cache invalidation for entities they manage.

**Pattern:**

```php
class UserService
{
    public function update(User $user, array $data): User
    {
        $user->update($data);
        
        // Invalidate per-record cache
        Cache::forget("identity:user:record:{$user->id}");
        
        // Invalidate list caches if status changed
        if ($user->wasChanged('status')) {
            Cache::forget('identity:user:list:active');
            Cache::forget('identity:user:list:suspended');
        }
        
        return $user->fresh();
    }
}
```

**Guidelines:**
- Invalidate immediately after database write
- Invalidate all affected cache scopes (record, list, aggregate)
- Do NOT delegate invalidation to event listeners (introduces timing issues)
- Invalidation is synchronous; caching is an optimization

---

### Cross-Service Invalidation

**Rule:** When Service A modifies data that affects Service B's cache, Service A must invalidate Service B's cache.

**Example:** `RoleService` assigns permission to role → must invalidate `UserService` permission caches

```php
class RoleService
{
    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
        
        // Invalidate role cache
        Cache::forget("identity:role:record:{$role->id}");
        
        // Invalidate all users with this role (their resolved permissions changed)
        $userIds = $role->users()->pluck('id');
        foreach ($userIds as $userId) {
            Cache::forget("identity:user:record:{$userId}:permissions");
        }
        
        // Invalidate global permission cache
        Cache::forget('identity:permission:global:all');
    }
}
```

**⚠ Caveat:** Cross-service invalidation creates coupling. Minimize by:
1. Using tags (see Tag-Based Invalidation)
2. Designing aggregates to minimize cross-boundary changes
3. Accepting eventual consistency where appropriate

---

### Tag-Based Invalidation

Laravel Cache supports tagging (Redis/Memcached only). Use tags to invalidate related caches atomically.

**Pattern:**

```php
// Store with tags
Cache::tags(['identity', 'user'])->put("identity:user:record:{$userId}", $user, 1800);
Cache::tags(['identity', 'user', "user:{$userId}"])->put("identity:user:record:{$userId}:permissions", $permissions, 1800);

// Invalidate all caches for a specific user
Cache::tags(["user:{$userId}"])->flush();

// Invalidate all identity-related caches
Cache::tags(['identity'])->flush();
```

**Tag Naming Convention:**
- Domain tags: `identity`, `billing`, `subscription`, `support`
- Entity tags: `user`, `role`, `invoice`, `ticket`
- Instance tags: `user:{id}`, `invoice:{id}`

**When to Use Tags:**
- Cross-service invalidation (flush by domain)
- Bulk invalidation (flush all user-related caches)
- Test isolation (flush by test tag)

**When NOT to Use Tags:**
- Simple per-record invalidation (direct `forget()` is faster)
- File cache driver (tags not supported)

---

## 7. Cache Consistency Patterns

### Read-Through Cache

**Pattern:** Cache miss triggers database read and cache write.

```php
$user = Cache::remember("identity:user:record:{$userId}", 1800, function () use ($userId) {
    return User::with('roles.permissions')->findOrFail($userId);
});
```

**Use When:** Standard pattern for most caching scenarios.

---

### Cache-Aside

**Pattern:** Application explicitly checks cache, then database, then writes to cache.

```php
$user = Cache::get("identity:user:record:{$userId}");

if (!$user) {
    $user = User::with('roles.permissions')->findOrFail($userId);
    Cache::put("identity:user:record:{$userId}", $user, 1800);
}
```

**Use When:** Fine-grained control over cache write conditions is needed.

---

### Write-Through Cache

**Pattern:** Write to database and cache simultaneously.

```php
$user->update($data);
Cache::put("identity:user:record:{$user->id}", $user, 1800);
```

**Use When:** Frequently read entity where cache must be immediately fresh after write.

**⚠ Caveat:** Can mask performance issues. Prefer invalidation + lazy read-through.

---

### Write-Behind (Deferred Invalidation)

**Pattern:** Write to database, queue cache invalidation.

```php
$user->update($data);
dispatch(new InvalidateUserCacheJob($user->id))->onQueue('cache');
```

**Use When:** Cache invalidation has cascading effects (e.g., must invalidate 1000s of records).

**⚠ Caveat:** Introduces eventual consistency. Use only when staleness is acceptable.

---

## 8. Service-Layer Cache Responsibilities

Services own all caching and invalidation for their managed entities.

### Service Method Patterns

#### `get()` / `find()` — Cache Read

```php
public function find(int $userId): User
{
    return Cache::remember(
        "identity:user:record:{$userId}",
        1800,
        fn() => User::with('roles.permissions')->findOrFail($userId)
    );
}
```

---

#### `create()` — No Cache (Fresh Data)

```php
public function create(array $data): User
{
    $user = User::create($data);
    
    // Invalidate list/aggregate caches
    Cache::forget('identity:user:list:active');
    Cache::forget('identity:user:aggregate:count');
    
    // Do NOT cache new record (it will be cached on first read)
    
    return $user;
}
```

**Rationale:** New record is unlikely to be read immediately. Let read-through cache it on demand.

---

#### `update()` — Invalidate

```php
public function update(User $user, array $data): User
{
    $user->update($data);
    
    // Invalidate per-record cache
    Cache::forget("identity:user:record:{$user->id}");
    
    // Invalidate derived caches
    if ($user->wasChanged('status')) {
        Cache::forget('identity:user:list:active');
        Cache::forget('identity:user:list:suspended');
    }
    
    return $user->fresh();
}
```

---

#### `delete()` — Invalidate

```php
public function delete(User $user): void
{
    $userId = $user->id;
    $user->delete();
    
    // Invalidate all user-related caches
    Cache::forget("identity:user:record:{$userId}");
    Cache::forget("identity:user:record:{$userId}:permissions");
    
    // Invalidate list/aggregate caches
    Cache::forget('identity:user:list:active');
    Cache::forget('identity:user:aggregate:count');
}
```

---

### When to Cache

| Operation | Cache? | Rationale |
|---|---|---|
| **Read frequently, write rarely** | ✅ Yes | Settings, permissions, roles, reference data |
| **Read rarely, write frequently** | ❌ No | Monitoring events, logs, audit trails |
| **Expensive JOIN or aggregation** | ✅ Yes | User with roles/permissions, invoice with items |
| **Simple primary key lookup** | ⚠️ Maybe | Only if read:write ratio is >10:1 |
| **User-specific data** | ✅ Yes | Per-user dashboards, resolved permissions |
| **Real-time data** | ❌ No | Current ONU signal, live monitoring metrics |

---

## 9. Reference Implementation: SettingService

The `SettingService` is the authoritative reference implementation for platform caching patterns.

### Cache Architecture

```php
class SettingService
{
    /**
     * Get a setting value with scope hierarchy fallback.
     * 
     * Cache key pattern: settings:setting:record:key:{key}:scope:{scope}:{scopeId}
     * TTL: 3600 seconds (1 hour)
     * Invalidation: Explicit in set() method
     */
    public function get(string $key, SettingScope $scope = null, int $scopeId = null): mixed
    {
        $scope = $scope ?? SettingScope::Global;
        $scopeId = $scopeId ?? 0;
        
        // Build hierarchical cache key
        $cacheKey = "settings:setting:record:key:{$key}:scope:{$scope->value}:{$scopeId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $scope, $scopeId) {
            return $this->resolveSetting($key, $scope, $scopeId);
        });
    }
    
    /**
     * Set a setting value and invalidate cache.
     */
    public function set(string $key, mixed $value, SettingScope $scope = null, int $scopeId = null): Setting
    {
        $scope = $scope ?? SettingScope::Global;
        $scopeId = $scopeId ?? 0;
        
        $setting = $this->findOrCreateSetting($key, $scope, $scopeId);
        $setting->value = $value;
        $setting->save();
        
        // Invalidate per-record cache
        $cacheKey = "settings:setting:record:key:{$key}:scope:{$scope->value}:{$scopeId}";
        Cache::forget($cacheKey);
        
        // If scope is not global, invalidate parent scope caches (cascade up hierarchy)
        if ($scope !== SettingScope::Global) {
            $this->invalidateParentScopes($key, $scope);
        }
        
        return $setting;
    }
}
```

### Key Patterns Demonstrated

1. **Hierarchical cache keys** — `key:{key}:scope:{scope}:{scopeId}`
2. **Read-through caching** — `Cache::remember()` with fallback to database
3. **Explicit invalidation** — `Cache::forget()` immediately after write
4. **Cascade invalidation** — Parent scope caches invalidated when child changes
5. **Long TTL** — 3600s for configuration data (low volatility)

### Scope Hierarchy Invalidation

```php
/**
 * Invalidate parent scope caches (Customer → Area/Cluster → Global).
 */
protected function invalidateParentScopes(string $key, SettingScope $scope): void
{
    match ($scope) {
        SettingScope::Customer => [
            // Invalidate parent cluster/area caches (if associated)
            // Invalidate global cache
            Cache::forget("settings:setting:record:key:{$key}:scope:global:0"),
        ],
        SettingScope::Cluster, SettingScope::Area => [
            // Invalidate global cache
            Cache::forget("settings:setting:record:key:{$key}:scope:global:0"),
        ],
        SettingScope::Global => [
            // Global is top-level; no parent to invalidate
        ],
    };
}
```

**Rationale:** When a customer-specific setting changes, the global fallback cache must be invalidated because the resolution path has changed.

---

## 10. Cache Invalidation Checklist

Use this checklist when implementing caching in a Service:

- [ ] **Per-record cache key** defined following naming convention
- [ ] **TTL** chosen based on entity volatility
- [ ] **Read method** uses `Cache::remember()` with read-through pattern
- [ ] **Create method** invalidates list/aggregate caches (not per-record)
- [ ] **Update method** invalidates per-record, list, and aggregate caches
- [ ] **Delete method** invalidates all entity-related caches
- [ ] **Cross-service impact** considered (does this change affect other services' caches?)
- [ ] **Tag strategy** defined if using Redis/Memcached
- [ ] **Aggregate invalidation** handled (counts, sums updated or allowed to expire)

---

## 11. Testing Guidelines

### Unit Tests

**Do NOT mock or assert cache calls in unit tests.** Caching is a performance optimization, not business logic.

**Exception:** Testing cache-specific Service methods (e.g., `clearUserCache()`)

```php
public function test_user_service_clears_cache_on_update()
{
    Cache::shouldReceive('forget')
        ->once()
        ->with("identity:user:record:123");
    
    $this->userService->update($user, ['name' => 'Updated']);
}
```

---

### Feature Tests

Use `Cache::flush()` in `setUp()` to ensure clean cache state:

```php
protected function setUp(): void
{
    parent::setUp();
    Cache::flush(); // Clear all caches before each test
}
```

**Alternative:** Use cache tags for test isolation:

```php
protected function setUp(): void
{
    parent::setUp();
    Cache::tags(['test'])->flush(); // Only clear test-tagged caches
}
```

---

### Performance Tests

Verify cache hit rates and TTL effectiveness:

```php
public function test_setting_is_cached()
{
    // First call: cache miss (database hit)
    $value1 = $this->settingService->get('billing.due_days');
    
    // Second call: cache hit (no database query)
    $value2 = $this->settingService->get('billing.due_days');
    
    $this->assertEquals($value1, $value2);
    $this->assertDatabaseQueryCount(1); // Only one query executed
}
```

---

## 12. Production Considerations

### Cache Warming

**Strategy:** Pre-populate critical caches on application boot or deployment.

```php
// In a scheduled command or deployment script
Artisan::command('cache:warm', function () {
    // Warm global caches
    Cache::remember('identity:permission:global:all', 3600, fn() => Permission::active()->get());
    Cache::remember('identity:role:global:all', 3600, fn() => Role::active()->get());
    Cache::remember('settings:registry:global:all', 3600, fn() => SettingRegistryEntry::all());
});
```

**When to Use:**
- Application deployment (warm cache before traffic arrives)
- Scheduled maintenance (rebuild expensive aggregates)

**When NOT to Use:**
- Per-user caches (too many to warm; let read-through handle it)
- Transactional data (cache on-demand only)

---

### Cache Stampede Prevention

**Problem:** Cache expires, 1000 concurrent requests all hit database simultaneously.

**Solution:** Use Laravel's cache locks:

```php
$user = Cache::lock("lock:user:{$userId}", 10)->block(5, function () use ($userId) {
    return Cache::remember("identity:user:record:{$userId}", 1800, function () use ($userId) {
        return User::with('roles.permissions')->findOrFail($userId);
    });
});
```

**How it works:**
1. First request acquires lock and populates cache
2. Concurrent requests wait (block) for lock release
3. After lock releases, cache is populated; subsequent requests hit cache

**Use When:** Expensive queries with high concurrency (permissions, settings, reports)

---

### Cache Monitoring

Track these metrics in production:

| Metric | Threshold | Action |
|---|---|---|
| **Cache hit rate** | <80% | Review TTL strategy; identify un-cached hot paths |
| **Cache memory usage** | >90% | Increase cache size or reduce TTL |
| **Eviction rate** | >10% of writes | Increase cache size or reduce data cached |
| **Invalidation frequency** | >1000/min | Review invalidation logic; may be over-invalidating |

---

## 13. Anti-Patterns

### ❌ Caching in Controllers

**Wrong:**
```php
public function show(User $user)
{
    $user = Cache::remember("user:{$user->id}", 1800, fn() => $user->load('roles'));
    return view('users.show', compact('user'));
}
```

**Right:**
```php
public function show(User $user)
{
    $userData = $this->userService->findForShow($user->id);
    return view('users.show', $userData);
}
```

**Rationale:** Controllers delegate; Services own caching.

---

### ❌ Caching in Models

**Wrong:**
```php
class User extends Model
{
    public function getCachedPermissionsAttribute()
    {
        return Cache::remember("user:{$this->id}:permissions", 1800, fn() => $this->permissions);
    }
}
```

**Right:**
```php
class UserService
{
    public function getPermissions(User $user): Collection
    {
        return Cache::remember("identity:user:record:{$user->id}:permissions", 1800, function () use ($user) {
            return $user->permissions;
        });
    }
}
```

**Rationale:** Models are data structures; Services own business logic including caching.

---

### ❌ Forgetting to Invalidate

**Wrong:**
```php
public function update(User $user, array $data): User
{
    $user->update($data);
    return $user;
}
```

**Right:**
```php
public function update(User $user, array $data): User
{
    $user->update($data);
    
    Cache::forget("identity:user:record:{$user->id}");
    
    if ($user->wasChanged('status')) {
        Cache::forget('identity:user:list:active');
    }
    
    return $user;
}
```

**Rationale:** Stale cache is worse than no cache. Always invalidate on write.

---

### ❌ Caching Database Query Builder

**Wrong:**
```php
Cache::remember('users', 3600, fn() => User::query());
```

**Right:**
```php
Cache::remember('users', 3600, fn() => User::all());
```

**Rationale:** Query builders are not serializable. Cache the result (Collection or Model).

---

### ❌ Over-Caching

**Wrong:**
```php
// Caching every single database query
$user = Cache::remember("user:{$id}", 3600, fn() => User::find($id));
$invoice = Cache::remember("invoice:{$id}", 3600, fn() => Invoice::find($id));
$ticket = Cache::remember("ticket:{$id}", 3600, fn() => Ticket::find($id));
```

**Right:**
```php
// Cache only frequently accessed, expensive queries
$user = User::find($id); // Simple PK lookup; don't cache
$userWithPermissions = Cache::remember("user:{$id}:permissions", 1800, function () use ($id) {
    return User::with('roles.permissions', 'directPermissions')->findOrFail($id);
});
```

**Rationale:** Caching has overhead (serialize, network, deserialize). Only cache when benefit > cost.

---

## 14. Cache Refresh Rules

Document when cache **must** be refreshed (invalidated) for each entity.

### User Cache Refresh Triggers

| Trigger | Affected Caches |
|---|---|
| User profile updated | `identity:user:record:{id}` |
| User status changed | `identity:user:record:{id}`, `identity:user:list:active`, `identity:user:list:suspended` |
| Role assigned/removed | `identity:user:record:{id}:permissions` |
| Permission directly granted | `identity:user:record:{id}:permissions` |
| User deleted | All `identity:user:*:{id}:*` |

---

### Role Cache Refresh Triggers

| Trigger | Affected Caches |
|---|---|
| Role created | `identity:role:global:all` |
| Role updated | `identity:role:record:{id}`, `identity:role:global:all` |
| Permission assigned to role | `identity:role:record:{id}`, all `identity:user:record:{userId}:permissions` for users with this role |
| Role deleted | `identity:role:global:all`, all `identity:user:record:{userId}:permissions` for users with this role |

---

### Setting Cache Refresh Triggers

| Trigger | Affected Caches |
|---|---|
| Setting value updated | `settings:setting:record:key:{key}:scope:{scope}:{scopeId}`, parent scope caches |
| Setting status changed | `settings:setting:record:key:{key}:scope:{scope}:{scopeId}` |
| Registry entry modified | All `settings:setting:*` for affected key |

---

### Invoice Cache Refresh Triggers

| Trigger | Affected Caches |
|---|---|
| Invoice published | `billing:invoice:record:{id}`, `billing:invoice:list:customer:{customerId}:unpaid` |
| Payment allocated to invoice | `billing:invoice:record:{id}`, `billing:invoice:list:customer:{customerId}:unpaid` |
| Invoice voided | `billing:invoice:record:{id}`, `billing:invoice:list:customer:{customerId}:unpaid` |

---

### Subscription Cache Refresh Triggers

| Trigger | Affected Caches |
|---|---|
| Subscription created | `subscription:subscription:list:customer:{customerId}`, `subscription:subscription:aggregate:count` |
| Subscription status changed | `subscription:subscription:record:{id}`, `subscription:subscription:list:customer:{customerId}`, aggregate caches |
| Subscription package changed | `subscription:subscription:record:{id}` |
| Subscription terminated | `subscription:subscription:record:{id}`, `subscription:subscription:list:customer:{customerId}`, aggregate caches |

---

## 15. Migration Path

### Current State

- `SettingService` uses caching (`Cache::remember()`, 3600s TTL, explicit invalidation)
- No other services implement caching
- No standardized naming convention
- No tag strategy

### Phase 1: Documentation & Standards (Sprint 1.3 — Complete)

- ✅ Create `docs/architecture/cache.md`
- ✅ Define naming convention, TTL strategy, invalidation rules
- ✅ Document `SettingService` as reference implementation

### Phase 2: Identity & Access Caching (Sprint 1.4)

- Implement caching in `UserService` (per-record, permissions)
- Implement caching in `RoleService` (global, per-record)
- Implement caching in `PermissionService` (global)
- Add cache invalidation to all write operations
- Add cache warming command for global caches

### Phase 3: Core Entity Caching (Sprint 1.5+)

- Implement caching in `CustomerService`
- Implement caching in `SubscriptionService`
- Implement caching in `PackageService`
- Add aggregate caching (subscription counts, revenue totals)

### Phase 4: Transactional Caching (Sprint 1.6+)

- Implement selective caching in `InvoiceService` (published invoices only)
- Implement selective caching in `PaymentService` (completed payments only)
- Add PDF blob caching for invoices
- Add list caching for unpaid invoices per customer

### Phase 5: Optimization & Monitoring (Sprint 1.7+)

- Implement tag-based invalidation for Redis driver
- Add cache hit rate monitoring
- Add cache stampede prevention for hot paths
- Optimize TTL values based on production metrics

---

## 16. Architecture Decisions

| Decision | Rationale |
|---|---|
| **Services own caching, not Models or Controllers** | Enforces architectural layering; single responsibility |
| **Explicit invalidation over TTL-only** | Predictable consistency; no stale data surprises |
| **Hierarchical cache key naming** | Discoverability; supports partial invalidation |
| **Redis for production** | Performance, scalability, tagging support |
| **Per-record > Global for volatile entities** | Minimizes invalidation blast radius |
| **Short TTL for transactional data** | Reduces staleness window; simpler invalidation logic |
| **No caching in read-rarely scenarios** | Overhead exceeds benefit; database is fast for occasional reads |

---

## 17. Summary

### Key Principles

1. **Services own all caching and invalidation** — Models are data, Controllers orchestrate
2. **Follow naming convention** — `{domain}:{entity}:{scope}:{identifier}[:{attribute}]`
3. **Choose TTL by volatility** — Configuration: hours; Core: 15-60min; Transactional: 5-15min
4. **Invalidate immediately after write** — Synchronous, explicit, complete
5. **Cache expensive operations only** — Simple PK lookups rarely benefit from caching
6. **Use tags for cross-service invalidation** — Flush by domain/entity/instance
7. **Test with clean cache state** — `Cache::flush()` in `setUp()`

### Quick Reference

| Cache Scope | TTL Range | Invalidation |
|---|---|---|
| **Global** | 1-24 hours | On any entity change |
| **Per-Record** | 5-60 minutes | On specific record change |
| **List** | 1-15 minutes | On any record in list change |
| **Aggregate** | 5-30 minutes | On contributing record change or TTL |

### Reference Implementation

See: `app/Services/Settings/SettingService.php`

- Cache key: `settings:setting:record:key:{key}:scope:{scope}:{scopeId}`
- TTL: 3600s
- Pattern: Read-through with explicit invalidation
- Special: Cascade invalidation up scope hierarchy

---

**Next Steps:**

Sprint 1.4+: Implement caching in Identity & Access services following this standard. Use `SettingService` as the reference pattern.
