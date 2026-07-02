# Platform Transaction Standard

**Version:** 1.0  
**Status:** Authoritative  
**Last Updated:** 2026-07-02

---

## Overview

### Purpose

This document establishes the authoritative database transaction architecture for the ISP Management Platform. It defines ownership rules, locking strategies, commit policies, and anti-patterns to ensure data consistency, prevent deadlocks, and maintain system reliability across all business modules.

**Core Principle:** Database transactions are **infrastructure concerns** owned exclusively by the Service Layer. HTTP Controllers must never directly manage transactions.

### Scope

This standard applies to:

- All database write operations (INSERT, UPDATE, DELETE)
- All operations requiring ACID guarantees
- Event dispatch timing relative to database commits
- Queue job dispatch timing relative to database commits
- External API calls and database transaction boundaries
- Locking strategies for concurrent access

**Out of Scope:**

- Query optimization (covered in Performance Guidelines)
- Database schema design (covered in `erd.md` and `entities.md`)
- Migration rollback strategies (covered in Migration Guidelines)

### Why Transaction Discipline Matters

**Without strict transaction ownership:**

- Controllers mix HTTP concerns with data consistency logic
- Transactions span HTTP boundaries, risking timeout and deadlock
- Events dispatch before database commits, creating inconsistent state
- External API calls inside transactions cause long locks
- Nested transaction complexity leads to maintenance burden

**With service-layer transaction ownership:**

- Clear separation of concerns (HTTP vs. data consistency)
- Transactions are short-lived and focused
- Events and jobs dispatch only after successful commits
- External integrations never block database locks
- Testable transaction logic independent of HTTP layer

---

## Transaction Ownership

### Rule 1: Services Own Transactions

**Only Services may open database transactions.**

Services are responsible for:

- Opening transactions with `DB::transaction()`
- Determining transaction boundaries
- Executing business logic within transactions
- Handling rollback on exceptions
- Dispatching events and jobs after commit

**Example — Service with Transaction:**

```php
<?php

namespace App\Services\Billing;

use App\Models\Subscription;
use App\Models\Invoice;
use App\Domain\Events\InvoiceGenerated;
use Illuminate\Support\Facades\DB;

class GenerateInvoiceService
{
    public function execute(Subscription $subscription): Invoice
    {
        return DB::transaction(function () use ($subscription) {
            // Create invoice
            $invoice = Invoice::create([
                'subscription_id' => $subscription->id,
                'customer_id' => $subscription->customer_id,
                'billing_period_id' => BillingPeriod::current()->id,
                'amount' => $subscription->package->price,
                'status' => InvoiceStatus::Draft,
            ]);
            
            // Create invoice items
            $invoice->items()->create([
                'description' => $subscription->package->name,
                'quantity' => 1,
                'unit_price' => $subscription->package->price,
                'total' => $subscription->package->price,
            ]);
            
            // Update subscription
            $subscription->update([
                'last_invoice_generated_at' => now(),
            ]);
            
            // Dispatch event AFTER commit (see afterCommit Rules section)
            event(new InvoiceGenerated($invoice));
            
            return $invoice;
        });
    }
}
```

### Rule 2: Controllers Never Manage Transactions

**Controllers must NEVER call:**

- `DB::transaction()`
- `DB::beginTransaction()`
- `DB::commit()`
- `DB::rollback()`

**Why:**

1. **Separation of Concerns** — Controllers handle HTTP, Services handle data consistency
2. **Transaction Scope** — Transactions must be short-lived; Controllers span HTTP request/response lifecycle
3. **Testability** — Business logic with transactions must be testable without HTTP layer
4. **Reusability** — Service transaction logic is reusable from CLI, jobs, tests without HTTP context

**❌ Anti-Pattern — Transaction in Controller:**

```php
// NEVER DO THIS
public function store(StoreInvoiceRequest $request)
{
    DB::transaction(function () use ($request) {
        $invoice = Invoice::create($request->validated());
        // Business logic in Controller...
        event(new InvoiceCreated($invoice));
    });
    
    return redirect()->route('invoices.show', $invoice);
}
```

**✅ Correct Pattern — Delegate to Service:**

```php
public function store(StoreInvoiceRequest $request)
{
    $this->authorize('create', Invoice::class);
    
    $invoice = $this->service->create($request->validated());
    
    return redirect()->route('invoices.show', $invoice);
}
```

### Rule 3: Models Never Manage Transactions

Eloquent models must not contain transaction logic. Models define relationships and accessors. Services orchestrate transactions.

**❌ Wrong:**

```php
class Subscription extends Model
{
    public function activate(): void
    {
        DB::transaction(function () {
            $this->status = SubscriptionStatus::Active;
            $this->save();
            event(new SubscriptionActivated($this));
        });
    }
}
```

**✅ Correct:**

```php
// Model — No transaction logic
class Subscription extends Model
{
    protected $casts = [
        'status' => SubscriptionStatus::class,
    ];
}

// Service — Owns transaction
class ActivateSubscriptionService
{
    public function execute(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $subscription->status = SubscriptionStatus::Active;
            $subscription->activated_at = now();
            $subscription->save();
            
            event(new SubscriptionActivated($subscription));
        });
    }
}
```

---

## Transaction Lifecycle

### Opening Transactions

**Preferred: Closure-Based Transaction**

```php
DB::transaction(function () {
    // All operations auto-commit
    // Auto-rollback on exception
});
```

**Why Preferred:**

- Automatic commit on success
- Automatic rollback on exception
- No risk of forgetting commit/rollback
- Cleaner exception handling

