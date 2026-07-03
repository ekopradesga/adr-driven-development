# Platform Queue Architecture

**Architecture Version:** 1.0  
**Last Updated:** 2026-07-02  
**Status:** Authoritative

---

## 1. Overview

This document establishes the authoritative queue architecture for the ISP Management Platform. All asynchronous job processing must follow these conventions to ensure reliable background execution, predictable retry behavior, and operational maintainability.

### Purpose

- **Asynchronous Processing**: Offload long-running operations from HTTP request/response cycle
- **Reliability**: Ensure jobs are executed with appropriate retry and timeout policies
- **Scalability**: Support horizontal scaling with dedicated queue workers
- **Observability**: Standardize queue naming and failed job handling

### Scope

This standard covers:
- Queue naming conventions
- Job retry strategy and exponential backoff
- Timeout policies by job category
- Queue priority ordering
- Idempotency requirements and patterns
- Dead letter queue (failed job) handling
- Job dispatching patterns from Services

**Out of Scope:**
- Queue driver selection (Redis, database, SQS)
- Worker deployment and infrastructure provisioning
- Job monitoring dashboards (external tools)

---

## 2. Queue Driver Strategy

Laravel supports multiple queue drivers. Platform queue strategy varies by environment.

### Development & Local

**Driver:** `sync` (executes immediately, no background processing)

**Rationale:**
- Simplifies debugging (jobs run synchronously in request)
- No external dependencies required
- Immediate feedback on job failures

**⚠ Limitation:** Does not test queue behavior (retry, timeout, delayed dispatch). Use `database` driver when testing queue-specific logic.

---

### Staging & Testing

**Driver:** `database` (jobs stored in `jobs` table)

**Rationale:**
- Zero external dependencies (uses existing database)
- Easy to inspect queued jobs via database queries
- Mirrors production behavior for retry/timeout testing
- Simple to reset queue state between tests

**Migration Required:**
```bash
php artisan queue:table
php artisan migrate
```

---

### Production

**Driver:** `redis` (recommended) or `database`

**Rationale:**
- **Redis**: High throughput, low latency, atomic operations, supports priority queues natively
- **Database**: Acceptable for low-to-medium volume; easier operational setup; avoids Redis infrastructure dependency

**Redis Advantages:**
- Faster job dispatch and consumption (sub-millisecond)
- Better horizontal scaling (multiple workers, multiple Redis nodes)
- Native support for delayed jobs and priority queues

**Database Acceptable When:**
- Job volume < 100/minute
- Acceptable latency ~50-100ms per job
- Simpler infrastructure preferred

**⚠ Important:** Production queue infrastructure must be monitored for:
- Queue depth (pending jobs count)
- Failed job rate
- Worker health (alive/dead)
- Job processing time (p50, p95, p99)

---

## 3. Queue Naming Convention

All queues must follow a hierarchical naming convention reflecting priority and domain.

### Pattern

```
{priority}[-{domain}]
```

### Priority Levels

| Priority | Queue Name | Description | Examples |
|---|---|---|---|
| **Critical** | `critical` | System-critical operations; must execute immediately | Password reset emails, payment processing |
| **High** | `high` | Time-sensitive business operations | Invoice generation, subscription activation |
| **Default** | `default` | Standard background jobs | Email notifications, report generation |
| **Low** | `low` | Non-urgent, resource-intensive jobs | Data exports, bulk operations, cleanup |

### Domain-Specific Queues

Optionally append domain name for isolation:

```
default-notifications
default-provisioning
low-cleanup
high-billing
```

**Use When:**
- Domain has high job volume (isolate from other domains)
- Domain requires dedicated worker pool
- SLA requirements differ by domain

---

### Queue Examples

| Queue Name | Priority | Domain | Use Case |
|---|---|---|---|
| `critical` | Critical | All | Password reset, payment processing, account lockout |
| `high` | High | All | Invoice generation, subscription lifecycle |
| `default` | Default | All | Standard email notifications, audit logs |
| `default-notifications` | Default | Notifications | Isolate notification volume from other jobs |
| `default-provisioning` | Default | Provisioning | ONU provisioning, service activation |
| `low` | Low | All | Report generation, data exports |
| `low-cleanup` | Low | Cleanup | Log rotation, cache warming, orphan cleanup |

