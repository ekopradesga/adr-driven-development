# GitHub Copilot Instructions
# ISP Management System — AI Development Guide

**Architecture Version:** 1.0 (Frozen)

**Stack:** Laravel 10 · PHP 8.x · MySQL/MariaDB · Blade · Bootstrap · Alpine.js

---

## 1. Project Mission

This is an enterprise-grade ISP Management Platform covering the complete operational lifecycle from customer acquisition through service termination. It replaces fragmented tools with a single, event-driven, auditable system.

The platform is built on an **Architecture-First philosophy**. Documentation defines the system. Code expresses it. Every module, entity, workflow, and business event has been formally specified before implementation begins.

The goal is a coherent, maintainable, production-ready system — not fast prototyping.

---

## 2. Source of Truth

Architecture documentation **always takes priority over generated code**.

> **Architecture Decisions → Workflows → Database Design → Source Code**

When implementation conflicts with documentation:
- Do not silently change behavior or invent alternatives.
- Stop and surface the conflict.
- Propose a documentation update or implementation correction.
- Wait for explicit direction before proceeding.

Never introduce new business behavior without a corresponding architecture decision or workflow update.

---

## 3. Documentation Reading Order

Always consult documentation in this order before generating any code, migration, event, or service:

1. `docs/architecture/decisions.md` — authoritative architecture decisions and principles
2. `docs/architecture/glossary.md` — canonical terminology (use exact glossary terms in code)
3. `docs/project/project-scope.md` — approved feature boundaries and out-of-scope list
4. `docs/architecture/business-events.md` — all documented business events and their contracts
5. Relevant workflow document(s) in `docs/workflows/` — lifecycle states, transitions, and business rules
6. `docs/database/entities.md` — entity definitions, ownership, classification, and immutability
7. `docs/database/erd.md` — authoritative relationship model, cardinalities, and cascade behavior

**Never skip steps 1–5 before working on a module for the first time.**

**Out-of-scope for Version 1.0:** Accounting, Payroll, HR, Advanced Warehouse, Advanced Inventory, CRM Marketing, POS, E-Commerce, Multi-Company, Franchise, AI autonomous decision-making. If a feature is not in `project-scope.md`, do not implement it — inform the developer and suggest adding it to the Architecture Backlog.

---

## 4. Technology Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 10 |
| Language | PHP 8.x |
| Database | MySQL / MariaDB |
| Views | Blade templates |
| CSS | Bootstrap |
| Interactivity | Alpine.js (lightweight; prefer over heavy JS) |
| Queue | Laravel Queue (database or Redis driver) |
| Auth | Laravel Sanctum / built-in auth |

- Do **not** assume Laravel 11+ features unless explicitly requested.
- Do **not** introduce Vue, React, or Livewire unless explicitly approved.
- Prefer Blade components and Alpine.js for interactive UI.
- Use Laravel conventions for everything: naming, routing, directory structure.

---

## 5. Architecture Rules

### Workflows
Every module is governed by a documented business workflow. Implementation must follow lifecycle states and transitions **exactly as defined**.

- Never bypass documented workflow states.
- Never invent undocumented state transitions.
- Every state transition must generate a `TimelineEvent` and an `ActivityLog` entry.
- Lifecycle states and their business rules are authoritative.

Workflow documents: `docs/workflows/subscription-lifecycle.md`, `billing-workflow.md`, `payment-workflow.md`, `collector-workflow.md`, `provisioning-workflow.md`, `network-monitoring-workflow.md`, `ticket-workflow.md`, `notification-workflow.md`

### Business Events
All inter-module communication must use **documented business events** from `business-events.md`.

- Use Laravel Events and Listeners for business event dispatch and consumption.
- Never create undocumented events.
- Event producers must not know consumers.
- Business events are immutable once dispatched.

### Aggregate Boundaries
Respect the aggregate roots defined in `entities.md`:

