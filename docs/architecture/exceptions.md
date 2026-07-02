# Platform Exception Standard

**Version:** 1.0  
**Status:** Authoritative  
**Last Updated:** 2026-07-02

---

## Overview

### Purpose

This document establishes the authoritative exception architecture for the ISP Management Platform. It defines exception classification, naming conventions, hierarchy structure, logging responsibilities, message formatting, and handling patterns to ensure consistent error management, clear diagnostics, and appropriate user feedback across all business modules.

**Core Principle:** Exceptions are **business communication tools**. They communicate failures to developers (via logs and stack traces) and to users (via error messages). Exception design must serve both audiences without conflation.

### Scope

This standard applies to:

- All PHP exceptions thrown in application code
- Business rule validation failures
- Domain integrity violations
- Infrastructure failures (database, cache, queue)
- External integration failures (API, SNMP, SMTP)
- Concurrency and locking failures
- Configuration and environment errors

**Out of Scope:**

- HTTP status code mapping (covered in HTTP Response Guidelines)
- Frontend error display (covered in UI Guidelines)
- Exception monitoring and alerting (covered in Observability Guidelines)

### Why Exception Discipline Matters

**Without structured exception taxonomy:**

- Generic `\Exception` used everywhere — no semantic meaning
- Logs filled with stack traces but unclear root causes
- User sees technical errors instead of actionable messages
- Catch blocks use fragile string matching on exception messages
- Third-party exceptions leak into domain layer

**With structured exception taxonomy:**

- Exception class name communicates intent (InvalidStateException, PaymentGatewayException)
- Logs categorize failures automatically (business vs infrastructure)
- Users see contextual, actionable error messages
- Catch blocks use type-safe exception classes
- Third-party exceptions wrapped at boundaries with domain context

---

## Exception Classification

Exceptions are organized into seven primary categories aligned with application architecture layers and failure modes.

### 1. Application Exceptions

**Purpose:** HTTP layer failures, routing failures, middleware failures.

**Base Class:** `App\Exceptions\ApplicationException extends \RuntimeException`

**Examples:**

- `RouteNotFoundException` — Requested route does not exist
- `MiddlewareException` — Middleware execution failed
- `RateLimitExceededException` — Too many requests from client
- `MaintenanceModeException` — Application in maintenance mode
- `CsrfTokenMismatchException` — CSRF validation failed

**When to Use:**

- Failures in HTTP request/response lifecycle
- Routing and middleware errors
- Rate limiting and throttling violations

**Logging Responsibility:**

- Log Level: `warning` (client errors like 404, 429) or `error` (application errors like 500)
- Log Channel: `application`
- Include: Request URL, HTTP method, IP address, user ID (if authenticated)

**User-Facing Message:**

- Clear, non-technical: "The page you requested does not exist."
- Actionable: "Please try again in a few minutes." (rate limiting)
- Never expose stack traces or file paths

**Developer Message:**

- Technical details: "Route [api/invoices/{id}/publish] not found in route collection"
- Include: Route name, parameters, middleware stack

### 2. Domain Exceptions

**Purpose:** Business logic violations, entity lifecycle violations, aggregate root consistency failures.

**Base Class:** `App\Exceptions\DomainException extends \RuntimeException`

**Examples:**

- `InvalidStateException` — Entity in wrong state for operation (e.g., activating already-active subscription)
- `AggregateRootNotFoundException` — Entity required for operation does not exist
- `EntityConstraintViolationException` — Entity invariant violated (e.g., subscription without package)
- `LifecycleTransitionException` — Invalid state transition (e.g., Draft → Cancelled without Published)
- `ImmutabilityViolationException` — Attempt to modify immutable record (e.g., published invoice)

**When to Use:**

- Entity state validation failures
- Workflow lifecycle violations
- Aggregate boundary violations
- Domain invariant violations

**Logging Responsibility:**

- Log Level: `notice` (expected business failures) or `warning` (unexpected but recoverable)
- Log Channel: `business`
- Include: Entity type, entity ID, current state, attempted operation, actor ID

**User-Facing Message:**

- Business-focused: "This subscription is already active."
- Explain constraint: "Invoices cannot be modified after publication."
- Guide action: "Please create a credit note to correct this invoice."

**Developer Message:**

- State context: "Cannot activate Subscription[123]. Current state: Active. Expected: PendingActivation or Suspended."
- Workflow reference: "See docs/workflows/subscription-lifecycle.md for valid transitions."

### 3. Validation Exceptions

**Purpose:** Input validation failures from user-submitted data (HTTP requests, CLI commands, API payloads).

**Base Class:** `Illuminate\Validation\ValidationException` (Laravel built-in)

**Do NOT create custom validation exception classes.** Use Laravel's `ValidationException` with custom messages and error bags.

**Examples:**

- Form validation failures (`StoreInvoiceRequest`)
- API payload validation failures (JSON schema violations)
- CLI argument validation failures (`artisan invoice:generate --period=invalid`)

**When to Use:**

- User input fails FormRequest validation rules
- API payload fails schema validation
- Required fields missing or malformed

**Logging Responsibility:**

- Log Level: `info` (expected user errors) — do NOT log at `warning`/`error`
- Log Channel: `application`
- Include: Validation errors (sanitized — no sensitive data), user ID, request URL
- Exclude: Request payload with passwords/tokens

**User-Facing Message:**

- Field-level errors: "The email field must be a valid email address."
- Validation error bag: `['email' => ['The email field is required.']]`
- Return 422 Unprocessable Entity with error bag JSON

**Developer Message:**

- Not needed — validation errors are self-explanatory with field names and rules

### 4. Business Rule Exceptions

**Purpose:** Policy enforcement failures, authorization failures, eligibility check failures, business constraint violations that depend on external state.

**Base Class:** `App\Exceptions\BusinessRuleException extends \RuntimeException`

**Examples:**

- `InsufficientBalanceException` — Payment allocation exceeds available balance
- `SuspensionNotEligibleException` — Subscription cannot be suspended (active payment plan, grace period)
- `DuplicateSubscriptionException` — Customer already has active subscription at same address
- `InvoiceGenerationNotDueException` — Billing period not yet elapsed
- `PackageNotAvailableException` — Package discontinued or not available in customer's service area