---

### Worker Configuration

Configure workers to process queues by priority:

```bash
# High-priority worker (critical, high, default only)
php artisan queue:work --queue=critical,high,default

# Standard worker (all queues by priority)
php artisan queue:work --queue=critical,high,default,low

# Domain-specific worker (notifications only)
php artisan queue:work --queue=critical,default-notifications
```

**Priority Processing:** Laravel processes queues left-to-right. Jobs in `critical` always execute before `high`, `high` before `default`, etc.

---

## 4. Retry Strategy

All jobs must define explicit retry policy. Default: 3 attempts with exponential backoff.

### Retry Count by Job Category

| Job Category | Max Attempts | Rationale |
|---|---|---|
| **Critical** | 5 | High-value operations; retry aggressively before failing |
| **External API Calls** | 3-5 | Network failures are transient; retry with backoff |
| **Email Delivery** | 3 | SMTP failures often transient; avoid excessive retries |
| **Data Processing** | 2 | Logic errors rarely fix themselves; fail fast |
| **Cleanup Jobs** | 1 | Idempotent; next scheduled run will retry |

### Exponential Backoff

Use `backoff()` method for exponential delay between attempts:

```php
class ProcessPaymentJob implements ShouldQueue
{
    public $tries = 5;
    
    public function backoff(): array
    {
        return [10, 30, 60, 300]; // 10s, 30s, 1m, 5m
    }
}
```

**Pattern:**
- **Attempt 1**: Immediate
- **Attempt 2**: +10 seconds
- **Attempt 3**: +30 seconds
- **Attempt 4**: +1 minute
- **Attempt 5**: +5 minutes
- **After 5 attempts**: Move to failed jobs table

**Why Exponential Backoff:**
- Transient failures often resolve quickly (database deadlock, network hiccup)
- Longer delays allow external services to recover (API rate limits, SMTP throttling)
- Prevents queue worker from busy-looping on failing job

---

### Retry on Specific Exceptions

Use `retryUntil()` for time-based retry limit:

```php
public function retryUntil(): DateTime
{
    return now()->addHours(2); // Retry for max 2 hours
}
```

**Use When:**
- Job must complete within SLA window (e.g., invoice must be generated within 24 hours)
- External service has known downtime window

---

### No Retry for Fatal Errors

Some exceptions should NOT trigger retry:

```php
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendInvoiceEmailJob implements ShouldQueue
{
    public $tries = 3;
    
    public function failed(Throwable $exception): void
    {
        // Log to ActivityLog, alert admin
        Log::channel('business')->error('Invoice email failed permanently', [
            'invoice_id' => $this->invoice->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

**Do NOT retry when:**
- Data validation error (invalid email address, missing required field)
- Authorization failure (user no longer has permission)
- Business rule violation (invoice already voided, subscription already terminated)

**Pattern:** Catch specific exceptions in `handle()`, log to `ActivityLog`, do NOT re-throw.

---

## 5. Timeout Strategy

All jobs must define explicit timeout. Default: 60 seconds.

### Timeout by Job Category

| Job Category | Timeout | Rationale |
|---|---|---|
| **Email Delivery** | 30s | SMTP handshake + send typically <10s; 30s allows retry buffer |
| **External API Calls** | 60s | HTTP timeout (30s) + processing + retry buffer |
| **PDF Generation** | 120s | Large invoices with 100+ line items may take 30-60s |
| **Data Export** | 300s (5m) | CSV/Excel generation for 1000s of records |
| **Provisioning (ONU)** | 180s (3m) | OLT SNMP calls may take 30-60s; allow multiple retries |
| **Cleanup Jobs** | 600s (10m) | Bulk delete operations may process 1000s of records |

### Implementation

```php
class GenerateInvoicePdfJob implements ShouldQueue
{
    public $tries = 3;
    public $timeout = 120; // 2 minutes
    