`Customer` · `Subscription` · `Invoice` · `Payment` · `CollectionTask` · `Ticket` · `OLT` · `User` · `Notification` · `Package` · `Employee` · `Cluster` · `ServiceArea` · `MaintenanceWindow` · `EventCatalogEntry`

- Child entities must be accessed through their aggregate root.
- Never create a FK that violates aggregate ownership.

### Shared Platform Components
Always reuse. Never duplicate.

| Component | Laravel Implementation |
|---|---|
| Timeline | `TimelineEvent` model + MorphMany |
| Activity Log | `ActivityLog` model + MorphMany |
| Notification Engine | Laravel Notifications + `EventCatalogEntry` governance |
| Attachment System | `Attachment` model + MorphMany |
| Settings Engine | `Setting` / `SettingRegistryEntry` models |
| Global Search | `SearchIndex` model + full-text or Scout |
| Universal Workspace | Blade layout component — Entity 360 tabs |

### Configuration Over Hardcoding
All policy-driven values belong in the Settings Engine. Never hardcode:
- Billing calendar values or due date rules
- Notification schedules, channels, or retry policies
- Monitoring health thresholds or alert rules
- Suspension eligibility criteria
- SLA targets or escalation thresholds
- Assignment policies

### Metadata Driven CRUD
Prefer metadata-driven list/form/detail pages. Build custom pages only when the business requirement cannot be expressed through the metadata engine.

---

## 6. Database Rules

- Follow `erd.md` exactly. Do not invent tables.
- Every FK must match the cardinality documented in the ERD.
- Use foreign key constraints in all migrations.
- Respect deletion behaviors per entity: `Soft Delete`, `Restrict`, `Archive`, `Cascade`.
- **Immutability rules must be enforced in code:**
  - `Invoice` (after publication), `Payment`, `ActivityLog`, `TimelineEvent`, `StateTransitionLog`, `MonitoringEvent`, `NotificationDeliveryAttempt`, `InvoiceItem` — never update or delete these records.
  - Corrections to financial records use new corrective records, never edits.
- Polymorphic relationships (`TimelineEvent`, `ActivityLog`, `StateTransitionLog`, `Attachment`, `SearchIndex`, `QRCodeReference`) use Laravel `morphTo()` / `morphMany()`.
- All policy-driven values belong in `Setting` — never hardcode billing rules, SLA thresholds, notification schedules, or suspension policies.

**Entity Classification (from `entities.md`):**

| Classification | Entities |
|---|---|
| Core | Customer, Subscription, Package, Employee, Cluster, ServiceArea |
| Transaction | Invoice, Payment, CollectionTask, Survey, Installation, ProvisioningRequest, SuspensionCase, PaymentAllocation |
| Infrastructure | OLT, ODF, FAT, ONT, ONU, MonitoringEvent, DeviceHealthState, MaintenanceWindow, BillingPeriod, Dropcore |
| Platform | Notification, Attachment, TimelineEvent, ActivityLog, StateTransitionLog, Setting, SearchIndex, EventCatalogEntry |
| Operational | Ticket, TicketAssignment, TicketComment, ServiceRequest, WorkOrder, CollectionTaskInvoice |
| Identity | User, Role, Permission, UserSession, ImpersonationSession |

---

## 7. Laravel Coding Standards

### Architecture Layers

```
HTTP Layer        →  FormRequest, Controller, Policy
Application Layer →  Service (one class per business operation)
Domain Layer      →  Model, Event, Listener, Job
Data Layer        →  Repository (where abstraction adds value), Eloquent
```

- **Controllers** prepare and pass data. Zero business logic.
- **Services** own business operations. Small, single-responsibility.
- **FormRequests** for all user input validation — never validate in controllers.
- **Policies** for all authorization — always call `$this->authorize()` in controllers.
- **Eloquent Relationships** must match ERD cardinalities exactly. Define both sides.
- **Queued Jobs** for all external integrations: notification delivery, provisioning, monitoring sync.
- **Events and Listeners** for all business event dispatch and inter-module communication.
- **Resource Controllers** for standard CRUD: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.

### Example — Correct Layer Separation