**When to Use:**

- Business policy enforcement failures
- Eligibility checks that query external state (e.g., checking invoice balance)
- Cross-aggregate business constraints (e.g., customer has active subscription)

**Logging Responsibility:**

- Log Level: `notice` (expected business constraint) or `warning` (unexpected constraint violation)
- Log Channel: `business`
- Include: Entity type, entity ID, rule violated, actor ID, constraint details

**User-Facing Message:**

- Explain constraint: "This customer already has an active subscription."
- Guide resolution: "Please suspend or terminate the existing subscription before creating a new one."
- Provide context: "Invoice generation is not due until 2026-07-15."

**Developer Message:**

- Rule reference: "Business rule violated: DuplicateActiveSubscription. See SubscriptionPolicy::canCreateForCustomer()"
- Constraint details: "Customer[456] has active Subscription[789] at same address (Cluster[12], ServiceArea[3])"

### 5. Concurrency Exceptions

**Purpose:** Concurrent access failures, locking failures, optimistic lock failures, deadlock failures.

**Base Class:** `App\Exceptions\ConcurrencyException extends \RuntimeException`

**Examples:**

- `OptimisticLockException` — Version mismatch during update (entity modified by another transaction)
- `PessimisticLockException` — Unable to acquire lock (timeout or deadlock)
- `DeadlockException` — Database deadlock detected (MySQL error 1213)
- `ResourceLockedException` — Resource locked by another process
- `ConcurrentModificationException` — Entity modified during read-then-write operation

**When to Use:**

- Optimistic locking version mismatch
- `lockForUpdate()` timeout
- Database deadlock detection
- Race condition detection

**Logging Responsibility:**

- Log Level: `warning` (expected under high concurrency) or `error` (retry exhausted)
- Log Channel: `application`
- Include: Entity type, entity ID, lock type (optimistic/pessimistic), retry attempt, actor ID

**User-Facing Message:**

- Non-technical: "This record was modified by another user. Please refresh and try again."
- Actionable: "Please retry your operation."
- Never expose: "Deadlock detected" or technical lock details

**Developer Message:**

- Lock details: "Optimistic lock failed for Subscription[123]. Expected version: 5. Current version: 6."
- Deadlock info: "Deadlock detected on Payment[456] after 3 retry attempts. See docs/architecture/transactions.md for lock ordering."

### 6. Configuration Exceptions

**Purpose:** Configuration errors, missing environment variables, invalid settings, registry errors.

**Base Class:** `App\Exceptions\ConfigurationException extends \RuntimeException`

**Examples:**

- `MissingConfigurationException` — Required configuration key not set (e.g., `PAYMENT_GATEWAY_URL`)
- `InvalidConfigurationException` — Configuration value invalid (e.g., `BILLING_DAY` = 32)
- `SettingNotFoundException` — Required setting not found in Settings Engine
- `RegistryEntryNotFoundException` — Required registry entry missing (e.g., Email Template)
- `EnvironmentMismatchException` — Operation not allowed in current environment (e.g., production data seeding)

**When to Use:**

- Application bootstrap fails due to missing config
- Service cannot initialize due to invalid configuration
- Required setting missing from Settings Engine
- Registry-driven feature not configured

**Logging Responsibility:**

- Log Level: `critical` (application cannot start) or `error` (feature unavailable)
- Log Channel: `application`
- Include: Configuration key, expected value type, current value (sanitized — no secrets)

**User-Facing Message:**

- Generic: "A configuration error has occurred. Please contact support."
- Never expose: Configuration keys, file paths, environment variable names

**Developer Message:**

- Specific: "Required configuration key [services.payment_gateway.url] is missing. Set PAYMENT_GATEWAY_URL in .env"
- Remediation: "Run 'php artisan config:cache' after updating configuration."

### 7. Infrastructure Exceptions

**Purpose:** External system failures, database failures, cache failures, queue failures, file system failures, network failures.

**Base Class:** `App\Exceptions\InfrastructureException extends \RuntimeException`

**Examples:**

- `DatabaseException` — Database connection failed or query execution failed
- `CacheException` — Cache read/write failed
- `QueueException` — Job dispatch failed or queue connection unavailable
- `FileSystemException` — File read/write/delete failed
- `NetworkException` — HTTP request failed (timeout, DNS failure)
- `ExternalApiException` — Third-party API returned error or is unavailable
- `PaymentGatewayException` — Payment gateway API failed
- `OltProvisioningException` — OLT SNMP provisioning failed
- `EmailDeliveryException` — SMTP email delivery failed
- `SmsDeliveryException` — SMS gateway delivery failed

**When to Use:**

- Database connectivity or query failures
- Cache/queue/filesystem unavailable
- External API calls fail (HTTP, SNMP, SMTP, SMS)
- Network timeouts or DNS failures

**Logging Responsibility:**

- Log Level: `error` (infrastructure failure) or `critical` (critical service unavailable)
- Log Channel: `infrastructure` or specific channel (`database`, `external_api`)
- Include: Infrastructure component, operation attempted, error code/message, retry attempt

**User-Facing Message:**

- Generic: "A system error occurred. Please try again later."
- Service-specific: "Payment processing is temporarily unavailable. Please try again in a few minutes."
- Never expose: Connection strings, API endpoints, credentials

**Developer Message:**

- Detailed: "Database connection failed: SQLSTATE[HY000] [2002] Connection refused on 127.0.0.1:3306"
- API errors: "Payment gateway returned 503 Service Unavailable. Transaction ID: abc123. Endpoint: POST /api/v1/charges"

---

## Exception Hierarchy

### Hierarchy Structure