    public function handle(): void
    {
        // Job will be killed if execution exceeds 120 seconds
        $this->invoice->generatePdf();
    }
}
```

**⚠ Worker Timeout Must Exceed Job Timeout:**

```bash
# Worker timeout should be max job timeout + 10 seconds
php artisan queue:work --timeout=130
```

If worker timeout ≤ job timeout, worker may kill job before it completes naturally.

---

### Timeout Handling

When timeout is reached:
1. Job process is killed (SIGTERM)
2. Job is marked as failed
3. If `$tries` remain, job is retried after backoff delay
4. If no `$tries` remain, job moves to failed jobs table
5. `failed()` method is called (if defined)

```php
public function failed(Throwable $exception): void
{
    if ($exception instanceof \Illuminate\Queue\MaxAttemptsExceededException) {
        Log::error('Job timed out after max attempts', [
            'job' => static::class,
            'timeout' => $this->timeout,
        ]);
    }
}
```

---

## 6. Queue Priority

Laravel processes queues in order specified to `queue:work`.

### Priority Ordering

```bash
php artisan queue:work --queue=critical,high,default,low
```

**Processing Behavior:**
1. Worker checks `critical` queue first
2. If `critical` has jobs, process one job
3. If `critical` empty, check `high` queue
4. If `high` has jobs, process one job
5. Continue pattern: `default` → `low`
6. After processing one job, repeat from step 1

**Starvation Risk:** If `critical` queue always has jobs, `low` queue may never process.

**Mitigation:**
- Deploy dedicated workers for `low` queue only
- Monitor queue depth; alert if `low` queue exceeds threshold
- Design low-priority jobs to be idempotent (safe to delay/skip)

---

### Priority Guidelines

| Queue | Job Types | SLA |
|---|---|---|
| **critical** | Password reset, payment processing, security events | <5 seconds |
| **high** | Invoice generation, subscription activation, ticket creation | <30 seconds |
| **default** | Email notifications, audit logs, cache invalidation | <2 minutes |
| **low** | Report generation, data export, cleanup jobs | <30 minutes |

---

## 7. Idempotency

All jobs MUST be idempotent: safe to execute multiple times with identical outcome.

### Why Idempotency Matters

**Scenarios Causing Duplicate Execution:**
- Worker crash after job processing but before marking complete
- Network partition during job acknowledgment
- Manual job retry by operator
- Job dispatched multiple times due to application bug

**If Job Is NOT Idempotent:**
- Email sent twice to customer
- Payment processed twice
- Invoice generated twice (duplicate invoice number)
- ONU provisioned twice (configuration conflict)

---

### Idempotency Patterns

#### 1. Unique Job Constraint

Use `ShouldBeUnique` interface to prevent duplicate dispatch:

```php
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ProcessPaymentJob implements ShouldQueue, ShouldBeUnique
{
    public function __construct(public int $paymentId) {}
    
    public function uniqueId(): string
    {
        return "process-payment-{$this->paymentId}";
    }
    
    public function uniqueFor(): int
    {
        return 3600; // Lock for 1 hour
    }
}
```

**Behavior:**
- If job with same `uniqueId()` already queued or processing, new dispatch is ignored
- Lock released after job completes or `uniqueFor()` expires
- Cache-based lock (requires Redis or Memcached)

**Use When:** Job must execute exactly once per entity within time window.

---

#### 2. Database Unique Constraint

Use unique constraint to prevent duplicate side effects:

```php
class SendInvoiceEmailJob implements ShouldQueue
{
    public function handle(): void
    {
        // Attempt to insert notification delivery record
        try {
            NotificationDeliveryAttempt::create([
                'notification_id' => $this->notification->id,
                'channel' => 'email',
                'recipient' => $this->invoice->customer->email,
                'status' => 'sent',
            ]);
            
            Mail::to($this->invoice->customer)->send(new InvoiceEmail($this->invoice));
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Email already sent; idempotent exit
            Log::info('Invoice email already sent', ['invoice_id' => $this->invoice->id]);
            return;
        }
    }
}
```

**Pattern:**
1. Insert record with unique constraint (`notification_id`, `channel`, `recipient`)
2. If insert succeeds, perform side effect (send email)
3. If insert fails (duplicate), side effect already happened; exit gracefully

---

#### 3. Check-Then-Act with Locking

Use database locks to prevent race conditions:

```php
class ActivateSubscriptionJob implements ShouldQueue
{
    public function handle(): void
    {
        DB::transaction(function () {
            $subscription = Subscription::lockForUpdate()->find($this->subscriptionId);
            
            if ($subscription->status === SubscriptionStatus::Active) {
                // Already activated; idempotent exit
                return;
            }
            
            $subscription->status = SubscriptionStatus::Active;
            $subscription->activated_at = now();
            $subscription->save();
            
            event(new SubscriptionActivated($subscription->id, auth()->id()));
        });
    }
}
```

**Pattern:**
1. Lock row with `lockForUpdate()`
2. Check current state
3. If already in target state, exit (idempotent)
4. If not, perform state transition

---

#### 4. Immutable Operations

Prefer operations that are naturally idempotent:

```php
// ✅ Idempotent: SET to specific value
Cache::put('setting:billing.due_days', 14, 3600);

