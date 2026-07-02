# Sprint 2.1 Story 1 — Customer Module Architecture Review

**Review Date:** 2026-07-02  
**Reviewer:** Architecture Team (GitHub Copilot)  
**Status:** Completed — Findings and Recommendations Recorded  
**Sprint:** 2.1 — Customer Module  
**Review Type:** Pre-Implementation Architecture Analysis

---

## Purpose

This document records the findings of the pre-implementation architecture review for the Customer module. The review was conducted against Architecture Freeze v1.0, the Service-First Application Layer decision, and all relevant documentation established through Sprint 1.4.

The goal is to identify gaps, inconsistencies, and risks before any Customer module code is written, ensuring the implementation starts from a fully resolved architecture baseline.

**No source code was generated or modified during this review.**

---

## Documentation Reviewed

| Document | Version | Reviewed |
|---|---|---|
| `docs/architecture/decisions.md` | v1.0 | ✅ |
| `docs/architecture/glossary.md` | v1.0 | ✅ |
| `docs/architecture/business-events.md` | v1.0 | ✅ |
| `docs/architecture/events.md` | v1.0 | ✅ |
| `docs/database/entities.md` | v1.0 | ✅ |
| `docs/database/erd.md` | v1.0 | ✅ |
| `docs/project/project-scope.md` | v1.0 | ✅ |
| `docs/workflows/subscription-lifecycle.md` | v1.0 | ✅ |

---

## Review Framework

Each finding is classified by:

- **Severity:** Critical / High / Medium / Low
- **Type:** Gap / Inconsistency / Risk / Technical Debt
- **Blocking:** Whether implementation must be blocked until resolved
- **Owner:** Architecture Team (doc update) or Implementation Team (code concern)

---

## Section 1: Customer Lifecycle Analysis

### Documented Lifecycle

`entities.md` documents the Customer lifecycle as:

```
Lead → Active → Suspended → Terminated
```

### Subscription Workflow Lifecycle States

`subscription-lifecycle.md` begins with:

```
Prospect → Survey Scheduled → Survey Completed → Installation Scheduled →
Installation In Progress → Installation Completed → Provisioning Pending →
Active → Suspended → Reactivation Pending → Terminated
```

### Finding CL-01: Terminology Inconsistency — "Lead" vs "Prospect"

**Severity:** High  
**Type:** Inconsistency  
**Blocking:** Yes

`entities.md` names the Customer's initial lifecycle state **"Lead"**. `subscription-lifecycle.md` names the starting state **"Prospect"**. `glossary.md` does not define either "Lead" or "Prospect" as a canonical term for a Customer account status.

The subscription-lifecycle.md is the authoritative workflow document for the Customer's pre-activation phase. It consistently uses "Prospect" throughout.

**Current state:**

| Document | Term Used |
|---|---|
| `entities.md` (Customer lifecycle) | `Lead` |
| `subscription-lifecycle.md` | `Prospect` |
| `glossary.md` | Neither defined |
| `business-events.md` | `CustomerRegistered` (no state name) |

**Impact:** When the `CustomerStatus` enum is implemented, there will be no authoritative source for the correct value. Developers will choose arbitrarily.

**Recommendation:** Align on one canonical term. Given that `subscription-lifecycle.md` is more detailed and uses "Prospect" consistently, **"Prospect" should be adopted as the canonical Customer status value.** `entities.md` should be updated to `Prospect → Active → Suspended → Terminated`. `glossary.md` should define "Prospect" as a canonical term. An architecture decision or glossary update is required before implementation.

---

### Finding CL-02: Missing Customer Workflow Document

**Severity:** High  
**Type:** Gap  
**Blocking:** Yes

`entities.md` sets `Lifecycle Reference: docs/workflows/subscription-lifecycle.md` for the Customer entity. However, the subscription lifecycle document governs **Subscription** states — not Customer account states.

The Customer has its own lifecycle (`Prospect → Active → Suspended → Terminated`) that is distinct from the Subscription lifecycle. This Customer account lifecycle has **no authoritative workflow document**.

**What is missing:**
- When does a Customer transition from Prospect to Active?
- What triggers Customer account Suspension (vs. Subscription suspension)?
- What are the conditions for Customer Termination?
- What business rules govern each Customer state transition?
- What actors are responsible for Customer state transitions?

**Recommendation:** Create `docs/workflows/customer-workflow.md` documenting:
- Customer account lifecycle states and transitions
- Actor responsibilities per state
- Entry/exit conditions per state
- Business rules distinguishing Customer account status from Subscription status
- Events generated per state transition

Update `entities.md` to reference `customer-workflow.md` instead of `subscription-lifecycle.md`.

---

