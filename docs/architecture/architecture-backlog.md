# Architecture Backlog

**Version:** 1.0  
**Status:** Living Document  
**Last Updated:** 2026-07-02  
**Owner:** Architecture Team

---

## Purpose

This document is the single source of truth for all unresolved architecture work. It captures decisions that must be made, documentation that must be created or updated, and technical investigations that must be completed before or during implementation.

The Architecture Backlog is **not** a dumping ground. Every item has a clear owner, priority, target version, and actionable recommendation.

---

## Backlog Rules

- Items are never deleted — they are moved to `Closed` when resolved.
- Items must not duplicate findings — related findings from review documents are merged into single actionable items.
- Every item references its source review finding(s).
- Priority is reviewed at the start of each Sprint.
- Items blocking current Sprint implementation are escalated to the Architecture Team immediately.

---

## Status Definitions

| Status | Meaning |
|---|---|
| **Todo** | Not yet started |
| **In Progress** | Work has begun |
| **Blocked** | Waiting on a dependency or decision |
| **Closed** | Completed and documentation updated |
| **Deferred** | Deliberately postponed to a future version |
| **Open** | Under investigation — decision not yet made |

---

## Priority Definitions

| Priority | Meaning |
|---|---|
| **Critical** | Blocks current Sprint implementation; cannot proceed without this |
| **High** | Must be resolved before the module implementation starts |
| **Medium** | Must be resolved before integration with dependent modules |
| **Low** | Should be resolved eventually; does not block immediate work |

---

## Contents