// ❌ Not idempotent: INCREMENT
Cache::increment('invoice:count'); // Running twice gives wrong result

// ✅ Idempotent: Database UPDATE with WHERE clause
DB::table('subscriptions')
    ->where('id', $subscriptionId)
    ->where('status', 'pending')
    ->update(['status' => 'active']); // Only updates if status is 'pending'
```

---

### Idempotency Checklist

When designing a job, verify:

- [ ] Job can be safely executed multiple times without duplicate side effects
- [ ] External API calls are idempotent (use idempotency key if API supports)
- [ ] Database writes check current state before modifying
- [ ] Email/SMS sends are tracked to prevent duplicates
- [ ] File operations check for existence before creating
- [ ] Payment processing includes deduplication logic

---

## 8. Dead Letter Queue (Failed Jobs)

All failed jobs are moved to `failed_jobs` table after exhausting retry attempts.

### Failed Job Handling

#### Automatic Failure

Job fails permanently when:
- Max attempts (`$tries`) exceeded
- `retryUntil()` deadline passed
- Timeout exceeded and no retries remain
- Job throws exception and `$tries = 1`

#### Migration

```bash
php artisan queue:failed-table
php artisan migrate
```

Creates `failed_jobs` table with columns:
- `id` — Auto-increment PK
- `uuid` — Unique identifier
- `connection` — Queue connection name
- `queue` — Queue name
- `payload` — Serialized job data (JSON)
- `exception` — Exception message and stack trace
- `failed_at` — Timestamp

---

### Failed Job Workflow

```
Job Fails → Max Retries Exceeded → Insert into failed_jobs
                                  → Call job->failed()
                                  → Send alert (if configured)
```

---

### Implementing `failed()` Method

All jobs should implement `failed()` to handle permanent failure:

```php
class GenerateInvoiceJob implements ShouldQueue
{
    public $tries = 3;
    
    public function handle(): void
    {
        // Generate invoice PDF
    }
    