### Finding CL-03: Customer Suspension vs Subscription Suspension — Semantic Gap

**Severity:** High  
**Type:** Gap  
**Blocking:** Yes

Two distinct suspension concepts exist in the architecture but are not clearly separated:

**Subscription Suspension** (documented in `subscription-lifecycle.md`):
- Triggered by billing overdue policy (7+ days overdue)
- Triggered by manual operator action
- Results in service access restriction
- Governs network provisioning state

**Customer Account Suspension** (implied in `entities.md`):
- Administrative action (fraud, policy violation, contractual breach)
- Should prevent all new subscriptions, portal access, and service requests
- Should NOT be triggered automatically by billing policy

Currently there is no documentation distinguishing these two suspension types. The architecture decision "Suspension and Reactivation States" defines `suspended_overdue` and `suspended_manual` — but these are Subscription states, not Customer states.

**Impact:** Without this distinction, developers may conflate the two and implement incorrect state machine behavior for Customer account management.

**Recommendation:** Document the distinction explicitly:
1. In the new `customer-workflow.md`: define Customer account suspension as an administrative action with explicit trigger conditions
2. In `decisions.md`: add a decision clarifying Customer account suspension vs. Subscription suspension semantics

---

### Finding CL-04: Missing "Inactive" or "Churned" Customer State

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No

The Customer lifecycle ends at `Terminated`. However, there is no intermediate state for customers who have terminated all subscriptions but whose account remains on record (e.g., for win-back campaigns or historical reporting).

Common ISP scenarios:
- Customer terminates service but retains account for potential return
- Customer moves to an area not serviced by the ISP
- Customer is offboarded but the historical record is preserved for audit

**Recommendation:** Evaluate whether an `Inactive` state is needed between `Active` and `Terminated` in the Customer lifecycle. Raise this as an architecture question for the Architecture Team before implementing the CustomerStatus enum.

---

## Section 2: Customer Status Enum Analysis

### Finding CSE-01: CustomerStatus Enum Values Not Formally Defined

**Severity:** Critical  
**Type:** Gap  
**Blocking:** Yes

Unlike `UserStatus` (which has a clear migration column with documented values `active`, `inactive`, `suspended`) and `RoleStatus`/`PermissionStatus`, no document formally specifies the exact string values for the Customer status column.

`entities.md` lists lifecycle states in prose form: `Lead → Active → Suspended → Terminated`. There is no specification of:

- Exact enum case names (PascalCase PHP)
- Exact database column values (snake_case string)
- Allowed transitions between states
- Default value for new Customer records

**Impact:** This is a Critical blocking gap. The `customers` migration, `CustomerStatus` enum, and Customer model cannot be implemented without these values being formally decided.

**Proposed values** (pending architecture decision on CL-01):

| Enum Case | DB Value | Meaning |
|---|---|---|
| `Prospect` | `prospect` | Lead / new enquiry, no active service |
| `Active` | `active` | Has at least one active subscription |
| `Suspended` | `suspended` | Account suspended by administrative action |
| `Terminated` | `terminated` | Account permanently closed |

**Recommendation:** Resolve CL-01 (Lead vs Prospect terminology), then formally document CustomerStatus enum values in `entities.md` or a new enum specification document. An Architecture Decision is required before migration generation.

---

### Finding CSE-02: CustomerStatus Transition Rules Undocumented

**Severity:** High  
**Type:** Gap  
**Blocking:** Yes

Unlike `RoleStatus` and `PermissionStatus` which have `Draft → Active → Deprecated` with clear governance meaning, the Customer lifecycle transitions have no documented:
- Valid state machine transitions (which `from → to` pairs are allowed)
- Who can trigger each transition (actor matrix)
- Whether transitions are automatic (system-triggered) or manual

**Recommendation:** Define the full state transition matrix in the new `customer-workflow.md`. Reference the subscription-lifecycle.md actor matrix as the template.

---

## Section 3: Customer Relationships Analysis

### Finding CR-01: Missing Customer → User Relationship in entities.md

**Severity:** Medium  
**Type:** Inconsistency  
**Blocking:** No (should be resolved before migration)

`erd.md` explicitly documents: `CUSTOMER |o--o| USER : portal_user`  
Relationship explanation: "Customer (0..1) -> (1) User (customer may have a portal user account)"

However, `entities.md` Customer relationships do **not** include this `User` relationship. The relationship list stops at:
- Customer 1 → N Subscription ✓
- Customer 1 → N Invoice ✓
- Customer 1 → N Payment ✓
- Customer 1 → N Ticket ✓
- Customer N → 1 Cluster ✓
- Customer N → 1 ServiceArea ✓
- Customer 1 → N Attachment ✓
- Customer 1 → N TimelineEvent ✓

