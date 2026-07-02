# ISP Billing, Monitoring & Operations Platform

**Version:** 1.0

**Status:** Living Document

---

## Purpose

This document defines the architectural decisions governing the ISP Billing, Monitoring, CRM, and Operations Platform.

It serves as the single source of truth for business architecture and system design.

All implementation should follow the decisions documented here unless a newer architectural decision explicitly supersedes them.

As a living document, it is continuously refined through explicit decision updates, with superseding decisions taking precedence over older guidance.

---

## Architecture Principles

These principles guide platform-level architectural choices across all domains. They complement, but do not replace, the detailed architecture decisions defined in this document.

## 1. Simplicity over Complexity
The platform should prioritize operational simplicity. When multiple valid solutions exist, prefer the simpler design unless additional complexity delivers clear and significant business value.

## 2. Business Rules First
Business requirements are authoritative and must lead architectural choices. Technical structure exists to express and enforce business intent, not to redefine it.

This principle includes API-first thinking: interfaces and contracts should preserve business behavior consistently across channels.

## 3. Architecture Decisions are the Source of Truth
Architecture decisions documented in this repository are the authoritative source for architecture and implementation alignment.

As this is a living document, the latest explicitly superseding decision becomes the active direction.

## 4. AI-Assisted Development
AI coding assistants shall treat the project documentation as the authoritative knowledge base for the entire platform.

Before generating code, database changes, APIs, workflows, tests, documentation, or new modules, AI assistants should review the existing project documentation and align all generated artifacts with the documented architecture.

When implementation conflicts with documented architecture, AI assistants should propose documentation updates or implementation changes instead of introducing inconsistent behavior.

AI-generated implementations should extend the existing architecture rather than creating parallel, duplicated, or conflicting solutions.