```
\Exception (PHP built-in)
├── \RuntimeException (PHP built-in)
│   ├── App\Exceptions\ApplicationException
│   │   ├── RouteNotFoundException
│   │   ├── MiddlewareException
│   │   ├── RateLimitExceededException
│   │   ├── MaintenanceModeException
│   │   └── CsrfTokenMismatchException
│   │
│   ├── App\Exceptions\DomainException
│   │   ├── InvalidStateException
│   │   ├── AggregateRootNotFoundException
│   │   ├── EntityConstraintViolationException
│   │   ├── LifecycleTransitionException
│   │   └── ImmutabilityViolationException
│   │
│   ├── App\Exceptions\BusinessRuleException
│   │   ├── InsufficientBalanceException
│   │   ├── SuspensionNotEligibleException
│   │   ├── DuplicateSubscriptionException
│   │   ├── InvoiceGenerationNotDueException
│   │   └── PackageNotAvailableException
│   │
│   ├── App\Exceptions\ConcurrencyException
│   │   ├── OptimisticLockException
│   │   ├── PessimisticLockException
│   │   ├── DeadlockException
│   │   ├── ResourceLockedException
│   │   └── ConcurrentModificationException
│   │
│   ├── App\Exceptions\ConfigurationException
│   │   ├── MissingConfigurationException
│   │   ├── InvalidConfigurationException
│   │   ├── SettingNotFoundException
│   │   ├── RegistryEntryNotFoundException
│   │   └── EnvironmentMismatchException
│   │
│   └── App\Exceptions\InfrastructureException
│       ├── DatabaseException
│       ├── CacheException
│       ├── QueueException
│       ├── FileSystemException
│       ├── NetworkException
│       ├── ExternalApiException
│       │   ├── PaymentGatewayException
│       │   ├── OltProvisioningException
│       │   ├── EmailDeliveryException
│       │   └── SmsDeliveryException
│       └── StorageException
│
└── \LogicException (PHP built-in)
    └── App\Exceptions\ProgrammingException
        ├── InvalidArgumentException (PHP built-in — use directly)
        ├── UnexpectedValueException (PHP built-in — use directly)
        └── BadMethodCallException (PHP built-in — use directly)
```

### Base Exception Classes

**Directory Structure:**

```
app/Exceptions/
├── ApplicationException.php
├── DomainException.php
├── BusinessRuleException.php
├── ConcurrencyException.php
├── ConfigurationException.php
├── InfrastructureException.php
├── Application/
│   ├── RouteNotFoundException.php
│   └── RateLimitExceededException.php
├── Domain/
│   ├── InvalidStateException.php
│   └── ImmutabilityViolationException.php
├── BusinessRule/
│   ├── InsufficientBalanceException.php
│   └── SuspensionNotEligibleException.php
├── Concurrency/
│   ├── OptimisticLockException.php
│   └── DeadlockException.php
├── Configuration/
│   ├── MissingConfigurationException.php
│   └── SettingNotFoundException.php
└── Infrastructure/
    ├── DatabaseException.php
    ├── ExternalApiException.php
    ├── PaymentGatewayException.php
    └── OltProvisioningException.php
```

### Hierarchy Rules

**Rule 1: Extend Appropriate Base Class**

Every custom exception MUST extend one of the seven base exception classes.

**❌ Wrong:**

```php
class InvoiceNotFoundException extends \Exception {}
```

**✅ Correct:**

```php
class InvoiceNotFoundException extends DomainException {}
```

**Rule 2: Base Classes Extend RuntimeException**

All seven base exception classes extend `\RuntimeException`.

**Why RuntimeException:**

- Most application exceptions are runtime failures (business logic, infrastructure)
- `\LogicException` is for programming errors (invalid arguments) — use PHP built-in `InvalidArgumentException`

**Rule 3: Never Extend Generic \Exception**

Never extend `\Exception` directly. Always extend a base class or `\RuntimeException`.

**Rule 4: Use PHP Built-in for Programming Errors**

For programming errors (developer mistakes), use PHP built-in exceptions:

- `\InvalidArgumentException` — Invalid method argument
- `\UnexpectedValueException` — Return value doesn't match expected type
- `\BadMethodCallException` — Method called in invalid context

**Example:**

```php
public function setPackage(Package $package): void
{
    if ($package->status !== PackageStatus::Active) {
        throw new \InvalidArgumentException(
            "Cannot assign inactive package. Package[{$package->id}] status: {$package->status->value}"
        );
    }
}
```

---

## Exception Naming Conventions

### Pattern: `{Concept}{Failure}Exception`

**Examples:**

- `InvalidStateException` — State is invalid
- `PaymentGatewayException` — Payment gateway failed
- `OptimisticLockException` — Optimistic lock failed
- `SettingNotFoundException` — Setting not found

### Naming Rules

**Rule 1: Use Descriptive Nouns**

Exception name should clearly indicate **what failed**.

**✅ Good:**

- `InsufficientBalanceException`
- `DuplicateSubscriptionException`
- `InvoiceGenerationNotDueException`

**❌ Bad:**

- `Exception` (too generic)
- `Error` (ambiguous)
- `Failure` (not specific)

**Rule 2: Include Domain Concept**

For domain/business exceptions, include entity or concept name.

**✅ Good:**

- `SubscriptionActivationException`
- `InvoicePublicationException`
- `PaymentAllocationException`

**❌ Bad:**

- `ActivationException` (activation of what?)
- `PublicationException` (publication of what?)

**Rule 3: Suffix Always "Exception"**

All exception classes MUST end with "Exception".

**Rule 4: Avoid Redundancy**

Don't repeat base class name.

**❌ Redundant:**

- `DomainInvalidStateException` (base class is `DomainException`)
- `InfrastructureDatabaseException` (base class is `InfrastructureException`)

**✅ Concise:**

- `InvalidStateException extends DomainException`
- `DatabaseException extends InfrastructureException`

---

## Exception Properties

### Required Properties

**Every exception class MUST support:**

1. **Message** — Human-readable description (via constructor `$message` parameter)
2. **Code** — Error code (via constructor `$code` parameter) — optional but recommended
3. **Previous** — Wrapped exception (via constructor `$previous` parameter)

**Optional Properties (recommended for context):**