**Missing from entities.md:**
- `Customer 0..1 → 1 User` (portal account)
- `Customer 1 → N ActivityLog` (Platform feature says Yes, but absent from relationships list)
- `Customer 1 → N ServiceRequest` (present in erd.md but absent from entities.md)

**Recommendation:** Update `entities.md` Customer relationships section to include the three missing relationships before migration generation.

---

### Finding CR-02: ServiceRequest Absent from entities.md Customer Relationships

**Severity:** Medium  
**Type:** Inconsistency  
**Blocking:** No (should be resolved before migration)

`erd.md` documents: `CUSTOMER ||--o{ SERVICE_REQUEST : submits`

The `entities.md` Customer relationships list does not mention `ServiceRequest`. This is a documentation gap — the relationship exists in the ERD but is missing from the entity inventory.

**Recommendation:** Add `Customer 1 → N ServiceRequest` to `entities.md` Customer relationships.

---

### Finding CR-03: ActivityLog Listed as Platform Feature But Missing from Relationships

**Severity:** Low  
**Type:** Inconsistency  
**Blocking:** No

`entities.md` Customer Shared Platform Features: `Activity Log: Yes`

But the Customer relationships list does not include `Customer 1 → N ActivityLog`. This is present in the User entity (User 1 → N ActivityLog) and is needed for the polymorphic morphMany relationship to work correctly.

**Recommendation:** Add `Customer 1 → N ActivityLog` to the relationships list in `entities.md` for documentation clarity, even though the implementation uses a polymorphic morphMany (no physical FK on the customers table).

---

### Finding CR-04: Customer Notification Recipient Mechanism Undocumented

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No (resolve before Notification integration)

`business-events.md` `CustomerRegistered` states:
> "Optional. A welcome notification may be sent to the customer based on configured rules."

However, there is no documentation of **how** the Notification Engine resolves a Customer as a notification recipient:

- Does the Customer entity have email/phone columns that Notifications resolve directly?
- Or does the Notification Engine always go through the portal `User` account?
- What happens when a Customer has no portal User account — can they still receive notifications via email/SMS?

**Impact:** The Customer table's contact field design (whether `email` and `phone` live on `customers` or on the portal `User`) determines the Notification Engine integration approach.

**Recommendation:** Define the notification recipient resolution mechanism for Customers in `notification-workflow.md` or in the `customer-workflow.md`. Ensure the Customer contact fields are specified before the migration is generated.

---

### Finding CR-05: Customer QR Code Reference Undocumented

**Severity:** Low  
**Type:** Gap  
**Blocking:** No (resolve before QR implementation)

`entities.md` states: "Customer QR lookup resolves to customer billing summary and outstanding invoices."

The platform's `QRCodeReference` is a Platform entity supporting Universal QR Search. However:
- The Customer entity relationships do not mention `QRCodeReference`
- There is no documentation of when a Customer QR code is generated (on creation? on first active subscription?)
- There is no documentation of what the QR code payload contains

**Recommendation:** Add `Customer 0..1 → 1 QRCodeReference` to the Customer entity relationships in `entities.md`, and document QR generation timing in `customer-workflow.md`.

---

## Section 4: Customer Deletion Policy Analysis

### Finding CD-01: Deletion Policy Condition "Financial Records" Undefined

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No (should be resolved before migration)

`entities.md` states: "Deletion behavior: Soft Delete by default; Restrict if related to financial records."

`erd.md` states: "Customer uses Soft Delete, Restrict with financial dependencies."

However, neither document specifies exactly what constitutes a "financial dependency" that triggers the Restrict behavior:

- Does any Invoice (regardless of status) trigger Restrict?
- Does any Payment trigger Restrict?
- Does a Draft Invoice (never published) trigger Restrict?
- Does a voided/reversed financial record trigger Restrict?

**Impact:** The `CustomerService::delete()` method cannot be correctly implemented without knowing the exact conditions that prevent soft deletion.

**Recommendation:** Define the exact Restrict conditions in `entities.md`:

> "Restrict when: Customer has any Invoice in Published, Overdue, or Paid status; OR Customer has any Payment record; OR Customer has any active PaymentAllocation."

---

### Finding CD-02: Cascade Behavior for Customer Soft Delete Unspecified

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No

When a Customer is soft-deleted, the behavior of child entities is not documented:
- Should Subscriptions be automatically soft-deleted?
- Should open Tickets be automatically closed or reassigned?
- Should active ServiceRequests be automatically cancelled?

**Recommendation:** Document the cascade behavior for Customer soft deletion in `customer-workflow.md` or `entities.md`. Example: "Customer soft delete requires: no active Subscriptions, no open Tickets, no pending ServiceRequests. These must be resolved before deletion is permitted."