```php
// Service owns business logic
class ActivateSubscriptionService
{
    public function execute(Subscription $subscription): void
    {
        // Business validation
        // State transition
        // Timeline entry
        event(new SubscriptionActivated($subscription));
    }
}

// Controller only prepares and delegates
public function activate(ActivateRequest $request, Subscription $subscription)
{
    $this->authorize('activate', $subscription);
    $this->service->execute($subscription);
    return redirect()->route('subscriptions.show', $subscription);
}
```

### Queued Jobs
- Use `ShouldQueue` for: notification delivery, provisioning requests, monitoring sync, PDF generation, report generation.
- Implement `failed()` to log failures to ActivityLog.
- Set appropriate `$tries` and `$backoff` values.

### Implementation Preferences
- Prefer Route Model Binding over manual `find()` lookups.
- Prefer Laravel Collections over raw arrays.
- Prefer Eloquent over Query Builder unless performance requires otherwise.
- Prefer Enums over magic strings for fixed value sets.
- Prefer Carbon for all date and time handling.
- Prefer constructor Dependency Injection over Facades in business services.

### Testing
- Generate Feature Tests for all HTTP endpoints.
- Generate Unit Tests for all business services.
- Business logic must be independently testable via injected dependencies.

### Performance
- Prevent N+1 queries — always use `with()` for eager loading.
- Use pagination for all large dataset queries.
- Queue all long-running operations.

### Security
- Validate all user input via FormRequest.
- Always authorize protected actions via Policies.
- Never trust client input — validate at system boundaries.
- Store secrets in environment variables, never in code.
- Escape output appropriately in Blade templates.

---

## 8. UI Rules

- **Index pages:** Card or grid layout by default. Tables only when the data is naturally tabular (invoices, logs, transactions).
- **All pages:** Mobile-first, Bootstrap responsive grid. No desktop-only layouts.
- **Forms:** Bootstrap form components. Blade components for reusable fields.
- **Confirmations:** Alpine.js modals for destructive actions — never native `confirm()`.
- **Universal Workspace (Entity 360):** Every core entity workspace must use the tabbed layout with: Overview, Timeline, Activity Log, Attachments, and domain-specific tabs.
- **State labels:** Use consistent status badge colors across all modules (configurable via Settings Engine).

---

## 9. AI Agent Behavior

- **Read documentation before writing code.** If context is missing, ask.
- **Minimize assumptions.** When architecture is ambiguous, surface the ambiguity rather than guessing.
- **Prefer consistency over creativity.** Match existing patterns before introducing new ones.
- **Never introduce undocumented business rules.** Reference the relevant workflow document.
- **Never create undocumented business events.** Reference `business-events.md`.
- **Never modify financial records directly.** Corrections use new corrective records.
- **Check scope before implementing.** Read `project-scope.md` when in doubt.
- **Use exact glossary terms.** Read `glossary.md` before naming any class, table, or route.
- **When a workflow state is unclear,** read the relevant workflow document before writing any code.
- **When generating migrations,** verify against `erd.md` before producing the file.
- **When a request is ambiguous,** recommend an architecture decision rather than making an assumption.
- **Prioritize consistency** with the existing architecture over speed of implementation.

---

## 10. Terminal Usage

**Prefer reading files directly** when you know the path. Use search only to locate unknown files or validate architecture consistency.

**Never execute the following without explicit developer permission:**

```bash
composer install / update / require
php artisan migrate
php artisan migrate:fresh
php artisan migrate:rollback
php artisan db:seed
npm install / run / build
git commit / push / reset / rebase / force
rm -rf  |  del  |  DROP TABLE  |  TRUNCATE
```

**Safe to run without asking:**
```bash
php artisan route:list
php artisan make:model / make:controller / make:migration / make:job / make:event
php artisan list
```

---

## 11. Documentation Maintenance

When implementation requires a structural change to the architecture:

- **Do not silently change behavior.**
- Recommend updating the appropriate documentation first.