4. **Entity Type** — For domain/business exceptions (`string $entityType`)
5. **Entity ID** — For domain/business exceptions (`int|string $entityId`)
6. **Context** — Additional data for logging (`array $context`)

### Example Exception Class

```php
<?php

namespace App\Exceptions\Domain;

use App\Exceptions\DomainException;

class InvalidStateException extends DomainException
{
    public function __construct(
        string $message,
        public readonly ?string $entityType = null,
        public readonly int|string|null $entityId = null,
        public readonly ?string $currentState = null,
        public readonly ?string $expectedState = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    public function context(): array
    {
        return array_filter([
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'current_state' => $this->currentState,
            'expected_state' => $this->expectedState,
        ]);
    }
}
```

### Using Context for Logging

**Exception Handler logs context automatically:**

```php
// app/Exceptions/Handler.php
public function report(Throwable $exception): void
{
    if (method_exists($exception, 'context')) {
        Log::error($exception->getMessage(), $exception->context());
    }
    
    parent::report($exception);
}
```

---

## Message Guidelines

### Two Message Audiences

**Every exception must consider TWO audiences:**

1. **Users** — Non-technical, actionable, friendly
2. **Developers** — Technical, detailed, diagnostic

### User-Facing Messages

**Characteristics:**

- Non-technical language
- Actionable (what can user do?)
- Friendly tone
- Never expose: stack traces, file paths, SQL, class names, server IPs

**Examples:**

| Bad (Technical) | Good (User-Friendly) |
|---|---|
| "SQLSTATE[23000]: Integrity constraint violation" | "This customer already exists." |
| "Class App\Services\BillingService not found" | "A system error occurred. Please contact support." |
| "Call to undefined method on null" | "The requested subscription was not found." |
| "Deadlock found when trying to get lock" | "Please refresh and try again." |

### Developer Messages

**Characteristics:**

- Technical details
- Entity IDs and types
- Current state and expected state
- Workflow/architecture document references
- Exception chain context

**Examples:**

```php
throw new InvalidStateException(
    "Cannot activate Subscription[123]. Current state: Active. Expected: PendingActivation or Suspended. See docs/workflows/subscription-lifecycle.md",
    entityType: 'Subscription',
    entityId: 123,
    currentState: 'Active',
    expectedState: 'PendingActivation|Suspended'
);
```

### Message Templates

**Domain Exception Template:**

```
Cannot {operation} {EntityType}[{id}]. Current state: {current}. Expected: {expected}. See {workflow_doc}.
```

**Business Rule Exception Template:**

```
{Rule} violated for {EntityType}[{id}]. Reason: {reason}. See {policy_class}.
```

**Infrastructure Exception Template:**

```
{Service} {operation} failed. Error: {error_message}. Endpoint: {endpoint}. Retry attempt: {attempt}.
```

### Message Localization

**User-facing messages SHOULD be localized** using Laravel's translation system.

```php
throw new InsufficientBalanceException(
    __('exceptions.insufficient_balance', [
        'available' => $availableBalance,
        'required' => $requiredAmount,
    ])
);
```

**Developer messages should NOT be localized** — always English for logs and diagnostics.

---

## Logging Responsibilities

### Log Level by Exception Type

| Exception Type | Log Level | Channel | Rationale |
|---|---|---|---|
| **ValidationException** | `info` | `application` | Expected user errors |
| **DomainException** | `notice` | `business` | Expected business failures |
| **BusinessRuleException** | `notice` | `business` | Expected constraint violations |
| **ApplicationException** (4xx) | `warning` | `application` | Client errors |
| **ApplicationException** (5xx) | `error` | `application` | Server errors |
| **ConcurrencyException** | `warning` | `application` | Expected under load; `error` if retry exhausted |
| **ConfigurationException** | `critical` | `application` | Configuration prevents operation |
| **InfrastructureException** | `error` | `infrastructure` | Infrastructure failure |
| **ExternalApiException** | `error` | `external_api` | External integration failure |

### Exception Handler Logging

**Global Exception Handler** (`app/Exceptions/Handler.php`) automatically logs exceptions based on type.

**Implementation Pattern:**

```php
public function report(Throwable $exception): void
{
    // Log with appropriate level and channel based on exception type
    if ($exception instanceof ValidationException) {
        Log::channel('application')->info('Validation failed', [
            'errors' => $exception->errors(),
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
        ]);
        return; // Don't call parent — already logged
    }
    
    if ($exception instanceof DomainException || $exception instanceof BusinessRuleException) {
        Log::channel('business')->notice($exception->getMessage(), [
            'exception' => get_class($exception),
            'entity_type' => $exception->entityType ?? null,
            'entity_id' => $exception->entityId ?? null,
            'actor_id' => auth()->id(),
        ]);
    }
    
    if ($exception instanceof ConcurrencyException) {
        Log::channel('application')->warning($exception->getMessage(), [
            'exception' => get_class($exception),
            'entity_type' => $exception->entityType ?? null,
            'entity_id' => $exception->entityId ?? null,
        ]);
    }
    
    if ($exception instanceof ConfigurationException) {
        Log::channel('application')->critical($exception->getMessage(), [
            'exception' => get_class($exception),
            'config_key' => $exception->configKey ?? null,
        ]);
    }
    
    if ($exception instanceof InfrastructureException) {
        Log::channel('infrastructure')->error($exception->getMessage(), [
            'exception' => get_class($exception),
            'component' => $exception->component ?? null,
        ]);
    }
    
    parent::report($exception);
}
```

### Context Logging

**Always log structured context** for filtering and analysis:

**Required Context:**

- Exception class name
- Exception message
- Actor ID (authenticated user)
- Timestamp (automatic)

**Domain/Business Exception Context:**

- Entity type
- Entity ID
- Current state
- Expected state

**Infrastructure Exception Context:**

- Component (database, cache, queue, external API)
- Operation attempted
- Error code/message from infrastructure
- Retry attempt number

**Concurrency Exception Context:**

- Lock type (optimistic, pessimistic)
- Version mismatch details
- Retry attempt number

### Security Logging

**Security-sensitive exceptions MUST log to `security` channel:**