**Alternative: Manual Transaction** (use only when closure cannot work)

```php
DB::beginTransaction();

try {
    // Operations...
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}
```

**When Manual Is Required:**

- Logic spans multiple service methods that cannot be refactored into single closure
- Conditional commit/rollback based on external API response (rare; prefer queue pattern instead)

### Transaction Duration

**Keep transactions SHORT.**

**Target:** <100ms per transaction  
**Maximum:** <1s per transaction

**Short transactions minimize:**

- Lock contention
- Deadlock risk
- Blocking other queries
- Memory usage

**If a transaction exceeds 1s:** refactor to remove external calls, break into smaller units, or offload to queues.

---

## Nested Transaction Policy

### Laravel Savepoint Behavior

Laravel does **not support true nested transactions**. Instead, it uses **savepoints**.

**When you nest `DB::transaction()` calls:**

- First call opens a real transaction
- Subsequent calls create savepoints (`SAVEPOINT sp1`, `SAVEPOINT sp2`)
- Inner rollback only rolls back to savepoint, not entire transaction
- Outer rollback rolls back entire transaction

**Example:**

```php
DB::transaction(function () {  // Real transaction
    User::create([...]);
    
    DB::transaction(function () {  // Savepoint sp1
        Role::create([...]);
        throw new \Exception('Inner failure');
    });  // Rollback to sp1
    
    Permission::create([...]);  // Still executes
});  // Commit (User and Permission persisted, Role rolled back)
```

### Nesting Rules

**Rule 1: Avoid Nesting When Possible**

Prefer flat, single-level transactions. Nesting adds complexity and cognitive load.

**Rule 2: Maximum 2 Levels of Nesting**

- Level 1: Outer service transaction
- Level 2: Called service transaction (becomes savepoint)
- Level 3+: **PROHIBITED**

**Rule 3: Document Nested Transactions**

If nesting is unavoidable, add inline comment explaining why:

```php
public function execute(Customer $customer): void
{
    DB::transaction(function () use ($customer) {
        // Outer transaction
        $customer->update([...]);
        
        // Nested transaction required because ActivateSubscriptionService
        // is reusable from CLI and must manage its own transaction boundary
        $this->activateSubscriptionService->execute($customer->subscription);
        
        $customer->activityLogs()->create([...]);
    });
}
```

### Nested Transaction Anti-Patterns

**❌ Deep Nesting:**

```php
DB::transaction(function () {
    DB::transaction(function () {
        DB::transaction(function () {
            // Too deep — refactor
        });
    });
});
```

**❌ Conditional Nesting:**

```php
if ($condition) {
    DB::transaction(function () {
        // Logic
    });
} else {
    // Same logic WITHOUT transaction — inconsistency risk
}
```

**✅ Refactor to Single Level:**

```php
public function execute(): void
{
    DB::transaction(function () {
        $this->executeLogic();
    });
}

private function executeLogic(): void
{
    // Reusable logic, no transaction management
}
```

---

## Deadlock Retry Strategy

### What Is a Deadlock?

**Deadlock occurs when two transactions wait for each other's locks:**

- Transaction A locks Row 1, waits for Row 2
- Transaction B locks Row 2, waits for Row 1
- Both transactions are blocked forever
- MySQL detects deadlock and rolls back one transaction

**Laravel Deadlock Exception:**

```
Illuminate\Database\QueryException: Deadlock found when trying to get lock
```

### Detection and Retry

**MySQL auto-detects and rolls back one transaction.** The application must retry.

**Implementation — Retry with Exponential Backoff:**

```php
use Illuminate\Database\QueryException;

class ProcessPaymentService
{
    private const MAX_RETRIES = 3;
    private const BACKOFF_MS = [10, 50, 200]; // Exponential backoff
    
    public function execute(Payment $payment): void
    {
        $attempt = 0;
        
        while ($attempt < self::MAX_RETRIES) {
            try {
                DB::transaction(function () use ($payment) {
                    // Business logic that may deadlock
                    $payment->lockForUpdate()->first();
                    $payment->update(['status' => PaymentStatus::Completed]);
                });
                
                return; // Success — exit retry loop
                
            } catch (QueryException $e) {
                // Check if deadlock error (MySQL error code 1213)
                if ($e->errorInfo[1] ?? null !== 1213) {
                    throw $e; // Not a deadlock — rethrow
                }
                
                $attempt++;
                
                if ($attempt >= self::MAX_RETRIES) {
                    Log::error('Deadlock retry exhausted', [
                        'payment_id' => $payment->id,
                        'attempts' => $attempt,
                    ]);
                    throw $e; // Max retries exceeded
                }
                
                // Exponential backoff
                usleep(self::BACKOFF_MS[$attempt - 1] * 1000);
                
                Log::warning('Deadlock detected, retrying', [
                    'payment_id' => $payment->id,
                    'attempt' => $attempt,
                    'backoff_ms' => self::BACKOFF_MS[$attempt - 1],
                ]);
            }
        }
    }
}
```

### Deadlock Prevention Strategies

**Strategy 1: Consistent Lock Ordering**

Always lock resources in the same order across all transactions.

**❌ Inconsistent Ordering:**

```php
// Transaction A
$invoice->lockForUpdate();
$subscription->lockForUpdate();

// Transaction B
$subscription->lockForUpdate();  // Locks in REVERSE order
$invoice->lockForUpdate();       // DEADLOCK RISK
```

**✅ Consistent Ordering:**

```php
// Always lock: Subscription → Invoice (alphabetical, or by foreign key dependency)
$subscription->lockForUpdate();
$invoice->lockForUpdate();
```