The following documents are considered the project's primary knowledge base:
- docs/architecture/decisions.md
- docs/architecture/glossary.md
- docs/database/entities.md
- docs/database/erd.md
- docs/workflows/*.md

## 5. Documentation Hierarchy
When conflicts occur, documentation authority follows this order: Architecture Decisions -> Workflows -> Database Design -> Source Code.

This hierarchy keeps strategic intent stable and prevents lower-level artifacts from silently overriding architectural direction.

## 6. Mobile-First Experience
Operational interfaces should be designed for mobile devices first, then enhanced for desktop usage.

Mobile-first design reflects the primary operating context and improves usability across customer and field workflows.

## 7. Reusable Components
Prefer reusable engines, metadata-driven components, and shared modules over duplicated implementations.

Reuse should favor clear boundaries and consistent contracts so capabilities can be composed without repeating domain behavior.

## 8. Entity 360 Workspace
Business entities should expose a unified workspace that consolidates related operational context in one place.

The Entity 360 model reduces fragmented navigation and supports faster support, troubleshooting, and decision-making.

## 9. Global Search as Primary Navigation
Operational users should primarily access business entities through the Global Search Engine rather than navigating module menus.

Search-first navigation accelerates cross-domain work by allowing users to start from identifiers and immediately reach the right context.

## 10. Prepared View Data
Views are responsible only for rendering data that has already been prepared.

Database access, business logic, and service orchestration are prohibited during view rendering to preserve separation of concerns and predictable performance.

## 11. Mandatory Eager Loading
Controllers and services must prepare all required data before rendering views.

Lazy loading during rendering should be avoided to reduce hidden dependencies and inconsistent runtime behavior.

## 12. Asynchronous External Integrations
External systems, including network infrastructure and communication providers, should be integrated asynchronously whenever possible.

Asynchronous integration improves resilience, isolates failures, and keeps core workflows responsive when external dependencies are slow or unavailable.

## 13. Configuration over Hardcoding
Business behavior should be configurable through the Settings Engine whenever appropriate.

Configuration-first design enables policy evolution without repeatedly changing underlying implementation.

## 14. Consistent Business Terminology
Business terminology must follow the project glossary to ensure consistent documentation and implementation.

Shared terminology reduces ambiguity across architecture, operations, and delivery teams.

## 15. Security by Default
Every new feature should apply least-privilege principles and respect role-based access control from the outset.

Security requirements are baseline architecture constraints, not optional post-implementation enhancements.

# Document Maintenance

This section defines how this document is maintained over the lifetime of the project.

## Automatic Updates
GitHub Copilot should automatically review this document whenever architecture-related documentation is created or modified.

Examples include:
- Architecture Decisions
- Workflow documents
- Database documentation
- Entity definitions
- Module specifications

When inconsistencies are detected, Copilot should:
- Propose updates.
- Merge duplicated decisions.
- Update cross references.
- Keep terminology consistent with the project glossary.
- Preserve business intent.
- Never silently remove architecture decisions.

## Manual Updates
Developers are responsible for updating this document whenever:
- A new architecture decision is made.
- A business rule changes.
- A new subsystem is introduced.
- A decision becomes obsolete.

Updates should extend the architecture rather than rewrite project history.

Superseded decisions should be explicitly marked or merged into the latest authoritative decision.

## Review Policy
This document should be reviewed whenever one of the following changes occurs:
- New business module
- New workflow
- Database redesign
- External system integration
- Security model changes
- Customer portal changes
- Monitoring architecture changes
- Notification architecture changes

## Documentation Synchronization
The following documents must remain consistent:
- docs/architecture/decisions.md
- docs/architecture/glossary.md
- docs/database/entities.md
- docs/database/erd.md
- docs/workflows/*.md

When conflicts occur, follow the Documentation Hierarchy defined in the Architecture Principles.

## Contents
- [Architecture Principles](#architecture-principles)
- [Document Maintenance](#document-maintenance)
- [Core Architecture](#core-architecture)
  - [Modular Monolith Architecture](#decision-modular-monolith-architecture)
  - [API First Boundary](#decision-api-first-boundary)
  - [Metadata Driven CRUD](#decision-metadata-driven-crud)
  - [Universal CRUD Views](#decision-universal-crud-views)
  - [Entity 360 Workspace Pattern](#decision-entity-360-workspace-pattern)
  - [Lifecycle State Machine](#decision-lifecycle-state-machine)
  - [State Transition Logging](#decision-state-transition-logging)
  - [Canonical Terminology](#decision-canonical-terminology)
  - [Migration Strategy Before First Release](#decision-migration-strategy-before-first-release)
- [Identity & Access](#identity--access)
  - [Role Based Access Control](#decision-role-based-access-control)
  - [Authorization Requires Active Role and Permission Lifecycle](#decision-authorization-requires-active-role-and-permission-lifecycle)
  - [Area Based Visibility](#decision-area-based-visibility)
  - [User Impersonation](#decision-user-impersonation)
- [Application Layer](#application-layer)
  - [Service-First Application Layer](#decision-service-first-application-layer)
- [Customer Management](#customer-management)
  - [Customer and Subscription Boundaries](#decision-customer-and-subscription-boundaries)
  - [One Active Primary Subscription](#decision-one-active-primary-subscription)
  - [Cluster and Service Area Model](#decision-cluster-and-service-area-model)
  - [Geographic Service Coverage](#decision-geographic-service-coverage)
  - [Customer Lifecycle Canonical States](#decision-customer-lifecycle-canonical-states)
  - [Customer Type Classification for v1.0](#decision-customer-type-classification-for-v10)
  - [Customer Account vs Subscription Suspension Semantics](#decision-customer-account-vs-subscription-suspension-semantics)
  - [Customer Number Format and Generation](#decision-customer-number-format-and-generation)
  - [Customer Contact Data Model](#decision-customer-contact-data-model)
  - [One Active Subscription Enforcement Mechanism](#decision-one-active-subscription-enforcement-mechanism)
  - [Customer Account Cascade on Status Change](#decision-customer-account-cascade-on-status-change)
  - [Subscription Lifecycle Canonical States](#decision-subscription-lifecycle-canonical-states)
  - [Subscription Type Classification for v1.0](#decision-subscription-type-classification-for-v10)
  - [Subscription Suspension Data Model](#decision-subscription-suspension-data-model)
- [Billing Engine](#billing-engine)
  - [Single Invoice per Subscription per Billing Period](#decision-single-invoice-per-subscription-per-billing-period)
  - [Invoice Level Tax and Discount](#decision-invoice-level-tax-and-discount)
  - [On Demand Invoice PDF](#decision-on-demand-invoice-pdf)
  - [Immutable Financial Snapshot](#decision-immutable-financial-snapshot)
  - [Financial Records Are Logically Immutable](#decision-financial-records-are-logically-immutable)
  - [Overdue and Auto Suspension Policy](#decision-overdue-and-auto-suspension-policy)
  - [Suspension and Reactivation States](#decision-suspension-and-reactivation-states)
  - [Billing as Source of Truth for Service Restriction](#decision-billing-as-source-of-truth-for-service-restriction)
  - [Time and Currency Standards](#decision-time-and-currency-standards)
  - [Invoice Lifecycle Canonical States](#decision-invoice-lifecycle-canonical-states)
  - [Invoice Number Format and Generation](#decision-invoice-number-format-and-generation)
  - [Invoice Immutability Enforcement](#decision-invoice-immutability-enforcement)
  - [Billing Period Data Model for v1.0](#decision-billing-period-data-model-for-v10)
- [Payments](#payments)
  - [Payment Allocation Model](#decision-payment-allocation-model)
  - [Overpayment and Credit Handling](#decision-overpayment-and-credit-handling)
- [Monitoring & Network](#monitoring--network)
  - [Logical and Physical Topology Separation](#decision-logical-and-physical-topology-separation)
  - [Network Endpoint Terminology](#decision-network-endpoint-terminology)
  - [Health Status Abstraction](#decision-health-status-abstraction)
  - [Geo Referenced Network Assets](#decision-geo-referenced-network-assets)
- [Customer Portal](#customer-portal)
  - [Self Service Portal](#decision-self-service-portal)
  - [Portal Requests Instead of Direct Mutation](#decision-portal-requests-instead-of-direct-mutation)
  - [Mobile First Unified Portal](#decision-mobile-first-unified-portal)
  - [Operational Card First Index Experience](#decision-operational-card-first-index-experience)
- [Notification Engine](#notification-engine)
  - [Event Driven Notification Engine](#decision-event-driven-notification-engine)
  - [Multi Channel Notification Support](#decision-multi-channel-notification-support)
  - [Asynchronous Notification Queue](#decision-asynchronous-notification-queue)
  - [Notification Preferences](#decision-notification-preferences)
  - [Template Based Notifications](#decision-template-based-notifications)
  - [Notification Audit Trail](#decision-notification-audit-trail)
  - [Notification Event Catalog](#decision-notification-event-catalog)
- [Attachments](#attachments)
  - [Generic Attachment System](#decision-generic-attachment-system)
  - [Attachment Categories](#decision-attachment-categories)
  - [Attachment Storage Strategy](#decision-attachment-storage-strategy)
  - [Attachment Access Control](#decision-attachment-access-control)
  - [Attachment Preview Support](#decision-attachment-preview-support)
  - [Attachment Lifecycle](#decision-attachment-lifecycle)
- [Activity & Timeline](#activity--timeline)
  - [Soft Delete Baseline Policy](#decision-soft-delete-baseline-policy)
  - [Audit Activity Logging](#decision-audit-activity-logging)
  - [Entity Timeline](#decision-entity-timeline)
  - [Timeline Severity Model](#decision-timeline-severity-model)
  - [Human Readable Timeline Events](#decision-human-readable-timeline-events)
- [Settings Engine](#settings-engine)
  - [Centralized Settings Engine](#decision-centralized-settings-engine)
  - [Settings Categories](#decision-settings-categories)
  - [Typed Settings](#decision-typed-settings)
  - [Hierarchical Settings](#decision-hierarchical-settings)
  - [Secret Setting Protection](#decision-secret-setting-protection)
  - [Settings Cache](#decision-settings-cache)
  - [Settings Change Audit](#decision-settings-change-audit)
  - [Registered Settings Only](#decision-registered-settings-only)
  - [Feature Flags](#decision-feature-flags)
- [Search Engine](#search-engine)
  - [Global Search Engine](#decision-global-search-engine)
  - [Search Result Ranking](#decision-search-result-ranking)
  - [Search Indexing](#decision-search-indexing)
  - [Search History](#decision-search-history)
  - [Universal QR Search](#decision-universal-qr-search)
- [Provisioning](#provisioning)
  - [Provisioning Queue Boundary](#decision-provisioning-queue-boundary)
  - [Provisioning Retry and Outcome Tracking](#decision-provisioning-retry-and-outcome-tracking)
- [Workflows](#workflows)
  - [Workflow Classification](#decision-workflow-classification)
  - [Customer and Employee Workflow Separation](#decision-customer-and-employee-workflow-separation)
- [Future Considerations](#future-considerations)
  - [Conflict Resolution and Superseded Decisions](#decision-conflict-resolution-and-superseded-decisions)
  - [Native Mobile Clients](#decision-native-mobile-clients)
  - [Advanced GIS and Topology](#decision-advanced-gis-and-topology)

# Core Architecture

### Decision: Modular Monolith Architecture

#### Decision
The platform is implemented as a modular monolith with clear domain boundaries.

#### Reason
Current delivery needs prioritize speed, cohesion, and reduced operational overhead.

#### Impact
- Lower platform complexity in early and mid stages.
- Clear path for future module extraction if required.

### Decision: API First Boundary

#### Decision
All business capabilities are exposed through consistent service and API boundaries.

#### Reason
Multiple interfaces, including customer and employee experiences, must consume shared domain behavior.

#### Impact
- Better separation between domain and presentation layers.
- Stronger integration readiness.

### Decision: Metadata Driven CRUD

#### Decision
Standard CRUD experiences are generated from metadata definitions whenever possible.

#### Reason
Domain expansion requires rapid and consistent module onboarding.

#### Impact
- Reduced implementation duplication.
- Consistent quality baseline across modules.

### Decision: Universal CRUD Views

#### Decision
Reusable CRUD views are the default for standard operations and are configured by metadata.

#### Reason
Universal CRUD Views directly depend on Metadata Driven CRUD for field, table, and validation definition.

#### Impact
- Faster delivery of standard admin capabilities.
- Strong consistency of operational UX.

### Decision: Entity 360 Workspace Pattern

#### Decision
Core entities use a 360 workspace pattern with tab registration from multiple domains.

#### Reason
Operational users need cross-domain context in one workspace without fragmented navigation.

#### Impact
- Faster troubleshooting and support.
- Extensible profile architecture without core page rewrites.

### Decision: Lifecycle State Machine

#### Decision
Lifecycle-managed entities use explicit states and controlled transitions.

#### Reason
State machines prevent invalid transitions and enable deterministic automation.

#### Impact
- Predictable process behavior.
- Easier policy enforcement and auditability.

### Decision: State Transition Logging

#### Decision
Every state transition must be logged with previous state, new state, actor, timestamp, and reason.

#### Reason
Lifecycle automation and compliance require complete traceability.

#### Impact
- Reliable forensic and operational reconstruction.
- Better incident analysis and accountability.

### Decision: Canonical Terminology

#### Decision
The architecture uses the following canonical terms:
- Customer: account owner buying service.
- Subscription: service contract under customer.
- Collector: employee role focused on collection workflows.
- Cluster: operational grouping, not network cluster.
- Timeline: business-event narrative.
- Activity Log: technical accountability and forensic log.
- ONU: official endpoint term in service and monitoring domains; ONT is retained as network-topology alias.

#### Reason
Terminology drift causes documentation conflicts and implementation ambiguity.

#### Impact
- Consistent language across decisions, data model, and workflows.
- Reduced interpretation errors.

### Decision: Migration Strategy Before First Release

#### Decision
Database migration strategy is governed by the project's release phase.

**Before the first production release:**

Migration files are part of the evolving architecture and may be replaced, renamed, consolidated, or regenerated as business rules are finalized.

- Existing migration files may be replaced or regenerated when the schema they represent has materially changed during pre-release development.
- Avoid accumulating ALTER migrations solely to preserve development history. Prefer rebuilding the migration so it reflects the latest approved architecture.
- A fresh installation must always produce the current architecture without requiring workarounds.
- Migration replacement is permitted only while no production environment has run the migration being replaced.
- When a migration is replaced, the original file must be deleted and a new file created with a new timestamp. The original must never be silently overwritten.

**After the first production release:**

Migration files become immutable historical records.

- Never modify or delete any migration file that has been run on a production environment.
- All database evolution after the first production release must use forward-only migrations (new ALTER TABLE, new CREATE TABLE files).
- Every deployed environment must always have a valid sequential upgrade path from its current state to the latest schema.
- Rollback migrations are recommended but not required for all changes. When provided, they must be verified.

**Release boundary definition:**

The first production release is the moment the application is deployed to a production environment with real customer data. Until that point, the pre-release policy applies.

#### Reason
During pre-release development, the architecture is still being finalized. Business rules, entity specifications, and schema designs are actively evolving. Accumulating ALTER migrations during this phase creates a fragmented migration history that:

- Requires running many sequential migrations on fresh installations
- Obscures the final intended schema behind layers of incremental changes
- Creates unnecessary cognitive overhead for new developers onboarding
- Makes CI/CD pipeline setup and database resets slower and more error-prone

A clean migration history — where each migration file represents a clear, final intent — is more valuable during this phase than preserving every intermediate schema experiment.

After the first production release, migration files become part of the deployment contract between the codebase and all running environments. Modifying or deleting them at that point would break upgrade paths for all deployed instances, which is categorically unacceptable.

#### Impact
- **Pre-release:** Developers may regenerate migrations when schema designs change. The migration history in source control always reflects the current approved architecture, not its evolution history.
- **Fresh installations:** Always produce the current approved schema in a single clean pass with no accumulated incremental drift.
- **CI/CD pipelines:** Database reset and test setup is simpler and faster during pre-release.
- **Onboarding:** New developers can read the migrations as a clear specification of the current schema, not as an archaeological record.
- **Post-release:** Migrations become immutable. All schema changes after production release use new forward-only ALTER migration files. The upgrade path is always valid and auditable.
- **Audit:** The transition from pre-release to post-release is defined by first production deployment. Teams must track this boundary and switch to forward-only migrations from that point.
- **Existing Sprint 0 migrations:** The customers, subscriptions, invoices, payments, olts, onus, and related Sprint 0 migrations are considered pre-release and may be replaced as the respective module architectures are finalized during Sprint 2+.

# Identity & Access

### Decision: Role Based Access Control

#### Decision
Authorization is governed by roles and permissions.

#### Reason
The platform serves multiple personas with different operational and security needs.

#### Impact
- Controlled access by business responsibility.
- Reduced unauthorized operations.

### Decision: Authorization Requires Active Role and Permission Lifecycle

#### Decision
Only Roles with status **Active** and Permissions with status **Active** may participate in authorization checks.

Roles with status Draft or Deprecated must never grant authorization, even if they are still assigned to a user.

Permissions with status Draft or Deprecated must never grant authorization, even if they are still assigned to a role or directly to a user.

`User::hasRole()` must only match Roles whose status is Active.

`User::hasPermission()` must only match Permissions through Active Roles, or through directly granted Active Permissions. Both the Role and the Permission must be Active.

Authentication must verify the user has at least one Active Role assigned before a session is granted.

#### Reason
Authorization behavior must be deterministic and lifecycle-aware.

Draft and Deprecated security objects must not accidentally grant access as they transition through their governance lifecycle.

Centralizing lifecycle validation in the domain layer eliminates duplicated status checks across Policies, Gates, Middleware, Controllers, and Services.

#### Impact
- `User::hasRole(string $slug)` must filter by `RoleStatus::Active`.
- `User::hasPermission(string $key)` must filter by `RoleStatus::Active` on the Role and `PermissionStatus::Active` on the Permission.
- Policies, Gates, Middleware, Controllers, and Services must rely on `User::hasRole()` and `User::hasPermission()` — they must never check `RoleStatus` or `PermissionStatus` directly before authorizing an operation.
- Authentication (login) must verify at least one Active Role is assigned before a session is granted.

### Decision: Area Based Visibility

#### Decision
Data visibility can be scoped by assigned service areas, with optional global exceptions.

#### Reason
Operations are territorially managed.

#### Impact
- Cleaner and safer operational context.
- Reduced cross-area errors.

### Decision: User Impersonation

#### Decision
Authorized users may impersonate target users for support and verification under strict audit controls.

#### Reason
Troubleshooting frequently requires reproducing target-user context.

#### Impact
- Faster support outcomes.
- Strong audit requirements for trust and compliance.

# Application Layer

### Decision: Service-First Application Layer

#### Decision
The application follows a strict service-first architecture where Controllers are orchestration-only components and Services own all business logic, transactions, events, cache, and model persistence.

**Controllers may ONLY:**
- Authorize operations via Policy calls (`$this->authorize()`)
- Validate input via FormRequest classes
- Invoke Service methods
- Return HTTP responses (views, redirects, JSON)

**Controllers must NEVER:**
- Query Eloquent models directly (except via Route Model Binding for entity lookup)
- Access Cache facade
- Dispatch Domain Events
- Open database transactions (`DB::transaction()`, `DB::beginTransaction()`)
- Execute business rules or domain logic

**Services own:**
- Business rules and domain logic
- Database transactions (all `DB::transaction()` calls)
- Domain Event dispatch
- Cache read/write/invalidation
- Model persistence (create, update, delete operations)
- Cross-entity coordination
- External API integration coordination

**Models remain persistence objects:**
- Define Eloquent relationships
- Define attribute casts and accessors
- Define scopes for reusable query constraints
- NO business logic
- NO transaction management
- NO event dispatch

**Policies contain authorization ONLY:**
- Authorization rules (`can`, `cannot` methods)
- NO business logic
- NO database writes
- NO side effects

#### Reason
Service-first architecture establishes clear separation of concerns across application layers. Controllers handling HTTP concerns remain thin and testable without HTTP context. Business logic centralized in Services becomes reusable from Controllers, CLI commands, Jobs, and Listeners without duplication. Transaction boundaries, event dispatch, and cache invalidation are managed consistently in one place. This architecture prevents business logic fragmentation across Controllers, Models, and Policies that makes systems difficult to maintain and test.

The pattern aligns with established Laravel service layer practices while enforcing stricter boundaries than default Laravel conventions. It prevents common anti-patterns: fat Controllers with business logic, fat Models with transaction management, and Policy classes that mutate data.

#### Impact
- **Controllers:** Thin orchestration layer. Average controller action: 3-10 lines (authorize, validate, call service, return response).
- **Services:** Own all business operations. One service class per business operation or closely related operations. Services are independently testable via dependency injection without HTTP layer.
- **Models:** Pure persistence layer. Define relationships and query scopes only. Business logic lives in Services.
- **Policies:** Pure authorization. Return boolean results. Never mutate data.
- **Testing:** Business logic tested via Service unit tests without HTTP requests. Controller tests focus on HTTP routing, authorization, and response formatting.
- **Reusability:** Service methods callable from Controllers, CLI commands (`artisan`), Queue Jobs, Event Listeners, and test suites without code duplication.
- **Consistency:** Transaction boundaries, event dispatch, cache invalidation, and external API coordination patterns are consistent across all business operations because Services own these concerns.
- **Migration:** Existing code violating these rules must be refactored during Sprint 1.5+ as modules are implemented. New code must follow this architecture immediately.

**Related Architecture Standards:**
- Transaction ownership documented in `docs/architecture/transactions.md`
- Event dispatch documented in `docs/architecture/events.md`
- Cache ownership documented in `docs/architecture/cache.md`
- Queue dispatch documented in `docs/architecture/queue.md`
- Exception handling documented in `docs/architecture/exceptions.md`

# Customer Management

### Decision: Customer and Subscription Boundaries

#### Decision
Customer and Subscription are distinct architectural concepts.

#### Reason
Customer is account identity, while Subscription is service lifecycle and billing anchor.

#### Impact
- Cleaner domain modeling.
- Better handling of historical and future service variations.

### Decision: One Active Primary Subscription

#### Decision
A customer may have only one active primary internet subscription at a time.

#### Reason
This matches the current commercial model and simplifies operational control.

#### Impact
- Simpler provisioning and billing decision logic.

### Decision: Cluster and Service Area Model

#### Decision
Customers are organized using operational clusters and service areas for assignment, reporting, and routing.

#### Reason
Field operations are territory-driven.

#### Impact
- Better workload distribution and reporting.
- Consistent territory governance.

### Decision: Geographic Service Coverage

#### Decision
Coverage views are derived from customer and network geo points with optional administrative polygons.

#### Reason
Planning and outage analysis need spatial context without mandatory full GIS complexity.

#### Impact
- Better planning decisions and expansion analysis.

### Decision: Customer Lifecycle Canonical States

#### Decision
The canonical Customer lifecycle states and their database column values are:

- `Prospect` → `prospect` — New customer record; no active Subscription yet. Initial/default state.
- `Active` → `active` — Customer has at least one active Subscription. Full portal access.
- `Suspended` → `suspended` — Account suspended by administrative action only. Not triggered by billing.
- `Terminated` → `terminated` — Account permanently closed. Terminal state; no reactivation permitted.

The term **Prospect** is the canonical name for the initial Customer state. The term **Lead** is retired and must not appear in code, migrations, enums, or documentation.

The state transition from Prospect → Active is triggered automatically by the system when the Customer's first Subscription is activated (`SubscriptionActivated` event). All other transitions are manual actions by authorized actors.

#### Reason
The pre-freeze architecture used inconsistent terminology ("Lead" in `entities.md`, "Prospect" in `subscription-lifecycle.md`). Inconsistent lifecycle state names cause enum drift, migration errors, and implementation ambiguity. Canonicalization on Prospect eliminates this ambiguity before the first line of Customer module code is written.

#### Impact
- `CustomerStatus` PHP enum cases: `Prospect`, `Active`, `Suspended`, `Terminated`.
- `customers.status` migration column default: `prospect`.
- `entities.md` Customer lifecycle updated to reflect canonical states.
- `glossary.md` updated to define Prospect as a canonical term.
- All architecture documentation must use Prospect, not Lead.

### Decision: Customer Type Classification for v1.0

#### Decision
In Version 1.0, all customers are treated as individuals by default. A `customer_type` column is included on the `customers` table as a passive data field with values `individual` (default) and `business`. No differential billing, contact, or tax behavior is applied based on customer type in v1.0.

Differential business-customer behavior (multi-contact model, tax ID handling, company billing address) is deferred to Version 1.1 following explicit business requirements.

#### Reason
`glossary.md` defines Customer as "a person or organization," confirming both individual and business customers exist. Including the `customer_type` field in v1.0 captures this data without requiring a retroactive ALTER migration in v1.1. Deferring differential behavior avoids speculative scope during initial module implementation.

#### Impact
- `customer_type` column exists on `customers` table.
- `CustomerType` enum: `Individual` (`individual`), `Business` (`business`). Default: `individual`.
- No differential business rules applied in v1.0 based on this field.
- v1.1 may introduce differential billing, contact model, or tax ID handling for business customers.

### Decision: Customer Account vs Subscription Suspension Semantics

#### Decision
Customer account suspension and Subscription suspension are orthogonal state concepts governed by separate lifecycles.

**Customer account suspension** (this decision) is an administrative action triggered only by an authorized administrator or Customer Service representative. It is used for fraud, policy violations, legal holds, and contractual breaches. It does not automatically suspend active Subscriptions.

**Subscription suspension** (governed by `subscription-lifecycle.md`) is triggered by billing overdue policy (7+ days overdue) or manual operator action. It restricts internet service access at the network level. It does not affect Customer account status.

A Customer may be `active` at the account level while all Subscriptions are in `suspended` state (billing-driven). These are independent status axes evaluated separately.

#### Reason
Merging account-level and service-level suspension semantics into a single status causes incorrect cascade behavior: billing overdue events should not lock customers out of their portal accounts or prevent support ticket submission. Keeping them separate enforces the correct business behavior.

#### Impact
- `CustomerService::suspend()` is an explicit administrative action requiring a reason.
- `customers.status` is never changed by billing overdue events.
- Billing overdue policy only modifies `subscriptions.status`.
- Suspension notification events are distinct: `CustomerSuspended` vs `SubscriptionSuspended`.

### Decision: Customer Number Format and Generation

#### Decision
Every Customer record is assigned a unique, human-readable external identifier at creation time:

**Format:** `CUST-{zero-padded 6-digit sequential number}`  
**Examples:** `CUST-000001`, `CUST-000002`, `CUST-001234`

The `customer_number` is:
- Generated by `CustomerService::create()` before database insert
- Stored as a unique string column on the `customers` table
- Immutable after creation — never changed for the lifetime of the record
- The primary external identifier used in QR codes, invoice headers, Collector visits, portal references, and Global Search

Generation uses a database lock to prevent race conditions during concurrent Customer creation.

#### Reason
Customer-facing workflows (Collector field visits, invoice headers, portal reference, QR lookup) require a stable, human-readable identifier. Database PKs (`id` columns) are internal and must not be exposed externally. A prefixed sequential format is human-readable, sortable, and unambiguous.

#### Impact
- `customers` migration includes `customer_number VARCHAR(20) UNIQUE NOT NULL`.
- `CustomerService::create()` generates the customer number before insert using `lockForUpdate()` pattern.
- Customer number is displayed prominently on Customer 360 workspace and invoice headers.
- Global Search indexes customer number as a searchable field.

### Decision: Customer Contact Data Model

#### Decision
In Version 1.0, Customer contact fields are stored directly on the `customers` table:

- `email` — primary email address
- `phone` — primary phone number (voice / SMS)
- `whatsapp_phone` — WhatsApp number (may differ from `phone`)
- `alt_phone` — secondary / alternative phone (optional)

A normalized `customer_contacts` table is deferred to v1.1 if multi-contact business customer requirements emerge.

A single address model is used: `address`, `latitude`, `longitude` on the `customers` table represent the service installation address. Separate billing/correspondence address is deferred to v1.1.

#### Reason
For v1.0 residential ISP operations, a single contact person and single service address per customer is the common case. Denormalized contact fields on the `customers` table satisfy all v1.0 notification, QR, Collector, and portal requirements without normalized table complexity.

#### Impact
- Notification Engine resolves Customer recipients from `customers.email`, `customers.phone`, `customers.whatsapp_phone` directly — not through the portal User account.
- This allows notifications to Prospects who have no portal User account.
- v1.1 may introduce `customer_contacts` table for business customer multi-contact model.

### Decision: One Active Subscription Enforcement Mechanism

#### Decision
The business rule "a Customer may have only one active primary internet Subscription" is enforced at the **application layer only** using pessimistic locking.

`ActivateSubscriptionService` must:
1. Open a `DB::transaction()`
2. Acquire `lockForUpdate()` on the Customer record
3. Query `$customer->subscriptions()->where('status', 'active')->exists()`
4. Throw `BusinessRuleException` if an active Subscription already exists
5. Proceed with activation only if no active Subscription found

MySQL / MariaDB do not support partial unique indexes (`UNIQUE WHERE status = 'active'`), making a database-level constraint infeasible without a trigger. Application-layer enforcement with pessimistic locking per `transactions.md` is the correct approach.

#### Reason
Partial unique index support is not available in MySQL 8.x or MariaDB 10.x. A composite unique index on `(customer_id, status)` would allow multiple non-active subscriptions per customer but only one active, which requires a different column design. The simpler and maintainable solution is application-layer enforcement with `lockForUpdate()` per the Transaction Standard.

#### Impact
- `ActivateSubscriptionService` implements `lockForUpdate()` check.
- No partial unique index on `subscriptions` table.
- Race condition risk is mitigated by pessimistic locking.
- Test coverage must include concurrent activation scenario.

### Decision: Customer Account Cascade on Status Change

#### Decision
Customer account status changes do **not** automatically cascade to child entities (Subscriptions, Tickets, ServiceRequests).

Specific rules:
- Customer Suspension → does NOT suspend active Subscriptions. Subscriptions continue to operate independently.
- Customer Termination → requires all Subscriptions to already be in `terminated` state. The `CustomerService::terminate()` method enforces this as a pre-condition, throwing `BusinessRuleException` if any non-terminated Subscription exists.
- Customer soft delete → requires no active Subscriptions, no open Tickets, no pending ServiceRequests, and no financial records (published Invoices, Payments). All must be resolved before deletion is permitted.

#### Reason
Automatic cascading suspension from Customer to Subscriptions would conflate the two independent suspension semantics. Termination requiring all Subscriptions to be terminated first ensures financial and operational closure happens in the correct sequence. Pre-condition enforcement is clearer and safer than silent cascade.

#### Impact
- `CustomerService::suspend()` only changes `customers.status`; does not touch `subscriptions`.
- `CustomerService::terminate()` validates all Subscriptions are terminated before proceeding.
- `CustomerService::delete()` validates financial and operational pre-conditions before soft-deleting.
- Business logic rules are owned by `CustomerService`, tested via unit tests.

# Billing Engine

### Decision: Subscription Lifecycle Canonical States

#### Decision
The canonical Subscription lifecycle states and their database column values are:

- `Pending` → `pending` — Subscription created; pre-activation phase covering survey, installation, and provisioning. Default/initial state.
- `Active` → `active` — Service is live. Billing runs. Monitoring active.
- `Suspended` → `suspended` — Service restricted due to billing overdue or manual operator action. Sub-type tracked in `suspension_type` column.
- `Reactivation Pending` → `reactivation_pending` — Payment confirmed; awaiting service restoration.
- `Terminated` → `terminated` — Permanently closed. Terminal state.

**Scope mapping:**

The pre-activation workflow states documented in `subscription-lifecycle.md` (Survey Scheduled, Survey Completed, Installation Scheduled, Installation In Progress, Installation Completed, Provisioning Pending) are tracked through child entity records (`Survey`, `Installation`, `ProvisioningRequest`). The Subscription record itself uses `pending` status throughout all pre-activation phases. The detailed pre-activation state is determined by querying child entities, not by the Subscription status column.

**`entities.md` historical inconsistencies resolved:**
- "Reactivated" → replaced by `reactivation_pending` (pending phase) + back to `active`
- "Cancelled" → removed; cancellations are represented as `terminated` with a documented reason

#### Reason
Pre-activation sub-phases (Survey, Installation, Provisioning) are each backed by their own entity records with their own lifecycle. Duplicating these states onto the Subscription status column creates redundancy, synchronization complexity, and inconsistency risk. A single `pending` status for the Subscription with child entities tracking detail is cleaner and maintainable.

#### Impact
- `SubscriptionStatus` PHP enum cases: `Pending`, `Active`, `Suspended`, `ReactivationPending`, `Terminated`.
- `subscriptions.status` migration column default: `pending`.
- `entities.md` Subscription lifecycle updated to match canonical states.
- `glossary.md` updated to define `SubscriptionStatus` as a canonical term.

### Decision: Subscription Type Classification for v1.0

#### Decision
In Version 1.0, all subscriptions default to `primary` type. A `subscription_type` column is included on the `subscriptions` table with values `primary` (default) and `addon`. No differential billing or provisioning behavior is applied to add-on subscriptions in v1.0.

The One Active Primary Subscription rule applies only to `primary` type subscriptions: a Customer may have only one active primary subscription at a time. Multiple `addon` subscriptions may coexist (future v1.1 capability).

#### Reason
The One Active Subscription decision references "primary" subscription explicitly. Including `subscription_type` in v1.0 enables the schema to support add-ons in v1.1 without an ALTER migration. All existing ISP operational logic applies to primary subscriptions.

#### Impact
- `subscription_type` column: `primary` (default), `addon`.
- `SubscriptionType` enum: `Primary` (`primary`), `Addon` (`addon`).
- One-active-subscription enforcement applies only to `primary` subscriptions.
- No differential billing/provisioning rules for `addon` in v1.0.

### Decision: Subscription Suspension Data Model

#### Decision
Subscription suspension is modeled with a single `suspended` status combined with a `suspension_type` column:

- `subscriptions.status = 'suspended'` for all suspension states.
- `subscriptions.suspension_type = 'overdue'` for billing-triggered suspension (auto-reactivatable on payment settlement).
- `subscriptions.suspension_type = 'manual'` for operator-initiated suspension (requires explicit operator override to reactivate).

The `suspension_type` column is `NULL` when the subscription is not suspended.

**Auto-reactivation rule:** Only `suspension_type = 'overdue'` subscriptions may be automatically reactivated when payment settlement confirms the overdue balance is cleared. `suspension_type = 'manual'` subscriptions require an explicit operator action regardless of payment state.

#### Reason
Having `suspended_overdue` and `suspended_manual` as separate status enum values would double the number of valid `status` transitions (active → suspended_overdue, active → suspended_manual, etc.). A single `suspended` status with a `suspension_type` column is simpler, reduces the state transition matrix, and clearly separates the sub-type concern from the primary lifecycle state.

#### Impact
- `subscription_type` column is `NULL` when not suspended; populated on `suspend()` call.
- `SubscriptionService::suspend()` accepts `type` parameter: `overdue` or `manual`.
- `SubscriptionService::reactivate()` validates `suspension_type` before allowing auto-reactivation.
- Architecture Decision "Suspension and Reactivation States" in `decisions.md` is superseded by this decision for the data model.

# Billing Engine

### Decision: Single Invoice per Subscription per Billing Period

#### Decision
Each subscription can produce exactly one invoice per billing period.

#### Reason
Prevents duplicate billing and preserves reconciliation clarity.

#### Impact
- Accurate aging and financial reporting.

### Decision: Invoice Level Tax and Discount

#### Decision
Tax and discount are applied at invoice level.

#### Reason
This model matches current billing complexity and improves operational simplicity.

#### Impact
- Easier billing computation and review.

### Decision: On Demand Invoice PDF

#### Decision
Invoice PDFs are generated on demand and not stored as permanent canonical artifacts.

#### Reason
Reduces stale document risk and storage overhead.

#### Impact
- Lower storage footprint.
- Strong dependence on immutable invoice data snapshot quality.

### Decision: Immutable Financial Snapshot

#### Decision
Invoice financial values are stored as immutable snapshots at generation time.

#### Reason
Historical financial correctness must be preserved after product and policy changes.

#### Impact
- Stable audit and reporting behavior.

### Decision: Financial Records Are Logically Immutable

#### Decision
Financial records are corrected by explicit financial workflows, not delete operations.

#### Reason
Accounting integrity requires full historical continuity.

#### Impact
- Stronger compliance posture.
- Clear correction workflow requirements.

### Decision: Overdue and Auto Suspension Policy

#### Decision
Suspension eligibility is based on overdue age.

The current authoritative threshold is: overdue for more than 7 days.

#### Reason
Overdue-age policy is predictable and customer-communicable, and latest project specification defines 7-day enforcement.

#### Impact
- Deterministic suspension timeline.
- Clear collection policy communication.

### Decision: Suspension and Reactivation States

#### Decision
Subscription lifecycle distinguishes at least:
- suspended_overdue
- suspended_manual

Only overdue-based suspension is automatically reactivated after confirmed settlement of overdue balance.

#### Reason
Manual operational restrictions must remain under explicit operator control.

#### Impact
- Prevents accidental auto-reactivation of manual operational holds.

### Decision: Billing as Source of Truth for Service Restriction

#### Decision
Billing state controls suspend or reactivate decisions; network telemetry alone cannot alter financial state.

#### Reason
Separates financial governance from infrastructure health fluctuations.

#### Impact
- Reduced coupling between NOC and billing control loops.
- Better policy determinism.

### Decision: Time and Currency Standards

#### Decision
Monetary values use IDR conventions, and time handling uses consistent timezone standards for due and overdue evaluation.

#### Reason
Billing correctness is sensitive to temporal and currency inconsistency.

#### Impact
- Reliable aging and reconciliation outputs.

### Decision: Invoice Lifecycle Canonical States

#### Decision
The canonical Invoice lifecycle states and their database column values are:

- `Draft` → `draft` — Invoice created; contains billing items; editable; not yet payable; not customer-visible. Default/initial state.
- `Published` → `published` — Frozen and payable; customer-visible; permanently immutable.
- `Partially Paid` → `partially_paid` — At least one payment allocation exists; outstanding balance remains.
- `Paid` → `paid` — Fully settled; balance is zero.
- `Overdue` → `overdue` — Past due date and grace period without full settlement.
- `Cancelled` → `cancelled` — Cancelled before publication; terminal state. Only draft invoices may be cancelled.

**Immutability boundary:** Any transition past `draft` makes the invoice permanently immutable. Only `paid_amount` and `balance_amount` may be updated after publication (exclusively by the Payment Workflow, never by direct user action).

**Allowed transitions:**
- `draft` → `published` (publish action)
- `draft` → `cancelled` (cancel action)
- `published` → `partially_paid` (payment allocation event)
- `published` → `paid` (payment allocation event, full settlement)
- `published` → `overdue` (billing policy evaluation)
- `partially_paid` → `paid` (payment allocation event, balance reaches zero)
- `partially_paid` → `overdue` (billing policy evaluation)
- `overdue` → `partially_paid` (payment allocation event)
- `overdue` → `paid` (payment allocation event)

#### Reason
Formalizing `InvoiceStatus` enum values before implementation prevents the `UserStatus::Disabled` class of enum-migration mismatch incidents. The status progression maps directly to billing-workflow.md lifecycle states.

#### Impact
- `InvoiceStatus` PHP enum cases: `Draft`, `Published`, `PartiallyPaid`, `Paid`, `Overdue`, `Cancelled`.
- `invoices.status` migration column default: `draft`.
- `InvoiceService` enforces state machine: throws `DomainException` for invalid transitions.
- `invoices` table has NO `deleted_at` column (Soft Delete: Never per entities.md).

### Decision: Invoice Number Format and Generation

#### Decision
Every Invoice is assigned a unique, human-readable external identifier at creation:

**Format:** `INV-{YYYYMM}-{zero-padded 6-digit sequential number}`  
**Examples:** `INV-202607-000001`, `INV-202607-000002`, `INV-202608-000001`

The sequence resets per calendar month. The `invoice_number` is:
- Generated by `InvoiceService::create()` before database insert
- Stored as a unique string column on the `invoices` table
- Immutable after creation
- Used in: payment references, collection workflows, customer portal, and PDF headers

Generation uses `lockForUpdate()` to prevent race conditions under concurrent invoice creation.

#### Reason
Invoices require a human-readable stable reference for financial communications, customer billing correspondence, and reconciliation. The monthly sequence format provides chronological sortability and communicates billing period context.

#### Impact
- `invoices` migration includes `invoice_number VARCHAR(25) UNIQUE NOT NULL`.
- `InvoiceService::create()` generates the invoice number using `lockForUpdate()` pattern.
- Invoice number is displayed on Invoice PDF headers, payment confirmation, and billing summaries.

### Decision: Invoice Immutability Enforcement

#### Decision
Invoice immutability is enforced at the **Service Layer** following the Financial Records Are Logically Immutable decision.

**What is immutable after publication:**
- All financial fields: `subtotal_amount`, `tax_amount`, `discount_amount`, `total_amount`, all invoice items
- Billing period: `period_start`, `period_end`, `issue_date`, `due_date`
- Customer and Subscription references

**What may change after publication (Payment Workflow only):**
- `paid_amount` — updated by `InvoiceService::recordPaymentAllocation()` called by Payment Workflow
- `balance_amount` — recomputed as `total_amount - paid_amount` on each allocation
- `status` — transitions driven by payment events and billing policy

**Enforcement rules:**
- `InvoiceService::update()` throws `DomainException` if `status != draft`
- `InvoiceService::cancel()` throws `DomainException` if `status != draft`
- `InvoiceService::addItem()` throws `DomainException` if `status != draft`
- `InvoiceService::removeItem()` throws `DomainException` if `status != draft`
- Invoices have **no soft delete** — the `invoices` table has no `deleted_at` column
- `InvoicePolicy::delete()` always returns `false`

#### Reason
Financial immutability is a core accounting integrity requirement. Preventing modifications at the service layer (not just database constraints) provides clear error messages and auditability. The absence of `deleted_at` prevents accidental soft-delete attempts.

#### Impact
- `Invoice` model does NOT use `SoftDeletes` trait.
- `invoices` table has NO `deleted_at` column.
- `InvoiceController::destroy()` returns 403 Forbidden with explanatory message.
- Corrections to published invoices require future void/reversal workflow (out of scope for v1.0).

### Decision: Billing Period Data Model for v1.0

#### Decision
For Version 1.0, billing period context is stored directly on the `invoices` table via `period_start` and `period_end` date columns. A separate `billing_periods` table is deferred to v1.1.

The ERD documents `BILLING_PERIOD ||--o{ INVOICE`, representing the future architecture. In v1.0, this relationship is denormalized: each Invoice carries its own period dates.

#### Reason
A `BillingPeriod` entity requires the automated billing cycle engine (periodic billing job, cycle scheduling, subscription eligibility evaluation) which is a v1.1 feature. Introducing the entity before the billing engine creates orphaned infrastructure. Storing dates on Invoice is sufficient for v1.0 manual invoice creation.

#### Impact
- `billing_period_id` FK is NOT on `invoices` in v1.0.
- `period_start` and `period_end` DATE columns on `invoices` carry period context.
- Automated billing engine (v1.1) will introduce `billing_periods` table and add `billing_period_id` FK to invoices.
- `erd.md` Billing section note added: BillingPeriod→Invoice relationship is v1.1.

# Payments

### Decision: Payment Allocation Model

#### Decision
One payment can allocate to multiple invoices via explicit allocation records.

#### Reason
Customers may settle multiple outstanding invoices in one transaction.

#### Impact
- Flexible collection and reconciliation workflows.

### Decision: Overpayment and Credit Handling

#### Decision
Overpayment is treated as customer credit for controlled downstream use.

#### Reason
Real-world payment behavior requires credit carry-forward handling.

#### Impact
- Cleaner settlement logic.
- Reduced manual adjustments.

# Monitoring & Network

### Decision: Logical and Physical Topology Separation

#### Decision
Logical and physical network topology are modeled separately.

#### Reason
Service control and inventory or field representation have different concerns.

#### Impact
- Better operational clarity.
- Cleaner topology-driven workflows.

### Decision: Network Endpoint Terminology

#### Decision
ONU is the canonical service endpoint term in platform decisions.

ONT remains a recognized topology and vendor alias and must map to ONU semantics where needed.

#### Reason
Existing documents use both ONT and ONU; canonicalization is required for consistency.

#### Impact
- Lower documentation and model ambiguity.
- Clearer cross-module references.

### Decision: Health Status Abstraction

#### Decision
Monitoring outputs are abstracted to operator-friendly health states.

#### Reason
Operators need actionable status, not raw diagnostic complexity.

#### Impact
- Faster triage and clearer monitoring UX.

### Decision: Geo Referenced Network Assets

#### Decision
Network and customer assets support independent geo coordinates.

#### Reason
Outage analysis, planning, and route optimization require asset-level spatial context.

#### Impact
- Better monitoring and planning intelligence.

# Customer Portal

### Decision: Self Service Portal

#### Decision
A customer self-service portal is provided with strict access to customer-owned data only.

Customer Portal experiences must integrate with Customer 360 context for support and operational continuity.

#### Reason
Self-service reduces support load while preserving controlled data boundaries.

#### Impact
- Improved customer experience.
- Better support handoff between portal and operations.

### Decision: Portal Requests Instead of Direct Mutation

#### Decision
Customer-initiated service changes are submitted as requests, not direct mutations.

#### Reason
Service-affecting changes require controlled operational validation.

#### Impact
- Reduced accidental or unsafe service changes.

### Decision: Mobile First Unified Portal

#### Decision
Customer and employee interfaces follow a mobile-first, unified portal strategy.

#### Reason
Field and customer interactions are predominantly mobile.

#### Impact
- Better usability in operational and customer contexts.

### Decision: Operational Card First Index Experience

#### Decision
Operational modules default to card-first index patterns where rapid lookup and contextual action dominate.

Table-first views remain valid for high-density analytical modules.

#### Reason
Most operational users navigate and act, rather than perform heavy tabular analysis.

#### Impact
- Faster field and support workflows.

# Notification Engine

### Decision: Event Driven Notification Engine

#### Decision
Notifications are emitted from domain events and processed by a dedicated notification engine.

Notification events must integrate with Timeline Events so user-facing lifecycle narratives remain consistent.

#### Reason
Centralized event-driven processing avoids module-level duplication and supports coherent user communication.

#### Impact
- Consistent cross-channel communication behavior.
- Better alignment between notifications and timeline narratives.

### Decision: Multi Channel Notification Support

#### Decision
Notification delivery supports multiple channels with configurable enablement.

#### Reason
Different users and scenarios require different channel strategies.

#### Impact
- Broader and more resilient communication coverage.

### Decision: Asynchronous Notification Queue

#### Decision
Notification delivery is asynchronous and must not block primary business transactions.

#### Reason
External delivery providers can be slow or unavailable.

#### Impact
- Better transactional responsiveness and reliability.

### Decision: Notification Preferences

#### Decision
Users can configure notification preferences by event type and channel.

#### Reason
Preference controls reduce fatigue and improve delivery relevance.

#### Impact
- Better engagement and delivery quality.

### Decision: Template Based Notifications

#### Decision
Notification content is generated from centrally managed templates with variable substitution.

#### Reason
Template governance improves consistency and update speed.

#### Impact
- Lower communication drift.
- Faster message maintenance.

### Decision: Notification Audit Trail

#### Decision
All notification attempts and outcomes are logged.

#### Reason
Delivery observability is required for support and compliance.

#### Impact
- Better diagnostics and accountability.

### Decision: Notification Event Catalog

#### Decision
Allowed notification events are registered in a controlled event catalog.

#### Reason
Prevents ad-hoc and duplicate event behavior.

#### Impact
- Predictable and governable notification behavior.

# Attachments

### Decision: Generic Attachment System

#### Decision
A shared attachment system is used across business modules.

#### Reason
Attachment needs are cross-cutting and should not be reimplemented per module.

#### Impact
- Lower duplication and more consistent file governance.

### Decision: Attachment Categories

#### Decision
Attachments are classified by controlled categories with extensibility.

#### Reason
Categorization improves retrieval and policy control.

#### Impact
- Better document governance and searchability.

### Decision: Attachment Storage Strategy

#### Decision
Attachment binary content is managed in file storage, while metadata is managed in the application data model.

#### Reason
Improves performance and backup efficiency.

#### Impact
- Smaller transactional data footprint.

### Decision: Attachment Access Control

#### Decision
Attachment authorization inherits from the owning entity.

#### Reason
Ownership inheritance avoids fragmented access models.

#### Impact
- Consistent and simpler authorization behavior.

### Decision: Attachment Preview Support

#### Decision
Supported file types provide in-app preview, with download fallback for unsupported types.

#### Reason
Operational speed benefits from immediate content preview.

#### Impact
- Faster validation and review workflows.

### Decision: Attachment Lifecycle

#### Decision
Attachments follow lifecycle controls including soft deletion, delayed purge, and optional image optimization.

#### Reason
Balancing recoverability, storage control, and performance requires lifecycle governance.

#### Impact
- Better storage hygiene and operational recovery.

# Activity & Timeline

### Decision: Soft Delete Baseline Policy

#### Decision
Soft delete is the default record policy for business data.

Financial data follows immutable correction workflows instead of standard delete paths.

#### Reason
The platform must preserve recoverability while protecting financial integrity.

#### Impact
- Fewer accidental data-loss incidents.
- Clear separation of operational and financial deletion semantics.

### Decision: Audit Activity Logging

#### Decision
A centralized activity log captures security and accountability actions.

#### Reason
Operational and compliance visibility depends on complete traceability.

#### Impact
- Improved accountability and incident investigations.

### Decision: Entity Timeline

#### Decision
Timeline is a business-readable chronological event narrative per entity.

#### Reason
Operators need contextual event storytelling separate from forensic logging.

#### Impact
- Better support and troubleshooting context.

### Decision: Timeline Severity Model

#### Decision
Timeline events are severity-classified.

#### Reason
Severity helps prioritize operational response.

#### Impact
- Faster triage.

### Decision: Human Readable Timeline Events

#### Decision
Timeline text uses human-readable business language, not internal system jargon.

#### Reason
Timelines are an operational communication surface.

#### Impact
- Lower interpretation errors.

# Settings Engine

### Decision: Centralized Settings Engine

#### Decision
Configurable business rules are managed through a centralized settings engine.

#### Reason
Operational policy change should not require repeated release cycles.

#### Impact
- Faster policy adaptation.

### Decision: Settings Categories

#### Decision
Settings are grouped by controlled categories.

#### Reason
Category governance improves discoverability and administration.

#### Impact
- Cleaner configuration management.

### Decision: Typed Settings

#### Decision
Each setting declares explicit data type and validation semantics.

#### Reason
Type and validation constraints reduce runtime misconfiguration.

#### Impact
- Improved reliability of runtime behavior.

### Decision: Hierarchical Settings

#### Decision
Settings can be applied at global, area or cluster, and customer scopes.

#### Reason
Policy needs vary by geography and account context.

#### Impact
- Fine-grained operational flexibility.

### Decision: Secret Setting Protection

#### Decision
Sensitive settings are protected with encryption-at-rest controls.

#### Reason
Credential and token confidentiality is mandatory.

#### Impact
- Lower secrets exposure risk.

### Decision: Settings Cache

#### Decision
Frequently accessed settings are cached with controlled refresh behavior.

#### Reason
Runtime settings reads are frequent and should remain efficient.

#### Impact
- Better runtime performance.

### Decision: Settings Change Audit

#### Decision
Settings changes must be fully audited with actor and value-change context.

#### Reason
Configuration changes can materially alter platform behavior.

#### Impact
- Better governance and traceability.

### Decision: Registered Settings Only

#### Decision
Only registry-defined settings are permitted.

#### Reason
Registry controls prevent undocumented and unsafe configuration drift.

#### Impact
- Predictable and governable settings surface.

### Decision: Feature Flags

#### Decision
Feature flags are managed by the settings engine for runtime-controlled rollout.

#### Reason
Incremental release and rollback require safe operational toggles.

#### Impact
- Safer releases and experiments.

# Search Engine

### Decision: Global Search Engine

#### Decision
A centralized global search experience spans key operational entities.

Search results should deep-link into Entity 360 workspaces for context-rich continuation.

#### Reason
Operators frequently begin with partial identifiers and need fast cross-entity navigation.

#### Impact
- Faster case resolution and navigation.

### Decision: Search Result Ranking

#### Decision
Search results are ranked by relevance rules (exact, prefix, partial, fuzzy).

#### Reason
Ranking quality directly affects operator speed at scale.

#### Impact
- Higher precision and faster discovery.

### Decision: Search Indexing

#### Decision
Search uses dedicated indexing with asynchronous refresh.

#### Reason
Direct transactional querying is not sufficient at growth scale.

#### Impact
- Better search performance and scalability.

### Decision: Search History

#### Decision
User-scoped search history is available for repeated operational workflows.

#### Reason
Many support and collection tasks are repetitive.

#### Impact
- Faster repeated lookups.

### Decision: Universal QR Search

#### Decision
QR lookup resolves supported entities and routes to the corresponding context view.

Customer QR behavior resolves to customer billing summary and outstanding invoices.

#### Reason
QR accelerates field operations and collection workflows.

#### Impact
- Improved mobile and field efficiency.

# Provisioning

### Decision: Provisioning Queue Boundary

#### Decision
Billing and service-policy decisions submit provisioning intents through a queue boundary.

Direct synchronous device mutation from billing flow is not allowed.

#### Reason
Network operations can fail independently and require retriable orchestration.

#### Impact
- Better reliability and fault isolation.

### Decision: Provisioning Retry and Outcome Tracking

#### Decision
Provisioning operations must support retries and explicit success/failure outcomes.

#### Reason
Device and connectivity volatility are expected in network operations.

#### Impact
- Better operational resilience and traceability.

# Workflows

### Decision: Workflow Classification

#### Decision
Workflows are classified as:
- Financial workflows
- Service lifecycle workflows
- Support workflows
- Monitoring workflows

Each class must preserve domain boundaries and auditability.

#### Reason
Classification prevents cross-domain coupling and policy leakage.

#### Impact
- Clear ownership and governance per workflow category.

### Decision: Customer and Employee Workflow Separation

#### Decision
Customer-facing workflows and internal employee workflows share domain rules but expose different interaction surfaces.

#### Reason
User capability and risk boundaries differ by actor type.

#### Impact
- Safer self-service and clearer internal controls.

# Future Considerations

### Decision: Conflict Resolution and Superseded Decisions

#### Decision
Conflicts were resolved with latest authoritative rules and superseded entries were removed from this consolidated log.

Resolved conflicts:
- Auto-suspension timing: older monthly example rule superseded by authoritative overdue > 7 days rule.
- ONT/ONU naming: canonical platform term is ONU; ONT retained as topology alias.
- Duplicate decisions across `docs/decisions.md` and `docs/architecture/decisions.md`: merged into single authoritative entries.
- Implementation-specific guidance (for example framework-specific code access patterns) is superseded by implementation-agnostic architectural guidance.

#### Reason
Long-term architecture documentation requires single-source clarity and stable governance.

#### Impact
- Lower ambiguity for design and implementation teams.
- Cleaner ADR maintenance over time.

### Decision: Native Mobile Clients

#### Decision
Native mobile applications remain a future option and are not required for initial architecture.

#### Reason
Current unified mobile-first strategy provides sufficient functional coverage.

#### Impact
- Preserves future evolution path without current delivery overhead.

### Decision: Advanced GIS and Topology

#### Decision
Advanced GIS-grade routing and topology analytics are deferred and can be introduced incrementally.

#### Reason
Current operations are supported by point coordinates, area boundaries, and abstracted health models.

#### Impact
- Controls complexity while preserving extensibility.