- Unauthorized access attempts (`AuthorizationException`)
- CSRF token mismatches (`CsrfTokenMismatchException`)
- Authentication failures (covered in Auth module)
- Suspicious patterns (rate limit exceeded from single IP)

**Log at `warning` level minimum; `error` for repeated violations.**

---

## Wrapping Third-Party Exceptions

### Why Wrap Third-Party Exceptions

**Third-party libraries throw their own exceptions:**

- Guzzle: `GuzzleHttp\Exception\RequestException`
- PDO: `\PDOException`
- Redis: `RedisException`
- SNMP: `SNMPException`

**Without wrapping:**

- Domain layer depends on infrastructure library exception classes
- Catch blocks are fragile (tied to specific library versions)
- Replacing library requires updating all catch blocks
- Exception logs lack business context

**With wrapping:**

- Domain layer catches domain exceptions (`PaymentGatewayException`)
- Catch blocks are stable (independent of library)
- Replacing library only requires updating wrapper
- Exception logs include business context

### Wrapping Pattern

**Wrap at the boundary where third-party code is called.**

**Example — HTTP Client:**

```php
<?php

namespace App\Services\External;

use App\Exceptions\Infrastructure\PaymentGatewayException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PaymentGatewayService
{
    public function charge(int $amount, string $customerId): string
    {
        try {
            $response = $this->client->post('/charges', [
                'json' => [
                    'amount' => $amount,
                    'customer_id' => $customerId,
                ],
            ]);
            
            return json_decode($response->getBody())->transaction_id;
            
        } catch (RequestException $e) {
            throw new PaymentGatewayException(
                "Payment gateway charge failed: {$e->getMessage()}",
                component: 'payment_gateway',
                operation: 'charge',
                amount: $amount,
                customerId: $customerId,
                statusCode: $e->getResponse()?->getStatusCode(),
                previous: $e  // Preserve original exception
            );
        }
    }
}
```

**Key Points:**

1. **Catch specific third-party exception** — `RequestException`, not generic `\Exception`
2. **Throw domain exception** — `PaymentGatewayException` with business context
3. **Preserve original exception** — Pass as `$previous` for stack trace
4. **Add context** — Amount, customer ID, operation, status code

### Database Exception Wrapping

**Laravel already wraps PDO exceptions** as `Illuminate\Database\QueryException`.

**Catch QueryException and wrap with domain context:**

```php
use Illuminate\Database\QueryException;
use App\Exceptions\Infrastructure\DatabaseException;

try {
    Invoice::create($data);
} catch (QueryException $e) {
    throw new DatabaseException(
        "Invoice creation failed: {$e->getMessage()}",
        component: 'database',
        operation: 'insert',
        table: 'invoices',
        sqlState: $e->errorInfo[0] ?? null,
        previous: $e
    );
}
```

### SNMP Exception Wrapping

**OLT provisioning via SNMP:**

```php
use App\Exceptions\Infrastructure\OltProvisioningException;

try {
    $this->snmpClient->set($oid, $value);
} catch (\SNMPException $e) {
    throw new OltProvisioningException(
        "ONU provisioning failed: {$e->getMessage()}",
        component: 'olt',
        operation: 'provision_onu',
        onuId: $this->onuId,
        oltId: $this->oltId,
        previous: $e
    );
}
```

### SMTP Exception Wrapping

**Email delivery:**

```php
use App\Exceptions\Infrastructure\EmailDeliveryException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

try {
    Mail::to($recipient)->send(new InvoiceEmail($invoice));
} catch (TransportExceptionInterface $e) {
    throw new EmailDeliveryException(
        "Email delivery failed: {$e->getMessage()}",
        component: 'smtp',
        operation: 'send_email',
        recipient: $recipient,
        subject: 'Invoice',
        previous: $e
    );
}
```

### Wrapping Guidelines

**Rule 1: Wrap at Service Layer**

Wrap third-party exceptions in Services, not Controllers or Models.

**Rule 2: Preserve Original Exception**

Always pass third-party exception as `$previous` parameter.

**Rule 3: Add Business Context**

Include entity IDs, operation details, and relevant business data.

**Rule 4: Don't Wrap Everywhere**

Only wrap exceptions that cross architectural boundaries (Service → Controller, Service → Queue Job).

**Rule 5: Don't Double-Wrap**

If exception is already wrapped, don't wrap again.

```php
// ❌ Wrong — double-wrapping
try {
    $this->service->processPayment($payment);
} catch (PaymentGatewayException $e) {
    throw new InfrastructureException("Payment failed", previous: $e); // Unnecessary
}

// ✅ Correct — let it propagate
try {
    $this->service->processPayment($payment);
} catch (PaymentGatewayException $e) {
    Log::error("Payment processing failed", ['payment_id' => $payment->id]);
    throw $e; // Re-throw without wrapping
}
```

---

## Exception Handling Patterns

### Pattern 1: Let It Propagate

**Default pattern:** Let exceptions propagate to global exception handler.

```php
public function store(StoreInvoiceRequest $request)
{
    $this->authorize('create', Invoice::class);
    
    // Let exceptions propagate — don't catch
    $invoice = $this->service->create($request->validated());
    
    return redirect()->route('invoices.show', $invoice);
}
```

**Global handler converts exception to HTTP response:**

- `ValidationException` → 422 Unprocessable Entity
- `AuthorizationException` → 403 Forbidden
- `DomainException` → 400 Bad Request
- `InfrastructureException` → 500 Internal Server Error

### Pattern 2: Catch and Re-throw

**Catch to add context, then re-throw.**

```php
public function activate(Subscription $subscription): void
{
    try {
        DB::transaction(function () use ($subscription) {
            $subscription->status = SubscriptionStatus::Active;
            $subscription->save();
        });
    } catch (QueryException $e) {
        Log::error('Subscription activation failed', [
            'subscription_id' => $subscription->id,
            'error' => $e->getMessage(),
        ]);
        
        throw new DatabaseException(
            "Failed to activate subscription {$subscription->id}",
            previous: $e
        );
    }
}
```