**Strategy 2: Keep Transactions Short**

Shorter transactions hold locks for less time, reducing collision probability.

**Strategy 3: Use Optimistic Locking for Read-Heavy Workloads**

Avoids locks entirely (see Locking Strategies section).

**Strategy 4: Index Foreign Keys**

Unindexed foreign keys cause table-level locks. Ensure all FKs are indexed.

---

## Locking Strategies

### Overview

Laravel/MySQL supports two locking strategies:

1. **Optimistic Locking** — Assume no conflicts; detect on commit
2. **Pessimistic Locking** — Lock rows immediately; prevent conflicts

### Optimistic Locking

**How It Works:**

- Add `version` column to table
- Read record with current version
- Update only if version matches
- Increment version on successful update
- If version mismatch, another transaction modified the record — retry or abort

**When to Use:**

- **Read-heavy workloads** — Conflicts are rare
- **Long-running user operations** — User edits form for 10 minutes before submitting
- **Low contention** — Few concurrent updates to the same record

**Example — Optimistic Locking with Version Column:**

```php
// Migration
Schema::table('subscriptions', function (Blueprint $table) {
    $table->unsignedBigInteger('version')->default(1);
});

// Service
public function update(Subscription $subscription, array $data): void
{
    DB::transaction(function () use ($subscription, $data) {
        $currentVersion = $subscription->version;
        
        $updated = Subscription::where('id', $subscription->id)
            ->where('version', $currentVersion)  // Only update if version matches
            ->update([
                'package_id' => $data['package_id'],
                'version' => $currentVersion + 1,  // Increment version
            ]);
        
        if ($updated === 0) {
            throw new OptimisticLockException(
                "Subscription {$subscription->id} was modified by another transaction"
            );
        }
    });
}
```

**Advantages:**

- No database locks — better concurrency
- No deadlock risk
- Suitable for long-running operations

**Disadvantages:**

- Requires application-level retry logic
- Version column adds schema complexity
- Not suitable for high-contention scenarios

### Pessimistic Locking

**How It Works:**

- Explicitly lock rows with `SELECT ... FOR UPDATE`
- Other transactions wait until lock is released
- Lock held until transaction commits or rolls back

**When to Use:**

- **Write-heavy workloads** — Conflicts are common
- **High contention** — Many concurrent updates to same record
- **Short transactions** — Lock duration is brief (<100ms)
- **Critical consistency** — Preventing race conditions is essential (payments, inventory)

**Example — Pessimistic Locking with lockForUpdate:**

```php
public function execute(Payment $payment): void
{
    DB::transaction(function () use ($payment) {
        // Lock the payment row immediately
        $payment = Payment::where('id', $payment->id)->lockForUpdate()->first();
        
        if ($payment->status !== PaymentStatus::Pending) {
            throw new InvalidStateException('Payment already processed');
        }
        
        $payment->status = PaymentStatus::Completed;
        $payment->processed_at = now();
        $payment->save();
        
        event(new PaymentCompleted($payment));
    });
}
```

**Advantages:**

- Prevents race conditions at database level
- Simple application logic — no retry needed
- Immediate consistency guarantee

**Disadvantages:**

- Blocks concurrent transactions — reduces throughput
- Deadlock risk if lock ordering is inconsistent
- Not suitable for long-running operations

### Comparison Table

| Criterion | Optimistic Locking | Pessimistic Locking |
|---|---|---|
| **Workload** | Read-heavy | Write-heavy |
| **Contention** | Low | High |
| **Transaction Duration** | Long (seconds/minutes) | Short (<100ms) |
| **Concurrency** | High (no locks) | Lower (blocks) |
| **Deadlock Risk** | None | Yes (if ordering inconsistent) |
| **Application Complexity** | Retry logic required | Simpler (DB handles conflict) |
| **Use Cases** | User form edits, bulk reports | Payments, inventory, provisioning |

### Choosing a Strategy

**Use Optimistic Locking when:**

- Conflicts are rare (<1% of operations)
- Operations are long-running (user interactions)
- Maximizing concurrency is critical

**Use Pessimistic Locking when:**

- Conflicts are common (>10% of operations)
- Operations are short-lived (<100ms)
- Data consistency is critical (payments, inventory)

---

## lockForUpdate Guidelines

### Syntax

**Eloquent:**

```php
$invoice = Invoice::where('id', $id)->lockForUpdate()->first();
```

**Query Builder:**

```php
$invoice = DB::table('invoices')->where('id', $id)->lockForUpdate()->first();
```

### When to Use lockForUpdate

**Use lockForUpdate when:**

1. **Preventing race conditions** — Multiple concurrent updates to the same record
2. **Read-then-write with consistency guarantee** — Read current state, update based on that state
3. **Critical operations** — Payments, inventory deductions, provisioning

**Example — Payment Processing (Race Condition Prevention):**

```php
DB::transaction(function () use ($paymentId) {
    // Lock payment row to prevent duplicate processing
    $payment = Payment::where('id', $paymentId)->lockForUpdate()->first();
    
    if ($payment->status !== PaymentStatus::Pending) {
        return; // Already processed — idempotent exit
    }
    
    $payment->status = PaymentStatus::Completed;
    $payment->save();
    
    // Allocate payment to invoices
    $this->allocationService->allocate($payment);
});
```

### lockForUpdate Best Practices

**Rule 1: Lock Only What You Need**

Lock specific rows, not entire tables.

**❌ Too Broad:**

```php
Invoice::lockForUpdate()->get();  // Locks ALL invoices
```