| Document | Update when |
|---|---|
| `decisions.md` | New architecture decision is required |
| `business-events.md` | New or changed business event |
| `workflows/*.md` | Workflow state, transition, or business rule changes |
| `entities.md` | New entity, changed lifecycle, new relationship, or immutability change |
| `erd.md` | Any relationship, cardinality, or deletion behavior change |

Documentation must lead. Code must follow. Do not silently change behavior — propose the documentation update before proceeding with implementation.

---

## 12. Development Log

After completing every requested task, update `docs/changelog/development-log.md`.

**Rules:**
- The log is append-only. Never rewrite previous entries except to correct obvious formatting or spelling mistakes.
- Entries must remain in chronological order.
- Each entry must include: Date, Category, Summary, Files Added, Files Modified, Architecture Impact, and Notes (optional).

**Categories:** Architecture · Documentation · Database · Backend · Frontend · Security · Performance · Testing · DevOps · Refactoring · Bug Fix

**When to update:** After every completed task — migrations, model changes, service implementations, documentation updates, and configuration changes.

Do not skip this step. The Development Log is a permanent project record.

---

## 13. Code Generation Principles

Generated code must be:

- **Maintainable** — clear intent, no magic strings or unexplained conditions
- **Modular** — one class, one responsibility
- **Readable** — self-documenting over commented; use names that explain intent
- **Testable** — injectable dependencies; no static calls inside business logic
- **Production-ready** — no TODO stubs or placeholder logic in delivered output

**Avoid:**
- Unnecessary abstraction for one-off operations
- Business logic in views, controllers, or route closures
- Direct DB queries outside the service or repository layer
- Hardcoded values that belong in the Settings Engine
- Duplicating logic already provided by shared platform components

---

## 14. Naming Conventions

Use exact terms from `glossary.md` in all code artifacts.

| Artifact | Convention | Example |
|---|---|---|
| Model | PascalCase, singular | `Subscription`, `InvoiceItem` |
| Controller | Model + Controller | `SubscriptionController` |
| Service | Verb + Entity + Service | `ActivateSubscriptionService` |
| Event (business) | PascalCase, past tense | `SubscriptionActivated` |
| Listener | Descriptive + Listener | `SendActivationNotificationListener` |
| Job | Action + Entity + Job | `ProcessProvisioningRequestJob` |
| Notification | Entity + Event + Notification | `InvoicePublishedNotification` |
| Form Request | Action + Entity + Request | `StorePaymentRequest` |
| Policy | Entity + Policy | `TicketPolicy` |
| Migration | Timestamped + snake_case action | `2026_06_28_create_collection_tasks_table` |
| Route name | lowercase dot notation | `subscription.activate`, `invoice.publish` |
| Table | snake_case, plural | `collection_tasks`, `invoice_items` |
| FK column | singular entity + `_id` | `subscription_id`, `customer_id` |
| Polymorphic | base + `_type` / `_id` | `timeline_eventable_type`, `timeline_eventable_id` |

---

## 15. Quality Expectations

- **SOLID** — Single Responsibility and Dependency Inversion above all
- **Small methods** — one method, one responsibility, independently testable
- **Clear separation of concerns** — HTTP, Application, Domain, Data layers must not bleed into each other
- **No duplication** — reuse existing Services, Traits, and Shared Components before creating new ones
- **Eager loading** — always define Eloquent relationships; use `with()` to prevent N+1; never lazy-load in loops
- **Exception handling** — catch specific exceptions; log to ActivityLog; never swallow errors silently
- **Validation at boundaries** — FormRequest validates everything from HTTP; trust nothing from user input

---

## 16. Final Reminder

> **Architecture Version 1.0 is considered frozen.**

Implementation must evolve **from** the architecture. The architecture must **not** evolve from implementation unless an explicit Architecture Decision is recorded in `decisions.md`.

When in doubt: **read the docs, follow the workflow, use the shared components, and ask before inventing.**

> Architecture is the contract. Documentation is the source of truth. Implementation is expected to follow both.