### Pattern 3: Catch and Handle

**Catch and handle gracefully (rare — only when recovery is possible).**

```php
public function generatePdf(Invoice $invoice): ?string
{
    try {
        return $this->pdfService->generate($invoice);
    } catch (PdfGenerationException $e) {
        Log::warning('PDF generation failed, will retry later', [
            'invoice_id' => $invoice->id,
        ]);
        
        // Queue job to retry later
        GenerateInvoicePdfJob::dispatch($invoice)->delay(now()->addMinutes(5));
        
        return null; // Graceful degradation
    }
}
```

### Pattern 4: Catch Specific, Re-throw Others

**Catch specific exceptions; let others propagate.**

```php
public function processPayment(Payment $payment): void
{
    try {
        $this->paymentGateway->charge($payment->amount);
    } catch (PaymentGatewayException $e) {
        // Handle specific exception
        Log::error('Payment gateway failed', ['payment_id' => $payment->id]);
        
        $payment->status = PaymentStatus::Failed;
        $payment->save();
        
        throw $e; // Re-throw for upstream handling
    }
    // Let other exceptions (DatabaseException, etc.) propagate
}
```

### Pattern 5: Catch for Retry Logic

**Catch to implement retry with backoff.**

```php
public function execute(Payment $payment): void
{
    $attempt = 0;
    
    while ($attempt < 3) {
        try {
            DB::transaction(function () use ($payment) {
                // Business logic
            });
            return; // Success
            
        } catch (DeadlockException $e) {
            $attempt++;
            
            if ($attempt >= 3) {
                throw $e; // Max retries exceeded
            }
            
            usleep([10, 50, 200][$attempt - 1] * 1000);
        }
    }
}
```

### Anti-Pattern: Catch Generic Exception

**❌ Never catch `\Exception` or `\Throwable` unless at application boundary (global handler).**

```php
// ❌ Wrong — too broad
try {
    $this->service->execute();
} catch (\Exception $e) {
    // Catches EVERYTHING — loses type information
}

// ✅ Correct — catch specific
try {
    $this->service->execute();
} catch (DomainException $e) {
    // Handle domain failures
} catch (InfrastructureException $e) {
    // Handle infrastructure failures
}
```

### Anti-Pattern: Swallow Exceptions

**❌ Never catch and ignore exceptions.**

```php
// ❌ Wrong — exception swallowed
try {
    $this->service->execute();
} catch (InfrastructureException $e) {
    // Do nothing — error is hidden
}

// ✅ Correct — log and re-throw
try {
    $this->service->execute();
} catch (InfrastructureException $e) {
    Log::error('Service execution failed', ['error' => $e->getMessage()]);
    throw $e;
}
```

---

## HTTP Response Mapping

### Exception to HTTP Status Code

**Global Exception Handler maps exceptions to HTTP status codes:**

| Exception Type | HTTP Status | Response Body |
|---|---|---|
| `ValidationException` | 422 Unprocessable Entity | JSON with validation errors |
| `AuthorizationException` | 403 Forbidden | "You are not authorized to perform this action." |
| `ModelNotFoundException` | 404 Not Found | "Resource not found." |
| `DomainException` | 400 Bad Request | User-facing message |
| `BusinessRuleException` | 400 Bad Request | User-facing message |
| `ConcurrencyException` | 409 Conflict | "This resource was modified. Please refresh." |
| `ConfigurationException` | 500 Internal Server Error | "A configuration error occurred." |
| `InfrastructureException` | 500 Internal Server Error | "A system error occurred." |
| `ApplicationException` | Varies | Based on exception subclass |

### Rendering Exceptions

**Handler render() method:**

```php
public function render($request, Throwable $exception)
{
    // JSON API response
    if ($request->expectsJson()) {
        return match(true) {
            $exception instanceof ValidationException => response()->json([
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
            ], 422),
            
            $exception instanceof DomainException => response()->json([
                'message' => $exception->getMessage(),
                'type' => 'domain_error',
            ], 400),
            
            $exception instanceof BusinessRuleException => response()->json([
                'message' => $exception->getMessage(),
                'type' => 'business_rule_violation',
            ], 400),
            
            $exception instanceof ConcurrencyException => response()->json([
                'message' => 'This resource was modified. Please refresh and try again.',
                'type' => 'concurrent_modification',
            ], 409),
            
            $exception instanceof InfrastructureException => response()->json([
                'message' => 'A system error occurred. Please try again later.',
                'type' => 'infrastructure_error',
            ], 500),
            
            default => parent::render($request, $exception),
        };
    }
    
    // HTML response (Blade view)
    return parent::render($request, $exception);
}
```

---

## Testing Guidelines

### Testing Exception Throwing

**Assert exception is thrown with correct type and message:**

```php
use App\Exceptions\Domain\InvalidStateException;

public function test_cannot_activate_already_active_subscription(): void
{
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::Active]);
    
    $this->expectException(InvalidStateException::class);
    $this->expectExceptionMessage('Cannot activate Subscription');
    
    $this->service->activate($subscription);
}
```

### Testing Exception Context

**Assert exception contains correct context:**

```php
public function test_invalid_state_exception_includes_context(): void
{
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::Active]);
    
    try {
        $this->service->activate($subscription);
        $this->fail('Expected InvalidStateException');
    } catch (InvalidStateException $e) {
        $this->assertEquals('Subscription', $e->entityType);
        $this->assertEquals($subscription->id, $e->entityId);
        $this->assertEquals('Active', $e->currentState);
        $this->assertEquals('PendingActivation', $e->expectedState);
    }
}
```

### Testing Exception Wrapping

**Assert third-party exception is wrapped correctly:**

```php
public function test_payment_gateway_exception_wraps_guzzle_exception(): void
{
    // Mock Guzzle client to throw RequestException
    $this->mock(Client::class, function ($mock) {
        $mock->shouldReceive('post')->andThrow(new RequestException(
            'Connection timeout',
            new Request('POST', '/charges')
        ));
    });
    
    $this->expectException(PaymentGatewayException::class);
    $this->expectExceptionMessage('Payment gateway charge failed');
    
    $this->service->charge(1000, 'customer-123');
}
```