- [Epic A — Customer Architecture Finalization](#epic-a--customer-architecture-finalization)
- [Epic B — Customer Implementation Preparation](#epic-b--customer-implementation-preparation)
- [Epic C — Future Customer Improvements](#epic-c--future-customer-improvements)
- [Epic D — Technical Investigations](#epic-d--technical-investigations)
- [Epic E — Subscription Architecture Finalization](#epic-e--subscription-architecture-finalization)
- [Epic F — Payment Architecture Finalization](#epic-f--payment-architecture-finalization)
- [Closed Items](#closed-items)

---

# Epic A — Customer Architecture Finalization

**Scope:** Items that must be completed before Customer module implementation begins.  
**Target Version:** v1.0  
**Priority:** Critical / High

---

### ARCH-001

**Title:** Canonical Customer Lifecycle and Terminology

**Priority:** Critical  
**Status:** Closed  
**Target Version:** v1.0  
**Source Findings:** CL-01, CSE-01, CSE-02  
**Blocking:** Yes

**Resolution (2026-07-02):** Architecture Decision "Customer Lifecycle Canonical States" added to `decisions.md`. `Prospect` adopted as the canonical initial Customer state. `entities.md` Customer lifecycle updated from `Lead → ...` to `Prospect → ...`. `glossary.md` updated with `Prospect` and `Customer Number` terms. `CustomerStatus` PHP enum implemented (`app/Enums/CustomerStatus.php`) with cases Prospect/Active/Suspended/Terminated. `customer-workflow.md` created with full state transition matrix and actor matrix.

---

### ARCH-002

**Title:** Create Customer Workflow Document

**Priority:** Critical  
**Status:** Closed  
**Target Version:** v1.0  
**Source Findings:** CL-02, CL-03, CSE-02, CIM-01, CD-02, CIM-05  
**Blocking:** Yes

**Resolution (2026-07-02):** `docs/workflows/customer-workflow.md` created as the authoritative Customer lifecycle workflow document. Covers: all four lifecycle states with entry/exit conditions, state transition matrix, actor matrix, Customer vs Subscription suspension semantics, Customer ↔ Subscription cascade rules, Customer number generation, customer type policy, Customer 360 tab inventory with eager load chain, soft delete pre-conditions and cascade, portal account lifecycle, all six business events, notification recipient resolution, QR code generation timing, and references to all related architecture documents. `entities.md` Lifecycle Reference updated from `subscription-lifecycle.md` to `customer-workflow.md`.

---

### ARCH-003

**Title:** Customer Entity Column Specification

**Priority:** Critical  
**Status:** Closed  
**Target Version:** v1.0  
**Source Findings:** DMG-01, DMG-02, DMG-03, DMG-04, CR-01, CR-02, CR-03, CR-04  
**Blocking:** Yes

**Resolution (2026-07-02):** `entities.md` Customer entity fully respecified with complete column table (id, customer_number, name, customer_type, email, phone, whatsapp_phone, alt_phone, address, latitude, longitude, notes, cluster_id, service_area_id, user_id, status, created_at, updated_at, deleted_at). Relationships updated to include User (0..1), ActivityLog (polymorphic), ServiceRequest (1..N), QRCodeReference (0..1). Business Rules section expanded with eight explicit rules. Key Attributes rewritten as column-level specification. Lifecycle Reference corrected to customer-workflow.md. Produces/Consumes Events updated to include all six events including SubscriptionActivated as consumed.

---

### ARCH-004

**Title:** Customer Business Events — Missing Lifecycle Events

**Priority:** High  
**Status:** Closed  
**Target Version:** v1.0  
**Source Findings:** CBE-01  
**Blocking:** No

**Resolution (2026-07-02):** All four missing events added to `business-events.md`: `CustomerConverted`, `CustomerSuspended`, `CustomerReactivated`, `CustomerTerminated`. Full event definition format used. `entities.md` Customer Produces Events updated to include all six events.

---

### ARCH-005

**Title:** Customer Deletion Policy — Restrict Conditions and Cascade Behavior

**Priority:** High  
**Status:** Closed  
**Target Version:** v1.0  
**Source Findings:** CD-01, CD-02  
**Blocking:** No

**Resolution (2026-07-02):** Explicit Restrict conditions documented in `entities.md` Business Rules: Restrict when any Invoice with status published/overdue/paid exists, OR any Payment record exists, OR any active PaymentAllocation exists. Draft Invoices do not trigger Restrict. Pre-delete operational requirements documented: no active Subscription, no open Ticket, no pending ServiceRequest. Cascade behavior documented in `customer-workflow.md` Soft Delete Rules section. Architecture Decision "Customer Account Cascade on Status Change" added to `decisions.md`.

---

### ARCH-006

**Title:** Customer Aggregate Access Patterns and Dual FK Clarification

**Priority:** High  
**Status:** Closed  
**Target Version:** v1.0  
**Source Findings:** COB-01, CIM-02, CIM-03  
**Blocking:** No

**Resolution (2026-07-02):** Aggregate Access Patterns documented in `entities.md` Business Rules and `customer-workflow.md`. Direct FK access (`$customer->invoices()`, `$customer->payments()`) documented as intentional denormalization for Customer 360 performance. Subscription traversal pattern documented for line-item and provisioning context. Customer 360 eager load chain documented in `customer-workflow.md`.

---

# Epic B — Customer Implementation Preparation

**Scope:** Items that directly unblock or support Customer module implementation.  
**Target Version:** v1.0  
**Priority:** High / Medium

---

### ARCH-007

**Title:** Customer Suspension Semantics — Account vs Subscription Distinction

**Priority:** High  
**Status:** Closed  
**Target Version:** v1.0  
**Source Findings:** CL-03, CIM-01  
**Blocking:** No

**Resolution (2026-07-02):** Architecture Decision "Customer Account vs Subscription Suspension Semantics" added to `decisions.md`. `customer-workflow.md` documents the canonical distinction: Customer account suspension is administrative-only (never billing-driven), does not cascade to Subscriptions, requires documented reason. State transition matrix and business rules per state document this clearly.
- Should NOT be triggered by billing policy
- Distinct from Subscription suspension — Customer can be Active while a Subscription is suspended

Without this distinction, `CustomerService` and `SubscriptionService` may implement overlapping or conflicting suspension behavior.

**Recommended Action:**

1. Add a formal Architecture Decision to `decisions.md` in the Customer Management section:
   > "Customer Account Suspension is an administrative action distinct from Subscription Suspension. Customer Suspension prevents new Subscriptions, portal access, and ServiceRequests. It does NOT automatically suspend existing Subscriptions. Subscription Suspension (billing-driven or manual) is governed by the Subscription lifecycle independently."
2. Document Customer suspension entry conditions, exit conditions, and allowed transitions in `customer-workflow.md`
3. Document cascade behavior: "A Suspended Customer's active Subscriptions continue to operate until independently suspended."

---

### ARCH-008

**Title:** One Active Subscription Enforcement Mechanism

**Priority:** High  
**Status:** Open  
**Target Version:** v1.0  
**Source Findings:** COB-02  
**Blocking:** No (required before Subscription migration and ActivateSubscriptionService implementation)

**Description:**

The business rule "Customer may own only one active primary internet subscription at a time" must be enforced. Currently two approaches are possible:

**Option A — Application-only enforcement:**
- `ActivateSubscriptionService` checks for existing active subscription using `lockForUpdate()` before activation
- No database constraint
- Risk: relies entirely on application code; bypassed by direct DB operations

**Option B — Application + Partial Database Index (defense in depth):**
- Application check with `lockForUpdate()`
- Partial unique index: `UNIQUE (customer_id) WHERE status = 'active' AND type = 'primary'`
- Requires defining `subscription_type` column (`primary` / `addon`)
- Stronger guarantee; protects against direct DB access and race conditions

**Recommended Action:**

1. Investigate whether MySQL 8.x / MariaDB supports partial unique indexes (conditional: `WHERE status = 'active'`)
2. Make an Architecture Decision on enforcement approach before Subscription migration is generated
3. If `subscription_type` column is required for Option B, document it in `entities.md` Subscription Key Attributes
4. Regardless of approach: implement `lockForUpdate()` in `ActivateSubscriptionService` per `transactions.md`

---

### ARCH-009

**Title:** Existing `customers` Migration Audit

**Priority:** High  
**Status:** Todo  
**Target Version:** v1.0  
**Source Findings:** DMG-01, CSE-01, CL-01  
**Blocking:** No (required before any shared environment migration)

**Description:**

A `customers` migration was created during Sprint 0 scaffolding (`2026_06_25_000001_create_customers_table.php`) before the Customer module architecture review was conducted. This migration:

- Was created before CustomerStatus enum values were formally defined
- Was created before Customer Key Attributes were column-level specified
- Was created before the Lead/Prospect terminology was resolved
- Has not been reviewed against `entities.md`, `erd.md`, or any of the findings in the Customer module review

The migration should not be run on any shared environment until it has been audited and corrected.

**Recommended Action:**

1. Review `2026_06_25_000001_create_customers_table.php` against final Customer column specification (from ARCH-003) and CustomerStatus enum values (from ARCH-001)
2. Compare migration columns against resolved `entities.md` Key Attributes
3. Verify all FK constraints, indexes, and deletion behaviors match `erd.md`
4. If migration is significantly incorrect: create a replacement migration rather than modifying the original (once any shared environment has run it)
5. Mark the original Sprint 0 migration as "pre-review — verify before use" in its docblock

---

# Epic C — Future Customer Improvements

**Scope:** Architecture improvements that are valuable but should not block Sprint 2.1 or Sprint 2.x Customer module implementation.  
**Target Version:** v1.1  
**Status:** Deferred

---

### ARCH-010

**Title:** Customer Portal Account Lifecycle Documentation

**Priority:** Medium  
**Status:** Deferred  
**Target Version:** v1.1  
**Source Findings:** CIM-05  
**Blocking:** No (required before Customer Portal implementation in Sprint 3+)

**Description:**

The Customer ↔ User (portal account) lifecycle is undefined:
- When is the portal User account created? (On `CustomerRegistered`? Manually by admin? On first portal self-service request?)
- When Customer is Suspended, is the portal User account also suspended?
- When Customer is Terminated, is the portal User account deleted/disabled?

These questions do not need to be answered for Sprint 2.1 Customer CRUD and lifecycle management. They become relevant when Customer Portal module development begins.

**Recommended Action:**

1. When Customer Portal sprint is planned, document the portal account creation lifecycle in `customer-workflow.md`
2. Define the portal User suspension/termination cascade behavior
3. Reference the Customer Portal decisions in `decisions.md` (`Self Service Portal`, `Portal Requests Instead of Direct Mutation`)

---

### ARCH-011

**Title:** Customer Notification Recipient Resolution

**Priority:** Medium  
**Status:** Deferred  
**Target Version:** v1.1  
**Source Findings:** CR-04, CIM-06  
**Blocking:** No (required before Notification Engine integration)

**Description:**

The Notification Engine needs to resolve how to contact a Customer when sending event-driven notifications (e.g., `CustomerRegistered` welcome, `CustomerSuspended` alert). Two models are possible:

1. Direct contact fields on `customers` table (`email`, `phone`, `whatsapp_phone`)
2. Contact resolved through linked portal `User` account

Additionally, when a Customer has no portal User account (pre-activation), the Notification Engine must still be able to send email/SMS using contact fields on the Customer record.

This is addressed as part of ARCH-003 (Customer Entity Column Specification) for the contact fields decision in v1.0, but the Notification Engine routing logic itself is a v1.1 concern.

**Recommended Action:**

1. In v1.0: ensure contact fields (`email`, `phone`, `whatsapp_phone`) exist on the `customers` table per ARCH-003 decision
2. In v1.1: document recipient resolution logic in `notification-workflow.md`: "Notification Engine resolves Customer recipients from `customers.email` and `customers.phone` directly, not through the portal User account"

---

### ARCH-012

**Title:** Customer QR Code Reference Integration

**Priority:** Low  
**Status:** Deferred  
**Target Version:** v1.1  
**Source Findings:** CR-05  
**Blocking:** No (required before QR implementation)

**Description:**

`entities.md` states Customer QR lookup resolves to billing summary and outstanding invoices. However:
- The `QRCodeReference` entity relationship is missing from Customer entity relationships
- QR code generation timing is undocumented (on Customer creation? on first active Subscription?)
- QR payload content is undocumented

The Universal QR Search decision is documented in `decisions.md` but Customer-specific QR behavior is not.

**Recommended Action:**

1. When QR implementation sprint is planned:
   - Add `Customer 0..1 → 1 QRCodeReference` to `entities.md` Customer relationships
   - Document QR generation timing in `customer-workflow.md`
   - Define QR payload: customer_number, customer_id, redirect target (Customer 360 billing view)

---

### ARCH-013

**Title:** Customer 360 Tab Inventory and Eager Load Specification

**Priority:** Medium  
**Status:** Deferred  
**Target Version:** v1.1  
**Source Findings:** Risk R-02, Risk R-03  
**Blocking:** No (required before Customer 360 implementation)

**Description:**

The Customer 360 workspace will aggregate data from multiple modules (Subscriptions, Invoices, Payments, Tickets, Timeline, ActivityLog, Attachments). Without a defined tab inventory and eager-load specification:
- Implementation may start in inconsistent direction requiring refactoring (scope creep risk)
- N+1 query vulnerability is high given the number of related entities

**Recommended Action:**

1. Before Customer 360 views are implemented, define tab inventory in `customer-workflow.md`:
   - Overview tab: summary card, active subscription status, outstanding balance
   - Subscriptions tab: list with status badges and quick actions
   - Billing tab: invoice list with status, due date, amounts
   - Payments tab: payment history with allocation summary
   - Tickets tab: open and recent tickets
   - Timeline tab: chronological business events
   - Activity Log tab: detailed audit entries
   - Attachments tab: document management
2. Define the eager-load chain for each tab view to prevent N+1 queries

---

### ARCH-014

**Title:** CustomerUpdated Event Granularity

**Priority:** Low  
**Status:** Deferred  
**Target Version:** v1.1  
**Source Findings:** CBE-02  
**Blocking:** No

**Description:**

`CustomerUpdated` is a broad event covering any profile modification. Notification Engine consumers may need to distinguish between contact channel changes (re-routing required) and non-impactful changes (name update).

**Recommended Action:**

1. Evaluate in v1.1 whether to:
   - Add a `changedFields` context array to `CustomerUpdated` event class so consumers can filter
   - OR create a separate `CustomerContactUpdated` event for contact-channel-specific changes
2. Defer until Notification Engine integration sprint to understand actual consumer needs

---

### ARCH-015

**Title:** Inactive / Churned Customer State Evaluation

**Priority:** Low  
**Status:** Deferred  
**Target Version:** v1.1  
**Source Findings:** CL-04  
**Blocking:** No

**Description:**

The current lifecycle ends at `Terminated`. There may be value in a `Inactive` state for customers who have terminated all subscriptions but remain on record for win-back campaigns or historical analysis. This needs business input before an architecture decision can be made.

**Recommended Action:**

1. Raise as a business question during Sprint 2.x review: "Do we need a win-back or churned customer state?"
2. If yes: add `Inactive` between `Active` and `Terminated` in the lifecycle, update `customer-workflow.md` and `entities.md`
3. If no: document explicit exclusion in `entities.md`

---

# Epic D — Technical Investigations

**Scope:** Architecture questions requiring evaluation or research before a decision can be made.  
**Target Version:** Investigation  
**Status:** Open

---

### ARCH-016

**Title:** Investigate: Partial Unique Index Support for One Active Subscription Rule

**Priority:** High  
**Status:** Open  
**Target Version:** Investigation  
**Source Findings:** COB-02, Risk R-04  
**Blocking:** No (but informs Subscription migration design)

**Description:**

The one-active-subscription rule enforcement decision (ARCH-008) requires knowing whether MySQL 8.x / MariaDB 10.x supports partial unique indexes (i.e., `UNIQUE INDEX WHERE status = 'active'`).

MySQL 8.x does NOT support partial/conditional unique indexes natively (unlike PostgreSQL). MariaDB does not either.

**Investigation Questions:**
1. Confirm MySQL/MariaDB does NOT support `CREATE UNIQUE INDEX ... WHERE status = 'active'`
2. Evaluate alternatives: composite unique index on `(customer_id, status)` — works but allows one active AND one of each other status, which is too permissive
3. Evaluate: application-layer enforcement with `lockForUpdate()` + check as the primary and only mechanism
4. Evaluate: trigger-based enforcement at DB level (not recommended for Laravel — creates hidden behavior)
5. **Expected outcome:** Application-only enforcement with `lockForUpdate()` per `transactions.md`, with the risk documented and accepted

**Recommended Action:**

Confirm MySQL/MariaDB constraint, close with Architecture Decision: "Enforce one-active-subscription at application layer using `lockForUpdate()` in `ActivateSubscriptionService`. No database partial index. Race condition risk accepted and mitigated through pessimistic locking as documented in `transactions.md`."

---

### ARCH-017

**Title:** Investigate: Customer Type Scope for v1.0

**Priority:** High  
**Status:** Closed  
**Target Version:** Investigation  
**Source Findings:** DMG-02  
**Blocking:** Yes (blocks ARCH-003 completion)

**Resolution (2026-07-02):** Architecture Decision "Customer Type Classification for v1.0" added to `decisions.md`. Decision: all customers treated as individuals by default. `customer_type` column included as passive data field (`individual` default, `business` option). No differential billing, contact, or tax behavior in v1.0. Differential business-customer behavior deferred to v1.1. `CustomerType` enum required: `Individual` (`individual`), `Business` (`business`).

**Description:**

`glossary.md` defines Customer as "a person or organization." If both individual and business customers are supported, differentiation requires:
- `customer_type` column (`individual` / `business`)
- Potentially different identity fields (ID number vs. tax registration number)
- Potentially different contact model (individual: one contact; business: multiple contacts)
- Potentially different billing rules (IDR tax treatment may differ)

The investigation must answer: **Is business customer differentiation in scope for v1.0?**

**Investigation Questions:**
1. Does the current ISP operate with both individual and business customers?
2. Are business customers billed differently (tax, invoicing)?
3. Are business contact models different (multiple contacts per business customer)?
4. Can v1.0 treat all customers as individuals with a `type` flag added as a passive field?

**Expected Outcomes:**
- **If Yes (business customers have different behavior):** Add `customer_type` enum, document differentiated business rules
- **If No (all treated the same for v1.0):** Add `type` column as passive data, document explicit scope exclusion for differentiated business behavior

---

### ARCH-018

**Title:** Investigate: Customer ↔ Subscription Activation Trigger

**Priority:** Medium  
**Status:** Open  
**Target Version:** Investigation  
**Source Findings:** CL-02, CIM-01  
**Blocking:** No (but informs ARCH-002 content)

**Description:**

The Customer lifecycle transition from `Prospect → Active` has no defined trigger. Two models are possible:

**Model A: Customer activated when first Subscription is activated**
- `CustomerConverted` event fires alongside `SubscriptionActivated`
- Customer status is updated from Prospect to Active as a side effect of Subscription activation
- CustomerService listens to `SubscriptionActivated` and updates customer status

**Model B: Customer activated explicitly as a separate operation**
- Sales/Customer Service explicitly converts the Customer from Prospect to Active
- Subscription can only be created for an Active Customer
- Two separate operations: activate customer, then create subscription

**Investigation Questions:**
1. Does the ISP create a Customer record before or at the same time as the Subscription?
2. Can a Customer be "Active" without any Subscription? (i.e., Customer activated, no package selected yet)
3. What does "Prospect" mean at the Customer level vs. at the Subscription level?

**Expected Outcome:** Document the trigger mechanism in `customer-workflow.md` as part of ARCH-002.

---

# Epic E — Subscription Architecture Finalization

**Scope:** Items that must be completed before Subscription module implementation begins.
**Target Version:** v1.0
**Priority:** Critical / High
**Source:** Sprint 2.1 Subscription Architecture Pre-Check (2026-07-03)

---

### ARCH-019

**Title:** Subscription Lifecycle Canonical States and SubscriptionStatus Enum

**Priority:** Critical
**Status:** Closed
**Target Version:** v1.0
**Blocking:** Yes

**Resolution (2026-07-03):** Three Architecture Decisions added to `decisions.md`: Subscription Lifecycle Canonical States, Subscription Type Classification for v1.0, Subscription Suspension Data Model. Canonical states defined: `pending` (default), `active`, `suspended`, `reactivation_pending`, `terminated`. Pre-activation sub-phases (Survey, Installation, Provisioning) tracked via child entities while Subscription stays in `pending`. "Cancelled" removed; maps to `terminated`. "Reactivated" renamed to `reactivation_pending` state + back to `active`. `entities.md` updated with full lifecycle and column specification. `glossary.md` updated with Subscription Status and Subscription Suspension terms.

**Description:**

`entities.md` documents the Subscription lifecycle as:
> `Pending → Active → Suspended → Reactivated → Cancelled → Terminated`

This is inconsistent with `subscription-lifecycle.md`, which documents **eleven** states covering the full pre-activation journey:

| State in workflow | State in entities.md |
|---|---|
| Prospect | ❌ Missing (or maps to "Pending"?) |
| Survey Scheduled | ❌ Missing |
| Survey Completed | ❌ Missing |
| Installation Scheduled | ❌ Missing |
| Installation In Progress | ❌ Missing |
| Installation Completed | ❌ Missing |
| Provisioning Pending | ❌ Missing (or maps to "Pending"?) |
| Active | ✅ Active |
| Suspended | ✅ Suspended |
| Reactivation Pending | ⚠️ entities.md says "Reactivated" (wrong — this is a pending state) |
| Terminated | ✅ Terminated |
| Cancelled | ⚠️ "Cancelled" in entities.md but no "Cancelled" state in workflow |

**Additional gaps:**
- `SubscriptionStatus` PHP enum cases and database column values are not formally specified anywhere
- The Sprint 0 migration uses `default('pending')` — it is unknown which canonical state `pending` maps to
- `suspended_overdue` and `suspended_manual` are referenced in the Suspension and Reactivation States architecture decision but are not documented as SubscriptionStatus values

**Recommended Action:**

1. Resolve the lifecycle state list: determine whether all pre-activation workflow states (Survey, Installation, Provisioning) map to SubscriptionStatus enum values, or whether they are tracked separately (Survey entity, Installation entity, ProvisioningRequest entity) with a single `Pending` status umbrella for the Subscription
2. Decide whether `suspended_overdue` and `suspended_manual` are separate enum cases or a single `Suspended` case with a `suspension_type` column
3. Define complete `SubscriptionStatus` PHP enum cases and database column values
4. Update `entities.md` Subscription lifecycle to match the resolved canonical state list
5. Record formal Architecture Decision in `decisions.md` under Customer Management
6. Add `Prospect` / `Pending` / pre-activation states clarification to `glossary.md`

---

### ARCH-020

**Title:** Subscription Entity Column Specification

**Priority:** Critical
**Status:** Closed
**Target Version:** v1.0
**Blocking:** Yes

**Resolution (2026-07-03):** Full column specification added to `entities.md` Subscription entity: id, customer_id, package_id, onu_id, status, subscription_type, suspension_type, suspension_reason, suspended_at, activated_at, reactivation_requested_at, terminated_at, terminated_reason, billing_day, notes, timestamps. Business Rules section expanded to 8 explicit rules. Relationships updated to include ActivityLog, Attachment, SuspensionCase, ServiceRequest.

**Description:**

`entities.md` describes Subscription Key Attributes as:
> "Service status, plan reference, activation context, suspension state, lifecycle history."

This is conceptual prose, not a column specification. The Subscription migration cannot be generated without formally specifying the column list.

The Sprint 0 migration has columns (`subscription_code`, `created_by`, `updated_by`, `deleted_by`, `billing_anchor_day`) whose alignment with the final architecture is unverified.

**Expected columns (to be confirmed):**

| Column | Notes |
|---|---|
| `id` | PK |
| `customer_id` | FK → customers |
| `package_id` | FK → packages |
| `onu_id` | FK → onus, nullable |
| `status` | SubscriptionStatus enum |
| `subscription_type` | `primary` / `addon` — see ARCH-021 |
| `activated_at` | Nullable timestamp |
| `suspended_at` | Nullable timestamp |
| `suspension_type` | `overdue` / `manual` — nullable |
| `suspension_reason` | Nullable string |
| `terminated_at` | Nullable timestamp |
| `terminated_reason` | Nullable string |
| `billing_day` | Billing anchor day (1–28) |
| `notes` | Internal notes |
| `created_at`, `updated_at`, `deleted_at` | Standard |

**Recommended Action:**

1. Resolve ARCH-019 first to determine the full status column values
2. Formally specify the complete column list in `entities.md`
3. Confirm or replace the Sprint 0 subscriptions migration

---

### ARCH-021

**Title:** Subscription Type Column — Primary vs Addon

**Priority:** High
**Status:** Closed
**Target Version:** v1.0
**Blocking:** No

**Resolution (2026-07-03):** Architecture Decision "Subscription Type Classification for v1.0" added to `decisions.md`. `subscription_type` column defined: `primary` (default), `addon`. One-active-subscription rule applies to primary subscriptions only. No differential billing/provisioning for addon in v1.0. `SubscriptionType` enum required. (required before Subscription migration)

**Description:**

The One Active Subscription Enforcement decision (decisions.md) states:
> "A customer may have only one active **primary** internet subscription at a time."

The word "primary" implies a `subscription_type` column distinguishing primary subscriptions from add-on services. However:
- No `subscription_type` column is defined in `entities.md`
- No `SubscriptionType` enum is defined
- The scope of "add-on subscriptions" in v1.0 is not formally specified

**Recommended Action:**

1. Make an Architecture Decision: are add-on subscriptions in scope for v1.0?
2. If yes: define `SubscriptionType` enum (`primary`, `addon`) and add to `entities.md` column spec
3. If no: explicitly document that all subscriptions in v1.0 are primary, and simplify the one-active-subscription rule to apply to all subscriptions

---

### ARCH-022

**Title:** Subscription Suspension Sub-Types Formal Specification

**Priority:** High
**Status:** Closed
**Target Version:** v1.0
**Blocking:** No

**Resolution (2026-07-03):** Architecture Decision "Subscription Suspension Data Model" added to `decisions.md`. Single `suspended` status + `suspension_type` column model adopted (`overdue`/`manual`). Auto-reactivation rule documented: only `overdue` type eligible for automatic reactivation on payment settlement. `manual` requires explicit operator action. Column nullability and lifecycle documented. (required before SubscriptionService suspension implementation)

**Description:**

The architecture decision "Suspension and Reactivation States" defines two suspension types:
- `suspended_overdue` — billing-driven, auto-reactivated on payment
- `suspended_manual` — operator-driven, requires explicit override

However:
- These are referenced in `decisions.md` but not formally mapped to database column values
- It is unclear whether these are separate `status` enum values (e.g., `suspended_overdue`, `suspended_manual`) or a single `suspended` status with a `suspension_type` column
- The auto-reactivation policy for `suspended_overdue` is mentioned but the exact trigger (InvoiceOverdue event? Payment confirmed event?) is not specified

**Recommended Action:**

1. Make an Architecture Decision on the data model: single `suspended` status + `suspension_type` column OR two separate status values (`suspended_overdue`, `suspended_manual`)
2. Document the auto-reactivation trigger for `suspended_overdue` (listens to `PaymentCompleted` event)
3. Update `entities.md` Subscription column spec to reflect the decision
4. Document in `subscription-lifecycle.md`

---

### ARCH-023

**Title:** Subscription Pre-Activation Business Events Not Cataloged

**Priority:** High
**Status:** Closed
**Target Version:** v1.0
**Blocking:** No

**Resolution (2026-07-03):** Two key missing subscription events added to `business-events.md`: `SubscriptionCreated` (on subscription record creation, pending state) and `SubscriptionReactivationPending` (on transition to reactivation_pending state). Pre-activation sub-phase events (SurveyScheduled, InstallationScheduled, etc.) deferred to their respective module implementations (Survey, Installation, Provisioning) as those modules own the event production. `entities.md` Subscription Produces Events updated to include all six events. (required before pre-activation workflow implementation)

**Description:**

`subscription-lifecycle.md` references numerous events for the pre-activation workflow phases, but `business-events.md` only catalogs four Subscription events (SubscriptionActivated, SubscriptionSuspended, SubscriptionReactivated, SubscriptionTerminated).

**Events referenced in the workflow but NOT in business-events.md:**

| Event | State | Notes |
|---|---|---|
| Survey Scheduled | Survey Scheduled | |
| Survey Rescheduled | Survey Scheduled | |
| Survey Completed | Survey Completed | |
| Survey Outcome Recorded | Survey Completed | |
| Installation Scheduled | Installation Scheduled | |
| Installation Rescheduled | Installation Scheduled | |
| Installation Started | Installation In Progress | |
| Installation In Progress Updated | Installation In Progress | |
| Installation Completed | Installation Completed | |
| Installation Closure Recorded | Installation Completed | |
| Provisioning Requested | Provisioning Pending | |
| Provisioning Pending | Provisioning Pending | |
| Reactivation Requested | Reactivation Pending | |
| Reactivation Pending | Reactivation Pending | |
| Service Activated | Active | Produced alongside SubscriptionActivated |
| Service Restricted | Suspended | Produced alongside SubscriptionSuspended |

**Recommended Action:**

1. Determine which pre-activation events need formal catalog entries (vs. which are implementation details)
2. At minimum, add catalog entries for: `SubscriptionCreated`, `SurveyScheduled`, `SurveyCompleted`, `InstallationScheduled`, `InstallationCompleted`, `ProvisioningRequested`
3. Update `entities.md` Subscription `Produces Events` list

---

### ARCH-024

**Title:** Subscription Entity Relationships — Missing Entries in entities.md

**Priority:** Medium
**Status:** Closed
**Target Version:** v1.0
**Blocking:** No

**Resolution (2026-07-03):** Added four missing relationships to `entities.md` Subscription: ActivityLog (polymorphic), Attachment (polymorphic), SuspensionCase (1..N), ServiceRequest (0..1..N). (resolve before migration)

**Description:**

`entities.md` Subscription relationships list is incomplete:

**Present:**
- Customer 1 → N Subscription ✓
- Package 1 → N Subscription ✓
- Subscription 1 → N Survey ✓
- Subscription 1 → N Installation ✓
- Subscription 1 → N ProvisioningRequest ✓
- Subscription 1 → N Invoice ✓
- Subscription 0..1 → 1 Active ONT/ONU assignment ✓
- Subscription 1 → N TimelineEvent ✓

**Missing:**
- Subscription 1 → N ActivityLog (Shared Platform Features says Yes, but not in relationships)
- Subscription 1 → N Attachment (Shared Platform Features says Yes, but not in relationships)
- Subscription 1 → N SuspensionCase (referenced in ERD billing section)
- Subscription 1 → N ServiceRequest (0..1 in ERD)

**Recommended Action:**

Update `entities.md` Subscription relationships to include all four missing entries.

---

### ARCH-025

**Title:** Existing Subscriptions Migration Audit and Replacement

**Priority:** High
**Status:** Closed
**Target Version:** v1.0
**Blocking:** No

**Resolution (2026-07-03):** Old stub migration `2026_07_02_000004_create_subscriptions_table.php` deleted. Full architecture-aligned replacement `2026_07_03_000001_create_subscriptions_table.php` created with all columns from the final specification including subscription_type, suspension_type, activated_at, suspended_at, reactivation_requested_at, terminated_at, billing_day, and FK constraints matching erd.md. (required before any shared environment migration)

**Description:**

The subscriptions migration (`2026_07_02_000004_create_subscriptions_table.php`) is a timestamp-corrected copy of the Sprint 0 scaffolding migration. It has not been architecture-reviewed and contains several known inconsistencies:

| Column | Sprint 0 | Likely Final Spec | Action |
|---|---|---|---|
| `subscription_code` | VARCHAR(50) UNIQUE | Not in entities.md — verify | May need replacement |
| `status` default | `'pending'` | Canonical value TBD (ARCH-019) | Replace |
| `suspension_reason` | Single string | May need `suspension_type` + `suspension_reason` | Replace per ARCH-022 |
| `terminated_reason` | String | Verify alignment | May keep |
| `billing_anchor_day` | TINYINT | Verify name (billing_day?) | May rename |
| `created_by`/`updated_by`/`deleted_by` | Present | Not in Customer final spec — verify for Subscription | May remove |
| `activated_at` | Missing | Likely needed for billing start | Add |
| `subscription_type` | Missing | Pending ARCH-021 decision | Add if in scope |

**Recommended Action:**

1. Resolve ARCH-019, ARCH-020, ARCH-021, ARCH-022 first to finalize the column specification
2. Delete `2026_07_02_000004_create_subscriptions_table.php` and create a fully architecture-aligned replacement
3. Per pre-release migration policy: replacement is permitted before first production release

---

# Epic F — Payment Architecture Finalization

**Scope:** Items that must be completed before Payment module implementation begins.
**Target Version:** v1.0
**Priority:** Critical / High
**Source:** Sprint 2.4 Payment Module Pre-Work (2026-07-03)

---

### ARCH-026

**Title:** Payment Lifecycle Canonical States and PaymentStatus Enum

**Priority:** Critical
**Status:** Closed
**Target Version:** v1.0
**Blocking:** Yes

**Resolution (2026-07-03):** Added Architecture Decision "Payment Lifecycle Canonical States" to `decisions.md`. Canonical states defined: `intent_created`, `waiting_payment`, `received`, `validated`, `recorded`, `partially_allocated`, `fully_allocated`, `completed`, `reversed`, `failed`. `entities.md` Payment lifecycle updated to match.

---

### ARCH-027

**Title:** Payment Entity Column Specification

**Priority:** Critical
**Status:** Closed
**Target Version:** v1.0
**Blocking:** Yes

**Resolution (2026-07-03):** `entities.md` Payment entity fully respecified with column table including payment_number, status enum, payment_date, amount, currency, method, channel_reference, recorded/completed/reversed timestamps, reversal/failure reasons, and no soft delete policy.

---

### ARCH-028

**Title:** PaymentAllocation Entity Column Specification and Immutability Model

**Priority:** High
**Status:** Closed
**Target Version:** v1.0
**Blocking:** No

**Resolution (2026-07-03):** `entities.md` PaymentAllocation entity updated with full column specification and canonical status model (`allocated`, `reversed`) using append-only correction semantics. Added Architecture Decision "Payment Record and Allocation Immutability" to `decisions.md`.

---

### ARCH-029

**Title:** Payment Business Events — Missing Failure and Reversal Events

**Priority:** High
**Status:** Closed
**Target Version:** v1.0
**Blocking:** No

**Resolution (2026-07-03):** Added `PaymentReversed` and `PaymentFailed` event contracts to `business-events.md`. Updated the event matrix to include both events and their consumers.

---

### ARCH-030

**Title:** Existing `payments` Migration Audit and Replacement

**Priority:** High
**Status:** Todo
**Target Version:** v1.0
**Blocking:** Yes (before Payment module implementation)

**Description:**

`database/migrations/2026_07_02_000007_create_payments_table.php` is a Sprint 0 stub migration with architecture mismatches (softDeletes, audit columns, non-canonical status defaults, and missing canonical lifecycle fields).

**Recommended Action:**

1. Replace stub with architecture-aligned payments migration using canonical Payment columns and no soft delete
2. Align status enum values to ARCH-026
3. Keep migration ordering compatible with customers/invoices/payment_allocations dependencies

---

### ARCH-031

**Title:** Existing `payment_allocations` Migration Audit and Replacement

**Priority:** High
**Status:** Todo
**Target Version:** v1.0
**Blocking:** Yes (before Payment module implementation)

**Description:**

`database/migrations/2026_07_02_000008_create_payment_allocations_table.php` is a Sprint 0 stub migration with architecture mismatches (softDeletes, audit columns, no allocation status lifecycle fields).

**Recommended Action:**

1. Replace stub with architecture-aligned payment_allocations migration using append-only correction model
2. Add canonical allocation status and reversal metadata columns
3. Preserve FK integrity and unique allocation constraints

---

### ARCH-032

**Title:** Collector Module CollectionTask and Employee Schema Finalization

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Description:**

The Collector workflow is documented, but the repository does not yet contain a finalized column-level schema for the `collection_tasks` aggregate or its `employees` ownership model. `entities.md` defines relationships and business rules, but the implementation boundary still needs a canonical migration contract before code generation can proceed without inventing fields.

**Recommended Action:**

1. Define the exact `collection_tasks` and `collection_task_invoices` column sets, lifecycle metadata, and foreign key strategy
2. Finalize the `employees` support table and assignment linkage used by Collector tasks
3. Confirm the permission key namespace for Collector actions before controller and policy generation

**Resolution (2026-07-03):** `docs/database/entities.md` now defines canonical column-level schemas for `Employee`, `CollectionTask`, and `CollectionTaskInvoice`, including lifecycle fields and FK strategy. `docs/architecture/decisions.md` now defines the `collector.*` permission namespace and collector ownership rules. `docs/architecture/glossary.md` now includes `CollectionTask` and `CollectionTaskInvoice` terms.

---

### ARCH-033

**Title:** Service Area and Cluster Schema Finalization

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Description:**

The repository contains only stub `clusters` and `service_areas` migrations and models. The implementation boundary still lacks canonical column-level schemas, lifecycle metadata, hierarchy rules, and merge fields required for safe module generation.

**Recommended Action:**

1. Define the exact `clusters` and `service_areas` column contracts including lifecycle fields and self-referencing hierarchy/merge linkage
2. Finalize the assignment pivot contract used for employee-to-service-area linkage
3. Clarify the aggregate root boundary between Cluster and ServiceArea

**Resolution (2026-07-03):** `docs/database/entities.md` now defines canonical column-level schemas for `Cluster` and `ServiceArea`, including `parent_id`, `merged_into_service_area_id`, lifecycle timestamps, and the `employee_service_area` pivot contract. `docs/architecture/decisions.md` now defines canonical lifecycle states and assignment rules.

---

### ARCH-034

**Title:** Service Area Workflow, Events, and Visibility Contract

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Description:**

Area and Assignment Management is in project scope, but the repository lacks an authoritative workflow, event contract, and visibility model for cluster/service area lifecycle transitions and employee area scoping.

**Recommended Action:**

1. Create a dedicated workflow document covering cluster and service area lifecycle transitions
2. Document the canonical business events emitted by the module
3. Formalize area-based visibility resolution through employee service area assignment

**Resolution (2026-07-03):** `docs/workflows/service-area-workflow.md` now defines the authoritative lifecycle and assignment rules for Cluster and ServiceArea. `docs/architecture/business-events.md` now defines cluster and service area lifecycle events. `docs/architecture/glossary.md`, `docs/database/erd.md`, and `docs/architecture/decisions.md` now document the visibility and relationship contract used by the module.

---

### ARCH-035

**Title:** Package Module Lifecycle, Workflow, and Event Contract Finalization

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Description:**

Service Package Management is in project scope, but the repository still lacks a finalized implementation boundary for package lifecycle states, workflow transitions, event contracts, and permission namespace. Existing package code remains stub-based and does not have an authoritative column-level schema contract in architecture documentation.

**Recommended Action:**

1. Define canonical package lifecycle states, transition rules, and permission namespace in architecture decisions
2. Add a dedicated package workflow document with transition guards and cross-module effects
3. Finalize package entity column contract and package lifecycle business events before code generation

**Resolution (2026-07-03):** `docs/workflows/package-workflow.md` now defines package lifecycle transitions and guards. `docs/architecture/decisions.md` now defines canonical package lifecycle states and `package.*` permission namespace. `docs/database/entities.md` now defines column-level package schema and lifecycle reference. `docs/architecture/business-events.md` now defines package lifecycle business events. `docs/architecture/glossary.md` now includes `Package Status`.

---

### ARCH-036

**Title:** OLT Module Lifecycle and Schema Finalization

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Description:**

OLT Management is in project scope, but the repository still lacks an authoritative implementation contract for the OLT asset lifecycle, permission namespace, workflow transitions, and event surface. The existing Sprint 0 OLT migration persists a monitoring-like `offline` default status that conflicts with the documented asset lifecycle.

**Recommended Action:**

1. Define canonical OLT lifecycle states distinct from monitoring health states
2. Create a dedicated OLT workflow document covering transition guards and provisioning assignment rules
3. Finalize the OLT column contract and lifecycle business events before module generation

**Resolution (2026-07-03):** `docs/workflows/olt-workflow.md` now defines the OLT asset lifecycle and transition guards. `docs/architecture/decisions.md` now defines canonical OLT lifecycle states and `olt.*` permission namespace. `docs/database/entities.md` now defines the column-level OLT schema and lifecycle reference. `docs/architecture/business-events.md` now defines OLT lifecycle business events. `docs/architecture/glossary.md` now includes `OLT Status`.

---

### ARCH-037

**Title:** FAT Module Topology, Schema, and Derived Reachability Contract Finalization

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Description:**

FAT Management is in project scope, but the repository lacks an authoritative implementation contract for FAT lifecycle states, parent ODF dependency schema, permission namespace, and the downstream ONU-derived reachability rule requested for operational status evaluation.

**Recommended Action:**

1. Define canonical FAT lifecycle states distinct from monitoring-derived reachability
2. Create a dedicated FAT workflow document covering parent ODF dependency and downstream ONU ping aggregation
3. Finalize ODF/FAT column contracts and the FAT-to-ONU distribution scope reference before module generation

**Resolution (2026-07-03):** `docs/workflows/fat-workflow.md` now defines FAT lifecycle transitions and downstream reachability rules. `docs/architecture/decisions.md` now defines canonical FAT lifecycle states and `fat.*` permission namespace. `docs/database/entities.md` now defines implementation-grade ODF/FAT schemas and the optional ONU `fat_id` mapping used for downstream reachability aggregation. `docs/database/erd.md` now includes the FAT-to-ONU scope reference. `docs/architecture/business-events.md` now defines FAT lifecycle business events. `docs/architecture/glossary.md` now includes `FAT Status`.

---

# Closed Items

### ARCH-032

**Title:** Collector Module CollectionTask and Employee Schema Finalization

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Resolution (2026-07-03):** `docs/database/entities.md` now defines canonical column-level schemas for `Employee`, `CollectionTask`, and `CollectionTaskInvoice`, including lifecycle fields and FK strategy. `docs/architecture/decisions.md` now defines the `collector.*` permission namespace and collector ownership rules. `docs/architecture/glossary.md` now includes `CollectionTask` and `CollectionTaskInvoice` terms.

---

### ARCH-033

**Title:** Service Area and Cluster Schema Finalization

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Resolution (2026-07-03):** `docs/database/entities.md` now defines canonical column-level schemas for `Cluster` and `ServiceArea`, including hierarchy and merge linkage plus the `employee_service_area` pivot contract. `docs/architecture/decisions.md` now defines the canonical lifecycle states and assignment rules required for implementation.

---

### ARCH-034

**Title:** Service Area Workflow, Events, and Visibility Contract

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Resolution (2026-07-03):** `docs/workflows/service-area-workflow.md`, `docs/architecture/business-events.md`, `docs/database/erd.md`, `docs/architecture/glossary.md`, and `docs/architecture/decisions.md` now define the module workflow, lifecycle events, area-based visibility, and relationship contract.

---

### ARCH-035

**Title:** Package Module Lifecycle, Workflow, and Event Contract Finalization

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Resolution (2026-07-03):** `docs/workflows/package-workflow.md`, `docs/architecture/decisions.md`, `docs/database/entities.md`, `docs/architecture/business-events.md`, and `docs/architecture/glossary.md` now define package lifecycle states, workflow transitions, permission namespace, event contracts, and column-level schema required for implementation.

---

### ARCH-036

**Title:** OLT Module Lifecycle and Schema Finalization

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Resolution (2026-07-03):** `docs/workflows/olt-workflow.md`, `docs/architecture/decisions.md`, `docs/database/entities.md`, `docs/architecture/business-events.md`, and `docs/architecture/glossary.md` now define canonical OLT asset lifecycle states, permission namespace, event contracts, and schema required for implementation.

---

### ARCH-037

**Title:** FAT Module Topology, Schema, and Derived Reachability Contract Finalization

**Priority:** High

**Status:** Closed

**Target Version:** v1.0

**Blocking:** No

**Resolution (2026-07-03):** `docs/workflows/fat-workflow.md`, `docs/architecture/decisions.md`, `docs/database/entities.md`, `docs/database/erd.md`, `docs/architecture/business-events.md`, and `docs/architecture/glossary.md` now define FAT lifecycle states, parent ODF support schema, downstream ONU scope mapping, reachability derivation rules, permission namespace, and event contracts required for implementation.

---

## Backlog Summary

**Last Updated:** 2026-07-03

### By Priority

| Priority | Count | Open Items | Closed Items |
|---|---|---|---|
| **Critical** | 7 | 0 | ARCH-001, ARCH-002, ARCH-003, ARCH-019, ARCH-020, ARCH-026, ARCH-027 |
| **High** | 20 | ARCH-008, ARCH-009, ARCH-030, ARCH-031 | ARCH-004, ARCH-005, ARCH-006, ARCH-007, ARCH-021, ARCH-022, ARCH-023, ARCH-025, ARCH-028, ARCH-029, ARCH-032, ARCH-033, ARCH-034, ARCH-035, ARCH-036, ARCH-037 |
| **Medium** | 4 | ARCH-010, ARCH-011, ARCH-013 | ARCH-024 |
| **Low** | 3 | ARCH-012, ARCH-014, ARCH-015 | — |
| **Investigation** | 3 | ARCH-016, ARCH-018 | ARCH-017 |

### By Status

| Status | Count | Items |
|---|---|---|
| **Closed** | 26 | ARCH-001–007, ARCH-017, ARCH-019–029, ARCH-032, ARCH-033, ARCH-034, ARCH-035, ARCH-036, ARCH-037 |
| **Todo** | 4 | ARCH-008, ARCH-009, ARCH-030, ARCH-031 |
| **Open** | 2 | ARCH-016, ARCH-018 |
| **Deferred** | 6 | ARCH-010, ARCH-011, ARCH-012, ARCH-013, ARCH-014, ARCH-015 |
| **In Progress** | 0 | — |

### Sprint Readiness

| Module | Status | Blocking Items |
|---|---|---|
| Customer | ✅ Implemented | None |
| Subscription | ✅ Architecture approved | None (ARCH-019–025 all closed) |
| Payment | ❌ Pre-work completed, implementation blocked | ARCH-030, ARCH-031 |
| Collector | ✅ Architecture approved | None |
| Service Area | ✅ Architecture approved | None |
| Package | ✅ Architecture approved | None |
| OLT | ✅ Architecture approved | None |
| FAT | ✅ Architecture approved | None |

---

**End of Document**