**✅ Specific:**

```php
Invoice::where('customer_id', $customerId)->lockForUpdate()->get();
```

**Rule 2: Keep Lock Duration Short**

Execute business logic quickly. Do NOT perform external API calls while holding locks.

**❌ Long Lock:**

```php
DB::transaction(function () use ($payment) {
    $payment = Payment::lockForUpdate()->find($paymentId);
    
    // External API call inside transaction — holds lock for seconds
    $this->paymentGateway->charge($payment->amount);
    
    $payment->status = PaymentStatus::Completed;
    $payment->save();
});
```

**✅ Short Lock:**

```php
// Step 1: External API call OUTSIDE transaction
$gatewayResponse = $this->paymentGateway->charge($payment->amount);

// Step 2: Update database with result
DB::transaction(function () use ($payment, $gatewayResponse) {
    $payment = Payment::lockForUpdate()->find($paymentId);
    
    $payment->status = PaymentStatus::Completed;
    $payment->gateway_transaction_id = $gatewayResponse->transactionId;
    $payment->save();
});
```

**Rule 3: Consistent Lock Ordering**

Always lock resources in the same order to prevent deadlocks.

**Rule 4: Use Timeout**

Set `innodb_lock_wait_timeout` to prevent indefinite waits (default 50s).

```sql
SET SESSION innodb_lock_wait_timeout = 10;  -- 10 seconds
```

### lockForUpdate Pitfalls

**Pitfall 1: Locking in Loops**

Avoid locking inside loops — causes N+1 lock operations and deadlock risk.

**❌ Wrong:**

```php
foreach ($invoices as $invoice) {
    DB::transaction(function () use ($invoice) {
        $invoice = Invoice::lockForUpdate()->find($invoice->id);
        // Update...
    });
}
```

**✅ Correct:**

```php
DB::transaction(function () use ($invoices) {
    $invoiceIds = $invoices->pluck('id');
    $lockedInvoices = Invoice::whereIn('id', $invoiceIds)->lockForUpdate()->get();
    
    foreach ($lockedInvoices as $invoice) {
        // Update...
    }
});
```

**Pitfall 2: Forgetting Transaction**

`lockForUpdate()` requires a transaction. Locks are released on commit.

**❌ No Transaction:**

```php
$payment = Payment::lockForUpdate()->find($paymentId);  // Lock released immediately
$payment->status = PaymentStatus::Completed;
$payment->save();  // No lock held — race condition
```

**✅ With Transaction:**

```php
DB::transaction(function () use ($paymentId) {
    $payment = Payment::lockForUpdate()->find($paymentId);
    $payment->status = PaymentStatus::Completed;
    $payment->save();
});  // Lock released on commit
```

---

## afterCommit Rules

### Why afterCommit Matters

**Problem Without afterCommit:**

```php
DB::transaction(function () {
    $invoice = Invoice::create([...]);
    
    event(new InvoiceCreated($invoice));  // Dispatched BEFORE commit
});

// If transaction rolls back, event was already dispatched
// Listeners see inconsistent state (invoice doesn't exist in database)
```

**Solution With afterCommit:**

```php
DB::transaction(function () {
    $invoice = Invoice::create([...]);
    
    event(new InvoiceCreated($invoice));  // Queued until commit
});

// Event only dispatches if transaction commits successfully
// Listeners always see consistent state
```

### Rule 1: Events Must Use afterCommit

**All Domain Events must be dispatched AFTER successful database commit.**

**Implementation Option 1: Configure Event Class**

```php
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class InvoiceCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    
    public function __construct(public Invoice $invoice) {}
}
```

**Implementation Option 2: Explicit afterCommit in Service**

```php
event(new InvoiceCreated($invoice))->afterCommit();
```

**Recommendation:** Use `ShouldDispatchAfterCommit` interface. It's declarative and cannot be forgotten.

### Rule 2: Queue Jobs Must Use afterCommit

**All queued jobs that depend on database persistence must use `afterCommit()`.**

**❌ Without afterCommit:**

```php
DB::transaction(function () use ($subscription) {
    $subscription->status = SubscriptionStatus::Active;
    $subscription->save();
    
    ProvisionOnuJob::dispatch($subscription->onu_id);  // Dispatched immediately
});

// If transaction rolls back, job already queued
// Job executes before subscription is activated — fails
```

**✅ With afterCommit:**

```php
DB::transaction(function () use ($subscription) {
    $subscription->status = SubscriptionStatus::Active;
    $subscription->save();
    
    ProvisionOnuJob::dispatch($subscription->onu_id)->afterCommit();
});

// Job only dispatched if transaction commits
// Job always sees activated subscription
```

**When afterCommit Is NOT Required:**

- Jobs that do NOT depend on database state (e.g., sending standalone email)
- Jobs explicitly designed to handle "not found" scenarios

**Default Recommendation:** Always use `afterCommit()` unless you have explicit reason not to.

### Rule 3: No External API Calls Inside Transactions

**External API calls must NEVER execute inside database transactions.**

**Why:**

- API calls are slow (100ms - 10s)
- Network failures cause transaction rollback
- Locks held during API call block other transactions
- API may succeed but transaction rolls back — inconsistent state

**❌ Wrong:**

```php
DB::transaction(function () use ($payment) {
    $payment->status = PaymentStatus::Completed;
    $payment->save();
    
    // External API inside transaction — WRONG
    $this->paymentGateway->recordPayment($payment);
});
```

**✅ Correct — Queue Pattern:**