### Testing Exception Logging

**Assert exception is logged with correct level and context:**

```php
public function test_domain_exception_logs_to_business_channel(): void
{
    Log::shouldReceive('channel')
        ->once()
        ->with('business')
        ->andReturnSelf();
    
    Log::shouldReceive('notice')
        ->once()
        ->with(
            Mockery::on(fn($msg) => str_contains($msg, 'Cannot activate')),
            Mockery::on(fn($context) => 
                $context['entity_type'] === 'Subscription' &&
                isset($context['entity_id'])
            )
        );
    
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::Active]);
    
    try {
        $this->service->activate($subscription);
    } catch (InvalidStateException $e) {
        // Exception expected
    }
}
```

### Testing HTTP Response

**Assert exception renders correct HTTP response:**

```php
public function test_domain_exception_returns_400_bad_request(): void
{
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::Active]);
    
    $response = $this->postJson(route('subscriptions.activate', $subscription));
    
    $response->assertStatus(400);
    $response->assertJson([
        'message' => 'This subscription is already active.',
        'type' => 'domain_error',
    ]);
}
```

---

## Migration Path

### Phase 1: Define Base Exception Classes (Sprint 1.5)

**Create seven base exception classes:**

- `App\Exceptions\ApplicationException`
- `App\Exceptions\DomainException`
- `App\Exceptions\BusinessRuleException`
- `App\Exceptions\ConcurrencyException`
- `App\Exceptions\ConfigurationException`
- `App\Exceptions\InfrastructureException`

**Update `Handler.php` to log exceptions by type.**

### Phase 2: Implement Domain/Business Exceptions (Sprint 1.5)

**Create exception classes for Identity & Access module:**

- `InvalidStateException` (user activation)
- `DuplicateUserException` (unique constraint)
- `RoleNotFoundException` (role assignment)

**Throw from Services; test exception throwing.**

### Phase 3: Implement Infrastructure Exceptions (Sprint 1.6)

**Create exception classes for external integrations:**

- `PaymentGatewayException`
- `OltProvisioningException`
- `EmailDeliveryException`
- `SmsDeliveryException`

**Wrap third-party exceptions at service boundaries.**

### Phase 4: Refactor Existing Code (Sprint 1.6)

**Search for generic `\Exception` usage:**

```bash
grep -r "throw new \\\\Exception" app/
```

**Replace with specific exception classes.**

**Before:**

```php
throw new \Exception("Subscription already active");
```

**After:**

```php
throw new InvalidStateException(
    "Cannot activate subscription. Already active.",
    entityType: 'Subscription',
    entityId: $subscription->id,
    currentState: 'Active',
    expectedState: 'PendingActivation'
);
```

### Phase 5: Update Tests (Sprint 1.6)

**Update all tests to assert specific exception classes:**

```php
// Before
$this->expectException(\Exception::class);

// After
$this->expectException(InvalidStateException::class);
```

---

## Examples

### Example 1: Domain Exception (Invalid State)

```php
<?php

namespace App\Exceptions\Domain;

use App\Exceptions\DomainException;

class InvalidStateException extends DomainException
{
    public function __construct(
        string $message,
        public readonly ?string $entityType = null,
        public readonly int|string|null $entityId = null,
        public readonly ?string $currentState = null,
        public readonly ?string $expectedState = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    public function context(): array
    {
        return array_filter([
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'current_state' => $this->currentState,
            'expected_state' => $this->expectedState,
        ]);
    }
}
```

**Usage in Service:**

```php
public function activate(Subscription $subscription): void
{
    if ($subscription->status === SubscriptionStatus::Active) {
        throw new InvalidStateException(
            "Cannot activate Subscription[{$subscription->id}]. Already active.",
            entityType: 'Subscription',
            entityId: $subscription->id,
            currentState: 'Active',
            expectedState: 'PendingActivation'
        );
    }
    
    DB::transaction(function () use ($subscription) {
        $subscription->status = SubscriptionStatus::Active;
        $subscription->save();
    });
}
```

### Example 2: Business Rule Exception

```php
<?php

namespace App\Exceptions\BusinessRule;

use App\Exceptions\BusinessRuleException;

class InsufficientBalanceException extends BusinessRuleException
{
    public function __construct(
        string $message,
        public readonly ?int $paymentId = null,
        public readonly ?int $availableBalance = null,
        public readonly ?int $requiredAmount = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    public function context(): array
    {
        return array_filter([
            'payment_id' => $this->paymentId,
            'available_balance' => $this->availableBalance,
            'required_amount' => $this->requiredAmount,
        ]);
    }
}
```

**Usage:**

```php
public function allocate(Payment $payment, Invoice $invoice, int $amount): void
{
    $availableBalance = $payment->amount - $payment->allocated_amount;
    
    if ($amount > $availableBalance) {
        throw new InsufficientBalanceException(
            "Payment allocation exceeds available balance. Available: {$availableBalance}, Required: {$amount}",
            paymentId: $payment->id,
            availableBalance: $availableBalance,
            requiredAmount: $amount
        );
    }
    
    // Allocate payment...
}
```

### Example 3: Infrastructure Exception (Payment Gateway)

```php
<?php

namespace App\Exceptions\Infrastructure;

use App\Exceptions\InfrastructureException;

class PaymentGatewayException extends InfrastructureException
{
    public function __construct(
        string $message,
        public readonly ?string $component = 'payment_gateway',
        public readonly ?string $operation = null,
        public readonly ?int $amount = null,
        public readonly ?string $customerId = null,
        public readonly ?int $statusCode = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    public function context(): array
    {
        return array_filter([
            'component' => $this->component,
            'operation' => $this->operation,
            'amount' => $this->amount,
            'customer_id' => $this->customerId,
            'status_code' => $this->statusCode,
        ]);
    }
}
```

**Usage with Third-Party Wrapping:**