---

## Section 5: Customer Business Events Analysis

### Finding CBE-01: Customer Business Events Are Incomplete

**Severity:** High  
**Type:** Gap  
**Blocking:** No (resolve before event dispatch implementation)

`entities.md` documents two Customer-produced events: `CustomerRegistered` and `CustomerUpdated`.

Both are defined in `business-events.md`. However, the following events are **not documented** despite corresponding to significant lifecycle transitions in the Customer entity:

| Missing Event | Trigger | Why It Matters |
|---|---|---|
| `CustomerSuspended` | Customer account suspended by admin | Notification Engine needs this; Portal must show account suspended |
| `CustomerReactivated` | Customer account reactivation | Portal and Notification must update |
| `CustomerTerminated` | Customer account permanently closed | Billing, Provisioning, Portal must react |
| `CustomerConverted` | Lead/Prospect → Active customer | Key business KPI milestone; reporting and notification trigger |

Additionally, the `CustomerRegistered` event covers creation, but there is no event for:
- `CustomerPortalAccountCreated` — when a portal User is linked to a Customer
- `CustomerContactUpdated` — distinct from general `CustomerUpdated` for notification re-routing

**Recommendation:** Add the missing business events to `business-events.md` before implementing Customer lifecycle state machine. At minimum, `CustomerSuspended`, `CustomerReactivated`, and `CustomerTerminated` are required to support the Notification Engine and Customer Portal integration.

---

### Finding CBE-02: CustomerUpdated Event Granularity Insufficient

**Severity:** Low  
**Type:** Gap  
**Blocking:** No

`business-events.md` `CustomerUpdated` says:
> "Notification Impact: Conditional. Contact channel updates may trigger re-verification or delivery configuration updates."

The event is defined broadly as "any profile modification." However, Notification Engine consumers need to distinguish:
- Address change (service delivery address) → Provisioning may need re-evaluation
- Contact channel change (phone, email) → Notification routing changes
- Identity change (name) → Low impact

**Recommendation:** Consider whether `CustomerContactUpdated` should be a distinct event from `CustomerAddressUpdated` in the catalog, or whether `CustomerUpdated` should carry a `changedFields` context array so consumers can filter by relevant field changes.

---

## Section 6: Customer Ownership Boundaries Analysis

### Finding COB-01: Customer is Aggregate Root — Child Entity Access Rules Not Documented

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No

`entities.md` correctly designates Customer as an Aggregate Root. The Service-First Application Layer decision requires that "child entities must be accessed through their aggregate root."