```php
DB::transaction(function () use ($payment) {
    $payment->status = PaymentStatus::Completed;
    $payment->save();
    
    // Dispatch job AFTER commit
    RecordPaymentWithGatewayJob::dispatch($payment)->afterCommit();
});
```

**✅ Correct — Call Before Transaction:**

```php
// Step 1: External API call
$gatewayResponse = $this->paymentGateway->charge($payment->amount);

// Step 2: Store result in transaction
DB::transaction(function () use ($payment, $gatewayResponse) {
    $payment->status = PaymentStatus::Completed;
    $payment->gateway_transaction_id = $gatewayResponse->transactionId;
    $payment->save();
});
```

---

## Queue Dispatch Rules

### Rule 1: Use afterCommit for Persistence-Dependent Jobs

**If a job depends on database records existing, use `afterCommit()`.**

**Examples of Persistence-Dependent Jobs:**

- Generate invoice PDF (invoice must exist)
- Send activation email (subscription must be activated)
- Provision ONU (subscription and ONU record must exist)
- Sync customer data to external system (customer must exist)

**Implementation:**

```php
GenerateInvoicePdfJob::dispatch($invoice)->afterCommit();
SendActivationEmailJob::dispatch($subscription)->afterCommit();
ProvisionOnuJob::dispatch($subscription->onu_id)->afterCommit();
```

### Rule 2: Configure Default afterCommit Behavior

**Set global default in `config/queue.php`:**

```php
'default' => env('QUEUE_CONNECTION', 'sync'),

'connections' => [
    'sync' => [
        'driver' => 'sync',
        'after_commit' => true,  // Global default
    ],
    
    'database' => [
        'driver' => 'database',
        'after_commit' => true,
    ],
],
```

**Why:** Reduces risk of forgetting `afterCommit()` call. Jobs opt-out if needed.

### Rule 3: Opt-Out Only When Safe

**Jobs that do NOT require persistence can opt out:**

```php
class SendGenericEmailJob implements ShouldQueue
{
    public $afterCommit = false;  // Opt out of afterCommit
    
    public function __construct(public string $email, public string $message) {}
}
```

**When Opt-Out Is Safe:**

- Job does not query database for records created in transaction
- Job is idempotent and handles "not found" gracefully

---

## External API Integration

### Rule: Never Inside Transactions

**External API calls must NEVER execute inside database transactions.**

**Prohibited Operations Inside Transactions:**

- HTTP requests (Guzzle, cURL)
- SOAP calls
- SNMP calls (OLT provisioning)
- SMTP email delivery
- SMS gateway API
- Payment gateway API
- Third-party webhooks

**Why:**

1. **Long Lock Duration** — API calls take 100ms - 10s; locks held entire time
2. **Network Failures** — Timeout or failure causes transaction rollback
3. **Inconsistent State** — API succeeds but transaction rolls back (or vice versa)
4. **Deadlock Risk** — Long locks increase collision probability

### Pattern 1: Queue Jobs for External Calls

**Preferred approach for external API integration.**

```php
class ActivateSubscriptionService
{
    public function execute(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $subscription->status = SubscriptionStatus::Active;
            $subscription->activated_at = now();
            $subscription->save();
            
            // Dispatch provisioning job AFTER commit
            ProvisionOnuJob::dispatch($subscription->onu_id)->afterCommit();
        });
    }
}

class ProvisionOnuJob implements ShouldQueue
{
    public function handle(OnuProvisioningService $service): void
    {
        // External SNMP call to OLT (outside any transaction)
        $service->provisionOnu($this->onuId);
    }
}
```

### Pattern 2: Call Before Transaction

**When immediate result is required.**

```php
public function processPayment(Payment $payment): void
{
    // Step 1: External API call BEFORE transaction
    try {
        $gatewayResponse = $this->paymentGateway->charge($payment->amount);
    } catch (GatewayException $e) {
        Log::error('Gateway charge failed', ['payment_id' => $payment->id]);
        throw $e;
    }
    
    // Step 2: Store result in SHORT transaction
    DB::transaction(function () use ($payment, $gatewayResponse) {
        $payment->status = PaymentStatus::Completed;
        $payment->gateway_transaction_id = $gatewayResponse->transactionId;
        $payment->processed_at = now();
        $payment->save();
        
        event(new PaymentCompleted($payment));
    });
}
```

### Pattern 3: Idempotent Retry on Failure

**Job retries if external API fails.**