```php
public function charge(int $amount, string $customerId): string
{
    try {
        $response = $this->client->post('/charges', [
            'json' => [
                'amount' => $amount,
                'customer_id' => $customerId,
            ],
        ]);
        
        return json_decode($response->getBody())->transaction_id;
        
    } catch (RequestException $e) {
        throw new PaymentGatewayException(
            "Payment gateway charge failed: {$e->getMessage()}",
            operation: 'charge',
            amount: $amount,
            customerId: $customerId,
            statusCode: $e->getResponse()?->getStatusCode(),
            previous: $e
        );
    }
}
```

### Example 4: Concurrency Exception (Optimistic Lock)

```php
<?php

namespace App\Exceptions\Concurrency;

use App\Exceptions\ConcurrencyException;

class OptimisticLockException extends ConcurrencyException
{
    public function __construct(
        string $message,
        public readonly ?string $entityType = null,
        public readonly int|string|null $entityId = null,
        public readonly ?int $expectedVersion = null,
        public readonly ?int $currentVersion = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    public function context(): array
    {
        return array_filter([
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'expected_version' => $this->expectedVersion,
            'current_version' => $this->currentVersion,
        ]);
    }
}
```

**Usage:**

```php
public function update(Subscription $subscription, array $data): void
{
    DB::transaction(function () use ($subscription, $data) {
        $expectedVersion = $subscription->version;
        
        $updated = Subscription::where('id', $subscription->id)
            ->where('version', $expectedVersion)
            ->update([
                'package_id' => $data['package_id'],
                'version' => $expectedVersion + 1,
            ]);
        
        if ($updated === 0) {
            throw new OptimisticLockException(
                "Subscription was modified by another transaction. Please refresh and try again.",
                entityType: 'Subscription',
                entityId: $subscription->id,
                expectedVersion: $expectedVersion,
                currentVersion: $expectedVersion + 1 // Approximate
            );
        }
    });
}
```

### Example 5: Configuration Exception

```php
<?php

namespace App\Exceptions\Configuration;

use App\Exceptions\ConfigurationException;

class SettingNotFoundException extends ConfigurationException
{
    public function __construct(
        string $message,
        public readonly ?string $settingKey = null,
        public readonly ?string $scope = null,
        public readonly int|string|null $scopeId = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    public function context(): array
    {
        return array_filter([
            'setting_key' => $this->settingKey,
            'scope' => $this->scope,
            'scope_id' => $this->scopeId,
        ]);
    }
}
```

**Usage:**

```php
public function get(string $key, string $scope = 'global', int|string|null $scopeId = null): mixed
{
    $setting = Setting::where('key', $key)
        ->where('scope', $scope)
        ->where('scope_id', $scopeId)
        ->first();
    
    if (!$setting) {
        throw new SettingNotFoundException(
            "Setting [{$key}] not found for scope [{$scope}:{$scopeId}]",
            settingKey: $key,
            scope: $scope,
            scopeId: $scopeId
        );
    }
    
    return $setting->value;
}
```

---

## Quick Reference

### Exception Type Selection

| Scenario | Exception Type | Base Class |
|---|---|---|
| Invalid entity state | Domain | `InvalidStateException` |
| Entity not found | Domain | `AggregateRootNotFoundException` |
| Workflow violation | Domain | `LifecycleTransitionException` |
| Business rule violated | Business Rule | Specific (e.g., `InsufficientBalanceException`) |
| User input invalid | Validation | `ValidationException` (Laravel) |
| Optimistic lock failed | Concurrency | `OptimisticLockException` |
| Deadlock detected | Concurrency | `DeadlockException` |
| Setting not found | Configuration | `SettingNotFoundException` |
| Database failed | Infrastructure | `DatabaseException` |
| External API failed | Infrastructure | Specific (e.g., `PaymentGatewayException`) |

### Log Level by Exception

| Exception Type | Log Level | Channel |
|---|---|---|
| ValidationException | `info` | `application` |
| DomainException | `notice` | `business` |
| BusinessRuleException | `notice` | `business` |
| ConcurrencyException | `warning` | `application` |
| ConfigurationException | `critical` | `application` |
| InfrastructureException | `error` | `infrastructure` |

### Exception Handling Decision Tree

```
Does exception need handling?
├─ YES → Can operation be recovered?
│  ├─ YES → Catch, recover, log, continue
│  └─ NO → Catch, log, re-throw
└─ NO → Let it propagate to global handler
```

### Wrapping Checklist

- [ ] Catch specific third-party exception (not `\Exception`)
- [ ] Throw domain exception with business context
- [ ] Pass original exception as `$previous`
- [ ] Include entity IDs, operation details, error codes
- [ ] Log wrapped exception at appropriate level

---

## Summary

### Key Principles

1. **Structured Taxonomy** — Seven exception categories aligned with architecture layers
2. **Clear Ownership** — Services throw exceptions; global handler logs and renders
3. **Two Audiences** — User-facing messages (actionable, friendly) vs developer messages (technical, detailed)
4. **Context is King** — Exceptions carry entity IDs, states, operation details for diagnostics
5. **Wrap at Boundaries** — Third-party exceptions wrapped at service layer with domain context
6. **Log by Type** — Exception type determines log level and channel automatically
7. **Propagate by Default** — Let exceptions propagate unless recovery is possible

### Architecture Impact

This exception standard establishes:

- Semantic exception hierarchy aligned with domain/infrastructure layers
- Logging responsibilities by exception type and severity
- Message formatting for users vs developers
- Third-party exception wrapping patterns
- HTTP response mapping conventions
- Testing approach for exception throwing and handling

**Complements:**

- **Platform Logging Standard** — Exception logging integrated with log levels and channels
- **Platform Transaction Standard** — Concurrency exceptions for deadlocks and locks
- **Domain Event Architecture** — Exceptions do NOT dispatch events (events only on success)
- **Queue Architecture** — Infrastructure exceptions for queue/job failures

### Migration Path

**Sprint 1.5:** Define base classes, implement Identity & Access exceptions  
**Sprint 1.6:** Implement infrastructure exceptions, wrap third-party exceptions, refactor existing code  

---

**End of Document**