However, there is no documentation of:
- Which entities are direct children of the Customer aggregate (vs. Subscription's own children)
- Invoice — is it a direct child of Customer or accessed through Subscription?
- Payment — is it a direct child of Customer?

The ERD documents both: `CUSTOMER ||--o{ INVOICE` and `SUBSCRIPTION ||--o{ INVOICE`. Invoice has dual FK references to both Customer and Subscription.

**Impact:** Services that query invoices need to know whether to use `$customer->invoices()` or `$customer->subscriptions()->with('invoices')` — and this architectural choice is not documented.

**Recommendation:** Document aggregate access patterns in `entities.md` Customer section:
- State which entities are directly accessible via Customer FK (Invoice, Payment — for billing context)
- State which entities require traversal through Subscription (InvoiceItems, ProvisioningRequests)
- State the expected access pattern for Customer 360 (denormalized direct access for performance)

---

### Finding COB-02: One Active Primary Subscription Rule — Database Enforcement Not Specified

**Severity:** High  
**Type:** Gap  
**Blocking:** No (resolve before Subscription migration)

The business rule "Customer may own only one active primary internet subscription" is documented in both `entities.md` and `decisions.md`. However, there is no documentation of the database-level enforcement mechanism:

- Should there be a partial unique index on `subscriptions` where `status = 'active'`?
- Should enforcement be application-only (Service validation)?
- Is there a `subscription_type` column (`primary` / `addon`) to support the "primary subscription" distinction?

**Impact:** Without database-level enforcement, a race condition could create duplicate active subscriptions if two concurrent requests activate subscriptions for the same customer simultaneously.

**Recommendation:** Document the enforcement approach in `entities.md` and `erd.md`:
1. Application-level check in `ActivateSubscriptionService` using `lockForUpdate()` (per `transactions.md` pessimistic locking)
2. Optional: Unique partial index as defense in depth: `UNIQUE INDEX on (customer_id) WHERE status = 'active' AND type = 'primary'`

This requires defining the `subscription_type` column if "primary" vs "addon" subscriptions are in scope for v1.

---

## Section 7: Customer Interaction with Other Modules

### Finding CIM-01: Customer ↔ Subscription — Lifecycle Dependency Rules Incomplete

**Severity:** High  
**Type:** Gap  
**Blocking:** No (resolve before Subscription implementation)

The lifecycle interaction between Customer status and Subscription status is not formally specified:

**Unanswered questions:**
- If Customer is Suspended, are existing active Subscriptions automatically suspended?
- If Customer is Terminated, are all Subscriptions automatically terminated?
- Can a Suspended Customer create a new Subscription?
- Can a Terminated Customer have any ongoing Subscriptions?
- When Customer transitions from Prospect to Active — is this triggered by the first Subscription activation?

**Recommendation:** Document the Customer ↔ Subscription lifecycle dependency rules in `customer-workflow.md`. These rules are critical for implementing `CustomerService` and ensuring correct cascade behavior.

---

### Finding CIM-02: Customer ↔ Billing — Invoice Access Pattern Ambiguous

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No

The ERD documents `CUSTOMER ||--o{ INVOICE` (Customer 1 → N Invoice). This means the `invoices` table has a direct `customer_id` FK. But Invoice also belongs to Subscription.

For the Customer 360 billing tab:
- Should the query use `Invoice::where('customer_id', $customerId)` (direct)?
- Or `Invoice::whereHas('subscription', fn($q) => $q->where('customer_id', $customerId))`?

Both return the same records, but the direct FK approach is significantly faster. The dual-FK design is intentional per the ERD.

**Recommendation:** Document the intended access pattern in `entities.md`. Confirm that `customer_id` on `invoices` is correct and intentional (not a modeling mistake) and serves as a denormalization for billing context on Customer 360.

---

### Finding CIM-03: Customer ↔ Payment — Same Ambiguity as Billing

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No

Same pattern as billing. `CUSTOMER ||--o{ PAYMENT` — Payment has a direct `customer_id` FK. The ERD design is intentional but undocumented.

**Recommendation:** Same as CIM-02 — confirm and document that `customer_id` on `payments` is intentional for direct Customer 360 access.

---

### Finding CIM-04: Customer ↔ Ticket — Assignment Model Undefined

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No (resolve before Ticket module)

`entities.md` Customer relationships include `Customer 1 → N Ticket`. Ticket is listed as an Operational entity. However:
- Are Tickets always initiated by Customers, or can Tickets be system-generated?
- Can a Ticket exist without a Customer reference (e.g., infrastructure tickets)?
- When Customer is Terminated, what happens to open Tickets?

**Recommendation:** These questions should be resolved in `docs/workflows/ticket-workflow.md` before Ticket module implementation. For the Customer module (Sprint 2.1), only the relationship existence needs to be confirmed.

---

### Finding CIM-05: Customer ↔ Customer Portal — Portal Account Lifecycle Undefined

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No (resolve before Customer Portal implementation)

`erd.md` shows `CUSTOMER |o--o| USER : portal_user`. The Customer has an optional FK to a `users` record for portal access.

Unresolved questions:
- When is the portal User account created? On CustomerRegistered? Manually by admin? On customer's first self-service request?
- When Customer is Suspended, is the portal User account also suspended?
- When Customer is Terminated, is the portal User account deleted/disabled?
- Can a Customer have a portal account created before their first Subscription is active?

**Recommendation:** Document Customer Portal account creation and lifecycle management in `customer-workflow.md`. Reference the `CustomerPortal` decisions in `decisions.md`.

---

### Finding CIM-06: Customer ↔ Notification — Recipient Resolution Gap (see also CR-04)

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No (resolve before Notification integration)

When `CustomerRegistered` fires, the Notification workflow should potentially send a welcome notification. However:
- Where are the Customer's contact channels stored? On the `customers` table? On the linked `users` record? On a separate `customer_contacts` table?
- If Customer has no portal User, how does the Notification Engine send email/SMS?

**Recommendation:** Define Customer contact data model before migration. Options:
1. Dedicated fields on `customers` table: `email`, `phone`, `whatsapp_phone`
2. Normalized `customer_contacts` table (name, type, value)

Option 1 is simpler for v1.0 given the platform's current scope.

---

## Section 8: Customer Module Data Model Gaps

### Finding DMG-01: Customer Key Attributes Not Column-Level Specified

**Severity:** Critical  
**Type:** Gap  
**Blocking:** Yes

`entities.md` documents Customer Key Attributes as:
> "Customer identity profile, contact profile, operational status, service location context."

This is a conceptual description, not a column specification. Before the migration can be generated, the actual columns must be formally specified. Based on the project scope and ISP operational context, the expected columns are:

**Identity:**
- `id` (PK)
- `customer_number` (unique, auto-generated reference code)
- `name` (full name for individual, company name for business)
- `type` (`individual` / `business`) — see Finding DMG-02

**Contact:**
- `email`
- `phone` (primary phone)
- `whatsapp_phone` (may differ from phone)
- `alt_phone` (optional secondary contact)

**Service Location:**
- `address` (service installation address)
- `latitude`, `longitude` (GPS for GIS and field navigation)

**Operational:**
- `cluster_id` (FK)
- `service_area_id` (FK)
- `user_id` (FK, nullable — portal account)
- `status` (CustomerStatus enum)
- `notes` (internal operational notes)

**Timestamps:**
- `created_at`, `updated_at`, `deleted_at`

**Recommendation:** Formally document Customer column specifications in `entities.md` (under Key Attributes) before generating the migration. This is a Critical blocker.

---

### Finding DMG-02: Customer Type (Individual vs Business) Undefined

**Severity:** High  
**Type:** Gap  
**Blocking:** Yes

`glossary.md` defines Customer as "a person or organization." This implies two customer types exist:
- **Individual** — natural person, residential subscription
- **Business** — legal entity, potentially different tax, billing, and contact handling

However, there is no `type` field documented for the Customer entity, no enum defined, and no business rules distinguishing individual vs. business behavior.

**Impact:**
- Billing (IDR tax handling may differ for business customers)
- Legal identity fields (individual: ID number; business: company registration number, tax ID)
- Contact model (individual has one person; business may have multiple contacts)

**Recommendation:** Define whether `customer_type` (individual/business) is in scope for v1.0. If yes, add it to the Customer entity specification and document any differential business rules. If no, document the explicit scope exclusion.

---

### Finding DMG-03: Customer Number / External Identifier Undefined

**Severity:** High  
**Type:** Gap  
**Blocking:** No (but required for QR and Global Search)

The Customer entity needs a stable, human-readable external identifier (customer number, account number) for:
- QR code lookup by field staff
- Collector referencing in visit records
- Customer Portal account reference
- Invoice header reference

There is no `customer_number` or equivalent field documented in `entities.md`. The Global Search decision implies customers are searchable by identifiers, but the identifier itself is unspecified.

**Recommendation:** Define `customer_number` as a unique, auto-generated identifier (e.g., `ISP-0001`, `CUST-000001`) in `entities.md`. Document the generation strategy (sequential, prefix-based, or UUID-derived).

---

### Finding DMG-04: Service Address vs Billing Address Not Distinguished

**Severity:** Medium  
**Type:** Gap  
**Blocking:** No

The Customer entity has a "service location context" but does not distinguish between:
- **Service address** — where the ONU/ONT is installed, used for provisioning and field navigation
- **Billing/correspondence address** — where invoices and correspondence are sent (may differ for businesses)

For v1.0 residential ISPs, these are typically the same address. However, for business customers this distinction may matter.

**Recommendation:** For v1.0, use a single address model on the `customers` table. Document this simplification explicitly so it can be extended in v1.1 if business customer requirements emerge.

---

## Section 9: Architecture Compliance Assessment

### Compliance Check — Architecture Freeze v1.0

| Architecture Standard | Compliance Status | Notes |
|---|---|---|
| Service-First Application Layer | ✅ Compliant (when implemented) | CustomerService must own all business logic |
| Lifecycle State Machine | ⚠️ Gap | Customer lifecycle states not fully defined; customer-workflow.md missing |
| State Transition Logging | ⚠️ Gap | Transitions undefined; cannot implement `StateTransitionLog` entries without them |
| Financial Immutability | ✅ No conflict | Customer has no financial immutability concerns directly |
| Event-Driven Architecture | ⚠️ Gap | Missing events: CustomerSuspended, CustomerReactivated, CustomerTerminated, CustomerConverted |
| Metadata-Driven CRUD | ✅ Applicable | Customer CRUD is suitable for metadata-driven pattern |
| Entity 360 Workspace | ✅ Documented | Customer 360 is explicitly named in glossary; tabs to be defined |
| Global Search | ✅ Documented | `Global Search: Yes` in entities.md |
| Canonical Terminology | ⚠️ Gap | Lead vs Prospect inconsistency must be resolved |

---

## Section 10: Missing Architecture Decisions

The following architecture questions are not covered by any existing decision in `decisions.md` and require formal Architecture Decisions before implementation:

### AD-MISSING-01: Customer Status Canonical Values

A formal decision on CustomerStatus enum values and the Lead/Prospect terminology is required.

### AD-MISSING-02: Customer Type Scope (Individual vs Business)

A formal decision on whether customer type differentiation is in scope for v1.0.

### AD-MISSING-03: Customer Contact Data Model

A formal decision on whether Customer contact fields (email, phone, whatsapp) live directly on the `customers` table or in a normalized contacts table.

### AD-MISSING-04: Customer Number Generation Strategy

A formal decision on the format and generation strategy for the customer external identifier.

### AD-MISSING-05: One Active Subscription Database Enforcement

A formal decision on whether the uniqueness constraint is application-only or reinforced with a partial database index.

### AD-MISSING-06: Customer ↔ Subscription Cascade on Status Change

A formal decision on whether Customer account suspension/termination automatically cascades to Subscription state.

---

## Section 11: Technical Debt Identified

| ID | Item | Priority | Sprint |
|---|---|---|---|
| TD-01 | `entities.md` Customer lifecycle references wrong workflow document (`subscription-lifecycle.md`) | High | Sprint 2.1 pre-work |
| TD-02 | `entities.md` Customer relationships missing User, ActivityLog, ServiceRequest | Medium | Sprint 2.1 pre-work |
| TD-03 | No `CustomerStatus` enum specification | Critical | Sprint 2.1 pre-work |
| TD-04 | No `customer-workflow.md` document | High | Sprint 2.1 pre-work |
| TD-05 | Missing business events: CustomerSuspended, CustomerReactivated, CustomerTerminated, CustomerConverted | High | Sprint 2.1 |
| TD-06 | Customer Key Attributes not column-level specified | Critical | Sprint 2.1 pre-work |
| TD-07 | Customer type (individual/business) decision pending | High | Sprint 2.1 pre-work |
| TD-08 | Customer notification recipient resolution undocumented | Medium | Sprint 2.2 (Notification integration) |
| TD-09 | QR Code relationship missing from Customer entity | Low | Sprint 2.3 (QR implementation) |
| TD-10 | Customer Portal account lifecycle undefined | Medium | Sprint 3 (Portal) |

---

## Section 12: Future Implementation Risks

### Risk R-01: CustomerStatus Enum Mismatch After Implementation

**Probability:** High if not resolved now  
**Impact:** High

The `UserStatus` enum was implemented with incorrect values (`Draft`, `Disabled`) that required a later refactoring task. The same risk applies to `CustomerStatus` if the Lead/Prospect inconsistency is not resolved before the enum is created.

**Mitigation:** Resolve Finding CL-01 and CSE-01 before any Customer code is generated.

---

### Risk R-02: Customer 360 Tab Scope Creep

**Probability:** Medium  
**Impact:** Medium

The Customer 360 workspace is referenced in the glossary as a key operational pattern. Without defining which tabs it contains and which modules contribute tabs, implementation may start in an inconsistent direction, requiring later refactoring.

**Mitigation:** Define Customer 360 tab inventory in `customer-workflow.md` or a dedicated Customer 360 specification.

---

### Risk R-03: N+1 Queries on Customer 360

**Probability:** High if not addressed  
**Impact:** Medium

The Customer 360 workspace will aggregate data from Subscriptions, Invoices, Payments, Tickets, Timeline, ActivityLog, and Attachments. Without explicit eager-loading specifications, the Customer 360 view will be vulnerable to severe N+1 query problems.

**Mitigation:** Document the expected eager-load chain for Customer 360 views in `customer-workflow.md` or in the Service design. Establish `$customer->load([...])` specification as part of implementation planning.

---

### Risk R-04: Duplicate Active Subscription Race Condition

**Probability:** Low under normal load, High under concurrent requests  
**Impact:** Critical (financial integrity)

The one-active-subscription rule relies on application-level enforcement. Under concurrent requests (e.g., customer service and automated retry both attempting activation simultaneously), a race condition can create duplicate active subscriptions.

**Mitigation:** See Finding COB-02. Implement `lockForUpdate()` in `ActivateSubscriptionService` per `transactions.md` pessimistic locking guidance.

---

### Risk R-05: Customer Financial Restrict Condition Ambiguity

**Probability:** High  
**Impact:** Medium

Without precise specification of what constitutes a "financial record" for the Restrict deletion rule, `CustomerService::delete()` may be implemented too permissively (allowing deletion when invoices exist) or too restrictively (blocking deletion for voided draft invoices).

**Mitigation:** Resolve Finding CD-01 before implementation.

---

## Summary of Findings

### Critical (Blocking)

| ID | Finding | Required Action |
|---|---|---|
| CSE-01 | CustomerStatus enum values not formally defined | Define enum before migration |
| DMG-01 | Customer key attributes not column-level specified | Define columns before migration |

### High (Should Resolve Before Implementation)

| ID | Finding | Required Action |
|---|---|---|
| CL-01 | Lead vs Prospect terminology inconsistency | Architecture decision + glossary update |
| CL-02 | No customer-workflow.md document | Create workflow document |
| CL-03 | Customer suspension vs subscription suspension undefined | Document distinction |
| CSE-02 | CustomerStatus transition rules undocumented | Document in customer-workflow.md |
| CR-01 | Missing User, ActivityLog, ServiceRequest relationships in entities.md | Update entities.md |
| CR-02 | ServiceRequest absent from Customer relationships | Update entities.md |
| COB-02 | One active subscription enforcement mechanism undefined | Document and decide |
| CIM-01 | Customer ↔ Subscription lifecycle dependency rules incomplete | Document in customer-workflow.md |
| CBE-01 | Missing business events (Suspended, Reactivated, Terminated, Converted) | Add to business-events.md |
| DMG-02 | Customer type (individual/business) undefined | Architecture decision required |
| DMG-03 | Customer number/external identifier undefined | Define before migration |

### Medium (Should Resolve Before Sprint 2 Module Integration)

| ID | Finding | Required Action |
|---|---|---|
| CR-03 | ActivityLog missing from relationships list | Update entities.md |
| CR-04 | Customer notification recipient mechanism undocumented | Document in notification or customer workflow |
| CD-01 | Financial restrict conditions undefined | Document in entities.md |
| CD-02 | Cascade behavior on Customer soft delete unspecified | Document in customer-workflow.md |
| COB-01 | Aggregate access patterns not documented | Document in entities.md |
| CIM-02 | Invoice access pattern ambiguous (direct vs through Subscription) | Confirm and document |
| CIM-03 | Payment access pattern ambiguous | Confirm and document |
| CIM-05 | Customer Portal account lifecycle undefined | Document in customer-workflow.md |
| CIM-06 | Notification recipient resolution gap | Document before Notification integration |
| DMG-04 | Service address vs billing address not distinguished | Document simplification decision |

### Low (Track for Future Sprints)

| ID | Finding | Required Action |
|---|---|---|
| CL-04 | Missing Inactive/Churned state | Evaluate need; future architecture decision |
| CR-05 | QR Code relationship missing | Add before QR implementation |
| CBE-02 | CustomerUpdated granularity insufficient | Evaluate before Notification integration |
| CIM-04 | Customer ↔ Ticket assignment model undefined | Resolve before Ticket module |

---

## Recommended Pre-Implementation Actions

The following actions must be completed **before any Customer module code is generated**:

### Phase 1 — Architecture Decisions (Immediate)

1. **Resolve Lead/Prospect** — Make an Architecture Decision on canonical CustomerStatus values, adopting "Prospect" terminology. Update `entities.md` and `glossary.md`.
2. **Define Customer type scope** — Make an Architecture Decision on individual vs business customer types for v1.0.
3. **Define Customer number strategy** — Make an Architecture Decision on external identifier format and generation.
4. **Define one-subscription enforcement** — Make an Architecture Decision on application-only vs database partial index.

### Phase 2 — Documentation Updates (Sprint 2.1 Pre-Work)

5. **Create `docs/workflows/customer-workflow.md`** — Document Customer account lifecycle, state transitions, actor matrix, entry/exit conditions, business rules.
6. **Update `entities.md`** — Add missing relationships (User, ActivityLog, ServiceRequest), define exact column attributes, update Lifecycle Reference.
7. **Update `business-events.md`** — Add missing events: `CustomerSuspended`, `CustomerReactivated`, `CustomerTerminated`, `CustomerConverted`.
8. **Update `docs/architecture/decisions.md`** — Add formal Architecture Decision entries for items AD-MISSING-01 through AD-MISSING-06.

### Phase 3 — Implementation (Sprint 2.1 Proper)

Once Phases 1 and 2 are complete:

9. Generate `CustomerStatus` enum and migration
10. Implement Customer model, CustomerService, CustomerPolicy, CustomerController
11. Implement Customer 360 workspace layout
12. Implement Domain Events: `CustomerRegistered`, `CustomerUpdated`, `CustomerConverted`

---

## Architecture Decisions Required Before Sprint 2.1 Implementation

The following decisions should be recorded in `decisions.md` as formal entries in the Customer Management section:

1. **Customer Status Canonical Values** — Defines `CustomerStatus` enum values and the canonical lifecycle state names
2. **Customer Type Classification** — Defines whether individual/business distinction is in v1.0 scope
3. **Customer Contact Data Model** — Defines where email/phone/WhatsApp fields live
4. **Customer External Identifier Format** — Defines `customer_number` generation strategy
5. **One Active Subscription Enforcement** — Defines database vs application enforcement approach
6. **Customer Cascade on Status Change** — Defines whether Customer suspension/termination cascades to Subscription

---

**End of Review**