```php
class ProvisionOnuJob implements ShouldQueue
{
    public $tries = 5;
    public $backoff = [10, 30, 60, 300];
    
    public function handle(OltService $oltService): void
    {
        // External SNMP call to OLT
        $result = $oltService->provisionOnu($this->onuId);
        
        // Store result in database AFTER external call succeeds
        DB::transaction(function () use ($result) {
            $onu = Onu::find($this->onuId);
            $onu->status = OnuStatus::Active;
            $onu->provisioned_at = now();
            $onu->serial_number = $result->serialNumber;
            $onu->save();
        });
    }
    
    public function failed(Throwable $exception): void
    {
        Log::error('ONU provisioning failed after retries', [
            'onu_id' => $this->onuId,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

---

## Transaction Isolation Levels

### MySQL Default: REPEATABLE READ

**Laravel/MySQL defaults to `REPEATABLE READ` isolation level.**

**Guarantees:**

- Reads within transaction see consistent snapshot
- Prevents dirty reads, non-repeatable reads, phantom reads (with gap locking)

**Sufficient for 99% of application needs.**

### When to Change Isolation Level

**Use Case 1: READ UNCOMMITTED (rare)**

Read uncommitted data for reporting queries that tolerate dirty reads.

```php
DB::statement('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
DB::select('SELECT ...');  // May see uncommitted changes
```

**Use Case 2: SERIALIZABLE (rare)**

Strictest isolation — prevents all anomalies but reduces concurrency.

```php
DB::statement('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
DB::transaction(function () {
    // Full serializability
});
```

**Recommendation:** Stick with `REPEATABLE READ` default unless specific requirement dictates otherwise.

---

## Transaction Anti-Patterns

### Anti-Pattern 1: Long-Running Transactions

**❌ Wrong:**

```php
DB::transaction(function () {
    $invoices = Invoice::where('status', InvoiceStatus::Draft)->get();
    
    foreach ($invoices as $invoice) {
        // Generate PDF — takes 5s per invoice
        $pdf = $this->pdfService->generate($invoice);
        
        Storage::put("invoices/{$invoice->id}.pdf", $pdf);
        
        $invoice->status = InvoiceStatus::Published;
        $invoice->save();
    }
});

// Transaction may run for MINUTES — holds locks entire time
```

**✅ Correct:**

```php
$invoices = Invoice::where('status', InvoiceStatus::Draft)->get();

foreach ($invoices as $invoice) {
    // Dispatch job — transaction is short
    GenerateInvoicePdfJob::dispatch($invoice);
}
```

### Anti-Pattern 2: Nested Complexity

**❌ Wrong:**

```php
DB::transaction(function () {
    DB::transaction(function () {
        DB::transaction(function () {
            // Deep nesting — hard to reason about rollback behavior
        });
    });
});
```

**✅ Correct:**

```php
public function execute(): void
{
    DB::transaction(function () {
        $this->step1();
        $this->step2();
        $this->step3();
    });
}
```

### Anti-Pattern 3: External Calls Inside Transactions

**Covered in detail in External API Integration section.**

### Anti-Pattern 4: Catching Generic Exceptions

**❌ Wrong:**

```php
DB::transaction(function () {
    try {
        Invoice::create([...]);
    } catch (\Exception $e) {
        // Swallows exception — transaction auto-rolls back, but app continues
        Log::error('Invoice creation failed');
    }
});
```

**✅ Correct:**

```php
DB::transaction(function () {
    Invoice::create([...]);
    
    // Let exceptions propagate — transaction auto-rolls back
});
```

### Anti-Pattern 5: Forgetting afterCommit

**❌ Wrong:**

```php
DB::transaction(function () use ($invoice) {
    $invoice->create([...]);
    
    event(new InvoiceCreated($invoice));  // Dispatched before commit
});
```

**✅ Correct:**

```php
class InvoiceCreated implements ShouldDispatchAfterCommit
{
    // Event waits for commit
}

DB::transaction(function () use ($invoice) {
    $invoice->create([...]);
    
    event(new InvoiceCreated($invoice));  // Dispatched after commit
});
```

---

## Testing Guidelines

### RefreshDatabase Trait

**Use `RefreshDatabase` to wrap each test in a transaction.**

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_invoice_generation(): void
    {
        $subscription = Subscription::factory()->create();
        
        $invoice = $this->service->generate($subscription);
        
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'subscription_id' => $subscription->id,
        ]);
    }
}
```

**How It Works:**

- `RefreshDatabase` runs migrations once
- Each test runs inside a database transaction
- Transaction is rolled back after test completes
- Database is clean for next test

### Testing Transaction Rollback

**Test that exceptions cause rollback:**

```php
public function test_invoice_generation_rollback_on_failure(): void
{
    $subscription = Subscription::factory()->create();
    
    // Mock service to throw exception
    $this->mock(BillingPeriodService::class, function ($mock) {
        $mock->shouldReceive('current')->andThrow(new \Exception('Period not found'));
    });
    
    try {
        $this->service->generate($subscription);
        $this->fail('Expected exception');
    } catch (\Exception $e) {
        // Exception thrown
    }
    
    // Assert NO invoice created (rolled back)
    $this->assertDatabaseMissing('invoices', [
        'subscription_id' => $subscription->id,
    ]);
}
```

### Testing Deadlock Retry

**Test that deadlock retry logic works:**

```php
public function test_deadlock_retry(): void
{
    $payment = Payment::factory()->create();
    
    // Mock QueryException with deadlock error code
    $deadlockException = new QueryException(
        'SELECT * FROM payments FOR UPDATE',
        [],
        new \PDOException('Deadlock found', 1213)
    );
    
    // Expect 2 calls: first fails with deadlock, second succeeds
    Payment::shouldReceive('lockForUpdate->first')
        ->once()
        ->andThrow($deadlockException)
        ->shouldReceive('lockForUpdate->first')
        ->once()
        ->andReturn($payment);
    
    $this->service->process($payment);
    
    // Assert payment processed after retry
    $this->assertEquals(PaymentStatus::Completed, $payment->fresh()->status);
}
```

---

## Migration Path

### Phase 1: Audit Existing Code (Sprint 1.4)

**Task:** Identify transaction ownership violations.

**Search for:**

```bash
# Controllers with DB::transaction
grep -r "DB::transaction" app/Http/Controllers/

# Controllers with beginTransaction
grep -r "beginTransaction" app/Http/Controllers/

# Models with transaction logic
grep -r "DB::transaction" app/Models/
```

**Document violations in Architecture Backlog.**

### Phase 2: Refactor Violations (Sprint 1.5)

**Move transaction logic from Controllers to Services.**

**Before:**

```php
// Controller
public function store(Request $request)
{
    DB::transaction(function () use ($request) {
        $invoice = Invoice::create($request->all());
        event(new InvoiceCreated($invoice));
    });
}
```

**After:**

```php
// Controller
public function store(StoreInvoiceRequest $request)
{
    $invoice = $this->service->create($request->validated());
    return redirect()->route('invoices.show', $invoice);
}

// Service
public function create(array $data): Invoice
{
    return DB::transaction(function () use ($data) {
        $invoice = Invoice::create($data);
        event(new InvoiceCreated($invoice));
        return $invoice;
    });
}
```

### Phase 3: Implement afterCommit (Sprint 1.5)

**Add `ShouldDispatchAfterCommit` to all Domain Events:**

```php
class InvoiceCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;
    
    public readonly int $invoiceId;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;
    
    public function __construct(int $invoiceId, ?int $actorId = null)
    {
        $this->invoiceId = $invoiceId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}
```

**Add `afterCommit()` to all job dispatches:**

```bash
# Find job dispatches
grep -r "::dispatch" app/Services/
```

**Update to:**

```php
GenerateInvoicePdfJob::dispatch($invoice)->afterCommit();
```

### Phase 4: Implement Deadlock Retry (Sprint 1.6)

**Identify high-contention services (Payment, Inventory, Provisioning).**

**Add retry logic per Deadlock Retry Strategy section.**

### Phase 5: Audit External API Calls (Sprint 1.6)

**Search for external calls inside transactions:**

```bash
grep -r "Http::get\|Http::post\|Guzzle\|curl" app/Services/
```

**Refactor to queue pattern or call-before-transaction pattern.**

---

## Examples

### Example 1: Service with Transaction

```php
<?php

namespace App\Services\Billing;

use App\Models\Subscription;
use App\Models\Invoice;
use App\Domain\Events\InvoiceGenerated;
use Illuminate\Support\Facades\DB;

class GenerateInvoiceService
{
    public function execute(Subscription $subscription): Invoice
    {
        return DB::transaction(function () use ($subscription) {
            $invoice = Invoice::create([
                'subscription_id' => $subscription->id,
                'customer_id' => $subscription->customer_id,
                'amount' => $subscription->package->price,
                'status' => InvoiceStatus::Draft,
            ]);
            
            $invoice->items()->create([
                'description' => $subscription->package->name,
                'unit_price' => $subscription->package->price,
                'quantity' => 1,
                'total' => $subscription->package->price,
            ]);
            
            $subscription->update([
                'last_invoice_generated_at' => now(),
            ]);
            
            // Event dispatches AFTER commit (ShouldDispatchAfterCommit)
            event(new InvoiceGenerated($invoice));
            
            return $invoice;
        });
    }
}
```

### Example 2: Nested Transaction (2 Levels Max)

```php
class CreateCustomerWithSubscriptionService
{
    public function __construct(
        private CreateSubscriptionService $createSubscriptionService
    ) {}
    
    public function execute(array $customerData, array $subscriptionData): Customer
    {
        return DB::transaction(function () use ($customerData, $subscriptionData) {
            // Level 1 transaction
            $customer = Customer::create($customerData);
            
            // Level 2 transaction (becomes savepoint)
            // Nested call required because CreateSubscriptionService
            // is reusable from CLI and must manage its own transaction boundary
            $subscription = $this->createSubscriptionService->execute($customer, $subscriptionData);
            
            $customer->activityLogs()->create([
                'action' => 'customer_created_with_subscription',
                'description' => "Customer created with subscription {$subscription->id}",
            ]);
            
            return $customer;
        });
    }
}
```

### Example 3: Deadlock Retry

```php
<?php

namespace App\Services\Billing;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class ProcessPaymentService
{
    private const MAX_RETRIES = 3;
    private const BACKOFF_MS = [10, 50, 200];
    
    public function execute(Payment $payment): void
    {
        $attempt = 0;
        
        while ($attempt < self::MAX_RETRIES) {
            try {
                DB::transaction(function () use ($payment) {
                    $payment = Payment::where('id', $payment->id)->lockForUpdate()->first();
                    
                    if ($payment->status !== PaymentStatus::Pending) {
                        return; // Already processed
                    }
                    
                    $payment->status = PaymentStatus::Completed;
                    $payment->processed_at = now();
                    $payment->save();
                    
                    // Allocate payment to invoices
                    $this->allocate($payment);
                });
                
                return; // Success
                
            } catch (QueryException $e) {
                if (($e->errorInfo[1] ?? null) !== 1213) {
                    throw $e; // Not a deadlock
                }
                
                $attempt++;
                
                if ($attempt >= self::MAX_RETRIES) {
                    Log::error('Deadlock retry exhausted', ['payment_id' => $payment->id]);
                    throw $e;
                }
                
                usleep(self::BACKOFF_MS[$attempt - 1] * 1000);
                
                Log::warning('Deadlock detected, retrying', [
                    'payment_id' => $payment->id,
                    'attempt' => $attempt,
                ]);
            }
        }
    }
}
```

### Example 4: Pessimistic Locking

```php
public function activate(Subscription $subscription): void
{
    DB::transaction(function () use ($subscription) {
        // Lock subscription row
        $subscription = Subscription::where('id', $subscription->id)->lockForUpdate()->first();
        
        if ($subscription->status === SubscriptionStatus::Active) {
            return; // Already active — idempotent
        }
        
        $subscription->status = SubscriptionStatus::Active;
        $subscription->activated_at = now();
        $subscription->save();
        
        event(new SubscriptionActivated($subscription));
    });
}
```

### Example 5: Optimistic Locking

```php
public function update(Subscription $subscription, array $data): void
{
    DB::transaction(function () use ($subscription, $data) {
        $currentVersion = $subscription->version;
        
        $updated = Subscription::where('id', $subscription->id)
            ->where('version', $currentVersion)
            ->update([
                'package_id' => $data['package_id'],
                'version' => $currentVersion + 1,
            ]);
        
        if ($updated === 0) {
            throw new OptimisticLockException(
                "Subscription was modified by another transaction"
            );
        }
        
        event(new SubscriptionUpdated($subscription->id));
    });
}
```

### Example 6: afterCommit with Event

```php
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class InvoicePublished implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;
    
    public readonly int $invoiceId;
    public readonly Carbon $occurredAt;
    public readonly ?int $actorId;
    
    public function __construct(int $invoiceId, ?int $actorId = null)
    {
        $this->invoiceId = $invoiceId;
        $this->actorId = $actorId;
        $this->occurredAt = now();
    }
}

// Service
public function publish(Invoice $invoice): void
{
    DB::transaction(function () use ($invoice) {
        $invoice->status = InvoiceStatus::Published;
        $invoice->published_at = now();
        $invoice->save();
        
        // Dispatches AFTER commit
        event(new InvoicePublished($invoice->id));
    });
}
```

### Example 7: afterCommit with Queue Job

```php
public function activate(Subscription $subscription): void
{
    DB::transaction(function () use ($subscription) {
        $subscription->status = SubscriptionStatus::Active;
        $subscription->activated_at = now();
        $subscription->save();
        
        // Job dispatches AFTER commit
        ProvisionOnuJob::dispatch($subscription->onu_id)->afterCommit();
    });
}
```

### Example 8: External API Outside Transaction

```php
public function processPayment(Payment $payment): void
{
    // Step 1: External API call OUTSIDE transaction
    $gatewayResponse = $this->paymentGateway->charge([
        'amount' => $payment->amount,
        'currency' => 'USD',
        'customer_id' => $payment->customer_id,
    ]);
    
    // Step 2: Store result in SHORT transaction
    DB::transaction(function () use ($payment, $gatewayResponse) {
        $payment->status = PaymentStatus::Completed;
        $payment->gateway_transaction_id = $gatewayResponse->transactionId;
        $payment->processed_at = now();
        $payment->save();
        
        event(new PaymentCompleted($payment->id));
    });
}
```

---

## Quick Reference

### Transaction Ownership

| Layer | Transaction Allowed? | Rationale |
|---|---|---|
| **Controller** | ❌ NO | HTTP concerns; delegates to Service |
| **Service** | ✅ YES | Business logic layer; owns data consistency |
| **Model** | ❌ NO | Models define relationships, not transactions |
| **Job** | ✅ YES | Async business logic; may need transactions |
| **Listener** | ✅ YES | Event-driven logic; may need transactions |

### Locking Decision Matrix

| Scenario | Strategy | Rationale |
|---|---|---|
| Rare conflicts (<1%) | Optimistic | Maximizes concurrency |
| Common conflicts (>10%) | Pessimistic | Prevents retries |
| Long-running operation | Optimistic | Avoids long locks |
| Short operation (<100ms) | Pessimistic | Simple, immediate consistency |
| Payment processing | Pessimistic | Critical consistency |
| User form editing | Optimistic | Long user interaction |

### afterCommit Checklist

| Operation | Requires afterCommit? | Reason |
|---|---|---|
| Event dispatch | ✅ YES (always) | Listeners must see committed state |
| Queue job (persistent data) | ✅ YES | Job queries for record created in transaction |
| Queue job (standalone) | ❌ NO | Job doesn't depend on transaction |
| External API call | ❌ N/A | Never inside transaction |

### Transaction Anti-Pattern Checklist

- [ ] Transaction in Controller — MOVE to Service
- [ ] Transaction in Model — MOVE to Service
- [ ] External API inside transaction — QUEUE or CALL BEFORE
- [ ] Event without afterCommit — ADD ShouldDispatchAfterCommit
- [ ] Job without afterCommit — ADD ->afterCommit()
- [ ] Transaction exceeds 1s — REFACTOR to shorter units
- [ ] Nested 3+ levels — REFACTOR to flat structure
- [ ] No deadlock retry for high-contention — ADD retry logic

---

## Summary

### Key Principles

1. **Services Own Transactions** — Controllers and Models never manage transactions
2. **Keep Transactions Short** — Target <100ms; maximum 1s
3. **Consistent Lock Ordering** — Prevent deadlocks with predictable locking sequence
4. **afterCommit for Events and Jobs** — Ensure consistency before dispatch
5. **No External Calls Inside Transactions** — Queue or call before transaction
6. **Retry on Deadlock** — Exponential backoff for high-contention operations
7. **Choose Locking Strategy** — Optimistic for read-heavy; pessimistic for write-heavy

### Architecture Impact

This transaction standard establishes:

- Clear ownership boundary (Service Layer)
- Consistent event and job dispatch timing
- Deadlock prevention and retry patterns
- External API integration patterns
- Testing approach for transactional logic

**Complements:**

- **Domain Event Architecture** — Events dispatch after commit
- **Queue Architecture** — Jobs dispatch after commit
- **Platform Logging Standard** — Transaction failures logged
- **Platform Cache Architecture** — Cache invalidation happens in transactions

### Migration Path

**Sprint 1.4:** Audit existing code for violations  
**Sprint 1.5:** Refactor Controllers, implement afterCommit  
**Sprint 1.6:** Implement deadlock retry, refactor external API calls  

---

**End of Document**