    public function failed(Throwable $exception): void
    {
        // Log to business log channel
        Log::channel('business')->error('Invoice generation failed permanently', [
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->invoice->customer_id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
        
        // Create ActivityLog entry
        $this->invoice->activityLogs()->create([
            'actor_id' => null,
            'action' => 'generation_failed',
            'description' => 'Invoice PDF generation failed: ' . $exception->getMessage(),
        ]);
        
        // Optional: Send alert to operations team
        // Notification::send($admins, new JobFailedNotification($this));
    }
}
```

---

### Failed Job Management Commands

```bash
# List all failed jobs
php artisan queue:failed

# Retry specific failed job by UUID
php artisan queue:retry <uuid>

# Retry all failed jobs
php artisan queue:retry all

# Delete specific failed job
php artisan queue:forget <uuid>

# Delete all failed jobs
php artisan queue:flush
```

---

### Production Failed Job Monitoring

**Metrics to Track:**
- Failed job count by queue
- Failed job count by job class
- Failed job age (oldest failed job timestamp)
- Retry success rate

**Alerting Thresholds:**
- Alert if failed job count > 10
- Alert if failed job age > 24 hours
- Alert if specific job class fails > 3 times in 1 hour

**Response Process:**
1. Investigate root cause (check `exception` column)
2. Fix underlying issue (code bug, config error, external service down)
3. Retry failed jobs: `php artisan queue:retry all`
4. Monitor for re-failure

---

## 9. Job Dispatching Patterns

Jobs are dispatched from Services, never from Controllers.

### Pattern: Dispatch After Database Commit

```php
class UserService
{
    public function create(array $data): User
    {
        $user = User::create($data);
        
        // Dispatch after transaction commits
        SendWelcomeEmailJob::dispatch($user)->afterCommit();
        
        return $user;
    }
}
```

**Why `afterCommit()`:**
- Ensures database write succeeds before job dispatches
- If transaction rolls back, job is not dispatched
- Prevents job from executing before entity exists

---

### Pattern: Dispatch to Specific Queue

```php
class InvoiceService
{
    public function publish(Invoice $invoice): void
    {
        $invoice->status = InvoiceStatus::Published;
        $invoice->published_at = now();
        $invoice->save();
        
        // Dispatch to high-priority queue
        GenerateInvoicePdfJob::dispatch($invoice)
            ->onQueue('high')
            ->afterCommit();
        
        SendInvoiceEmailJob::dispatch($invoice)
            ->onQueue('default-notifications')
            ->afterCommit();
    }
}
```

---

### Pattern: Delayed Dispatch

```php
class CollectionService
{
    public function scheduleReminder(Invoice $invoice): void
    {
        // Send reminder 7 days after due date
        SendPaymentReminderJob::dispatch($invoice)
            ->delay($invoice->due_date->addDays(7))
            ->onQueue('default-notifications')
            ->afterCommit();
    }
}
```

---

### Pattern: Chain Jobs

```php
class SubscriptionService
{
    public function activate(Subscription $subscription): void
    {
        $subscription->status = SubscriptionStatus::Active;
        $subscription->save();
        
        // Execute jobs sequentially
        Bus::chain([
            new ProvisionOnuJob($subscription->onu_id),
            new GenerateActivationEmailJob($subscription->id),
            new UpdateCustomerDashboardJob($subscription->customer_id),
        ])->onQueue('default-provisioning')->dispatch();
    }
}
```

**Use When:** Jobs must execute in specific order and each depends on previous success.

---

### Pattern: Batch Jobs

```php
class BillingService
{
    public function generateMonthlyInvoices(): void
    {
        $subscriptions = Subscription::where('status', 'active')->cursor();
        
        $batch = Bus::batch([])
            ->name('Monthly Invoice Generation')
            ->onQueue('high')
            ->allowFailures()
            ->dispatch();
        
        foreach ($subscriptions as $subscription) {
            $batch->add(new GenerateInvoiceJob($subscription->id));
        }
    }
}
```

**Use When:** Processing large dataset where partial failure is acceptable.

---

## 10. Job Naming Convention

All job classes must follow naming convention: `{Action}{Entity}Job`

### Examples

| Job Class | Pattern | Description |
|---|---|---|
| `SendWelcomeEmailJob` | Send{Resource}Job | Notification delivery |
| `GenerateInvoicePdfJob` | Generate{Resource}Job | Document generation |
| `ProcessPaymentJob` | Process{Resource}Job | Transaction processing |
| `ProvisionOnuJob` | Provision{Resource}Job | Infrastructure provisioning |
| `SyncCustomerDataJob` | Sync{Resource}Job | External integration |
| `CleanupOldLogsJob` | Cleanup{Resource}Job | Maintenance task |
| `UpdateSubscriptionCacheJob` | Update{Resource}Job | Cache invalidation |

---

## 11. Job Organization

Jobs are organized by domain in `app/Jobs/{Domain}/`:

```
app/Jobs/
├── Identity/
│   ├── SendWelcomeEmailJob.php
│   ├── SendPasswordResetEmailJob.php
│   └── SyncUserPermissionCacheJob.php
├── Billing/
│   ├── GenerateInvoiceJob.php
│   ├── GenerateInvoicePdfJob.php
│   ├── SendInvoiceEmailJob.php
│   └── ProcessPaymentJob.php
├── Provisioning/
│   ├── ProvisionOnuJob.php
│   ├── DeprovisionOnuJob.php
│   └── SyncOnuConfigJob.php
├── Notifications/
│   ├── SendEmailNotificationJob.php
│   ├── SendSmsNotificationJob.php
│   └── SendWhatsAppNotificationJob.php
└── Maintenance/
    ├── CleanupOldLogsJob.php
    ├── WarmCacheJob.php
    └── GenerateMonthlyReportJob.php
```

---

## 12. Testing Guidelines

### Unit Testing Jobs

Test job logic independently from queue infrastructure:

```php
use Illuminate\Support\Facades\Queue;

public function test_send_welcome_email_job()
{
    Queue::fake();
    
    $user = User::factory()->create();
    
    SendWelcomeEmailJob::dispatch($user);
    
    Queue::assertPushed(SendWelcomeEmailJob::class, function ($job) use ($user) {
        return $job->user->id === $user->id;
    });
}
```

---

### Testing Job Execution

Execute job synchronously to test `handle()` logic:

```php
public function test_generate_invoice_pdf()
{
    $invoice = Invoice::factory()->create();
    
    $job = new GenerateInvoicePdfJob($invoice);
    $job->handle();
    
    $this->assertNotNull($invoice->fresh()->pdf_path);
}
```

---

### Testing Job Retry

Test that job retries on transient failure:

```php
public function test_payment_job_retries_on_network_error()
{
    $payment = Payment::factory()->create();
    
    // Mock external API to fail once, then succeed
    Http::fake([
        'payment-gateway.com/*' => Http::sequence()
            ->push(['error' => 'timeout'], 500)
            ->push(['status' => 'success'], 200),
    ]);
    
    $job = new ProcessPaymentJob($payment);
    
    // First attempt fails
    try {
        $job->handle();
        $this->fail('Expected exception');
    } catch (\Exception $e) {
        $this->assertEquals(1, $job->attempts());
    }
    
    // Second attempt succeeds
    $job->handle();
    $this->assertEquals('completed', $payment->fresh()->status);
}
```

---

### Testing Idempotency

Verify job can be executed multiple times safely:

```php
public function test_activate_subscription_job_is_idempotent()
{
    $subscription = Subscription::factory()->create(['status' => 'pending']);
    
    $job = new ActivateSubscriptionJob($subscription->id);
    
    // Execute twice
    $job->handle();
    $job->handle();
    
    // Verify subscription activated only once
    $this->assertEquals('active', $subscription->fresh()->status);
    $this->assertEquals(1, TimelineEvent::where('subscription_id', $subscription->id)->count());
}
```

---

## 13. Production Considerations

### Worker Configuration

```bash
# Supervisor configuration for Laravel queue workers
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --queue=critical,high,default,low --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/worker.log
stopwaitsecs=3600
```

**Configuration Explanation:**
- `--queue=critical,high,default,low` — Process queues by priority
- `--sleep=3` — Sleep 3 seconds when no jobs available (reduces CPU usage)
- `--tries=3` — Max attempts (can be overridden by job `$tries`)
- `--max-time=3600` — Worker restarts after 1 hour (prevents memory leaks)
- `numprocs=4` — Run 4 worker processes in parallel

---

### Worker Scaling

**Vertical Scaling:** Increase `numprocs` to run more workers per server.

**Horizontal Scaling:** Deploy workers on multiple servers; all connect to shared Redis queue.

**Auto-Scaling Triggers:**
- Queue depth > 100 jobs: Scale up
- Queue depth < 10 jobs for 5 minutes: Scale down
- Worker CPU usage > 80%: Scale up

---

### Monitoring Metrics

| Metric | Command | Alert Threshold |
|---|---|---|
| Queue depth | `redis-cli LLEN queues:default` | > 1000 |
| Failed jobs | `SELECT COUNT(*) FROM failed_jobs` | > 10 |
| Worker health | `ps aux | grep queue:work` | < 1 worker |
| Job processing time | (custom logging in jobs) | p95 > 30s |

---

### Graceful Shutdown

```bash
# Send SIGTERM to worker (completes current job before stopping)
php artisan queue:restart

# Force stop all workers immediately
pkill -9 -f "queue:work"
```

**Deployment Process:**
1. Queue pending jobs should complete before deployment
2. Send `queue:restart` signal
3. Workers complete current job and exit
4. Supervisor restarts workers with new code
5. New workers begin processing queue

---

## 14. Anti-Patterns

### ❌ Dispatching Jobs from Controllers

**Wrong:**
```php
public function store(Request $request)
{
    $user = User::create($request->validated());
    SendWelcomeEmailJob::dispatch($user);
    return redirect()->route('users.index');
}
```

**Right:**
```php
public function store(StoreUserRequest $request)
{
    $user = $this->userService->create($request->validated());
    return redirect()->route('users.index');
}

// In UserService
public function create(array $data): User
{
    $user = User::create($data);
    SendWelcomeEmailJob::dispatch($user)->afterCommit();
    return $user;
}
```

---

### ❌ Not Using `afterCommit()`

**Wrong:**
```php
DB::transaction(function () use ($data) {
    $invoice = Invoice::create($data);
    GenerateInvoicePdfJob::dispatch($invoice); // May execute before commit
});
```

**Right:**
```php
DB::transaction(function () use ($data) {
    $invoice = Invoice::create($data);
    GenerateInvoicePdfJob::dispatch($invoice)->afterCommit();
});
```

---

### ❌ Long-Running Synchronous Operations in HTTP Request

**Wrong:**
```php
public function publish(Invoice $invoice)
{
    $invoice->generatePdf(); // Takes 30 seconds
    $invoice->sendEmail();   // Takes 5 seconds
    return redirect()->route('invoices.show', $invoice);
}
```

**Right:**
```php
public function publish(Invoice $invoice)
{
    $this->invoiceService->publish($invoice); // Dispatches jobs, returns immediately
    return redirect()->route('invoices.show', $invoice);
}
```

---

### ❌ Not Defining Timeout

**Wrong:**
```php
class ProcessPaymentJob implements ShouldQueue
{
    // No timeout defined; defaults to 60s
}
```

**Right:**
```php
class ProcessPaymentJob implements ShouldQueue
{
    public $timeout = 30; // Explicit timeout
}
```

---

### ❌ Not Implementing `failed()`

**Wrong:**
```php
class SendInvoiceEmailJob implements ShouldQueue
{
    // No failed() method; failures go silent
}
```

**Right:**
```php
class SendInvoiceEmailJob implements ShouldQueue
{
    public function failed(Throwable $exception): void
    {
        Log::error('Invoice email failed', [
            'invoice_id' => $this->invoice->id,
            'error' => $exception->getMessage(),
        ]);
        
        $this->invoice->activityLogs()->create([
            'action' => 'email_failed',
            'description' => $exception->getMessage(),
        ]);
    }
}
```

---

## 15. Migration Path

### Current State

- No queued jobs implemented
- `queue.php` config uses `sync` driver (development)
- No `jobs` or `failed_jobs` tables

### Phase 1: Infrastructure Setup (Sprint 1.4)

- Generate queue tables: `php artisan queue:table`, `php artisan queue:failed-table`
- Run migrations
- Update `.env` for environment-specific queue drivers:
  - Development: `QUEUE_CONNECTION=sync` (or `database` for testing queue behavior)
  - Staging: `QUEUE_CONNECTION=database`
  - Production: `QUEUE_CONNECTION=redis`
- Configure Supervisor for production workers

### Phase 2: Notification Jobs (Sprint 1.5)

- Implement `SendEmailNotificationJob`
- Implement `SendSmsNotificationJob`
- Refactor `NotificationService` to dispatch jobs instead of sending synchronously
- Queue: `default-notifications`
- Tries: 3, Timeout: 30s

### Phase 3: Billing Jobs (Sprint 1.6)

- Implement `GenerateInvoiceJob`
- Implement `GenerateInvoicePdfJob`
- Implement `SendInvoiceEmailJob`
- Queue: `high` (invoice generation), `default` (email)
- Tries: 3, Timeout: 120s (PDF generation)

### Phase 4: Provisioning Jobs (Sprint 3.8)

- `ProvisionOnuJob` implemented
- Implement `DeprovisionOnuJob`
- Implement `SyncOnuConfigJob`
- Queue: `default-provisioning`
- Tries: 5, Timeout: 180s (OLT SNMP may be slow)

### Phase 5: Maintenance Jobs (Sprint 1.8+)

- Implement `CleanupOldLogsJob`
- Implement `WarmCacheJob`
- Implement `GenerateMonthlyReportJob`
- Queue: `low`
- Scheduled via `app/Console/Kernel.php` scheduler

---

## 16. Architecture Decisions

| Decision | Rationale |
|---|---|
| **Services dispatch jobs, not Controllers** | Maintains service layer encapsulation; business logic owns side effects |
| **Always use `afterCommit()`** | Ensures database consistency before triggering side effects |
| **Explicit timeout and retry for all jobs** | Predictable resource consumption and failure handling |
| **Jobs must be idempotent** | Prevents duplicate side effects from retries and worker crashes |
| **Priority queues (critical/high/default/low)** | Ensures time-sensitive operations execute first |
| **Implement `failed()` method** | Provides visibility into permanent failures and enables custom alerting |
| **Domain-specific queues for high-volume domains** | Prevents one domain from overwhelming queue; enables dedicated workers |

---

## 17. Summary

### Key Principles

1. **Services dispatch jobs** — Controllers never dispatch directly
2. **Use `afterCommit()`** — Ensure database commit before job dispatch
3. **Define explicit timeout and retry** — No defaults; every job declares policy
4. **Jobs must be idempotent** — Safe to execute multiple times
5. **Implement `failed()`** — Log permanent failures to ActivityLog and alert
6. **Use priority queues** — critical > high > default > low
7. **Test idempotency** — Verify job can run multiple times safely

### Quick Reference

| Job Category | Queue | Tries | Timeout | Backoff |
|---|---|---|---|---|
| **Critical** | `critical` | 5 | 30s | [10, 30, 60, 300] |
| **Email** | `default-notifications` | 3 | 30s | [10, 30, 60] |
| **PDF Generation** | `high` | 3 | 120s | [10, 30, 60] |
| **Provisioning** | `default-provisioning` | 5 | 180s | [10, 30, 60, 300] |
| **Data Export** | `low` | 2 | 300s | [30, 60] |
| **Cleanup** | `low-cleanup` | 1 | 600s | None |

### Job Template

```php
<?php

namespace App\Jobs\Billing;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $timeout = 120;
    
    public function __construct(public int $invoiceId) {}
    
    public function backoff(): array
    {
        return [10, 30, 60];
    }
    
    public function handle(): void
    {
        $invoice = Invoice::findOrFail($this->invoiceId);
        
        // Check idempotency
        if ($invoice->pdf_path) {
            return; // Already generated
        }
        
        // Generate PDF
        $pdfPath = $invoice->generatePdf();
        
        // Log to ActivityLog
        $invoice->activityLogs()->create([
            'action' => 'pdf_generated',
            'description' => 'Invoice PDF generated',
        ]);
    }
    
    public function failed(Throwable $exception): void
    {
        Log::channel('business')->error('Invoice PDF generation failed', [
            'invoice_id' => $this->invoiceId,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

---

**Next Steps:**

Sprint 1.4+: Implement queue infrastructure (migrations, Supervisor config) and begin implementing jobs for notifications, billing, and provisioning following this standard.
