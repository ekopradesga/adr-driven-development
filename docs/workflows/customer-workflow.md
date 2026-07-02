# Customer Workflow

**Version:** 1.0  
**Status:** Authoritative  
**Last Updated:** 2026-07-02  
**Owner:** Architecture Team  
**Module:** Customer Management

---

## Purpose

This document defines the authoritative lifecycle, business rules, state transitions, and operational behaviors for the Customer entity. It is the canonical reference for Customer account management and must be read before implementing any Customer module code.

This document is distinct from `subscription-lifecycle.md`. The Subscription lifecycle governs service contract states. This document governs the Customer *account* lifecycle — an independent concept that wraps and outlives individual Subscriptions.

---

## Scope

This workflow governs:

- Customer account lifecycle (Prospect → Active → Suspended → Terminated)
- Customer account suspension semantics (distinct from Subscription suspension)
- Customer ↔ Subscription lifecycle cascade rules
- Customer ↔ Portal User account lifecycle
- Customer soft delete pre-conditions and cascade behavior
- Customer 360 workspace tab inventory
- Customer business events and timeline entries
- Customer notification responsibilities
- Customer QR code generation
- Customer number generation

This document does **not** govern:
- Subscription lifecycle states (see `subscription-lifecycle.md`)
- Billing invoice generation (see `billing-workflow.md`)
- ONU provisioning (see `provisioning-workflow.md`)
- Support ticket lifecycle (see `ticket-workflow.md`)

---

## Actor Matrix

| Actor | Prospect | Active | Suspended | Terminated | Notes |
|---|---|---|---|---|---|
| **Sales** | Create, update | Read | Read | Read | Owns lead-to-prospect conversion |
| **Customer Service** | Update | Update, suspend | Reactivate | Read | Day-to-day account management |
| **Administrator** | Full access | Full access | Full access | Full access | Override and governance |
| **Finance** | Read | Read | Read | Read | Financial oversight only |
| **System** | Auto-activate on first subscription | — | — | — | Automated transitions only |
| **Collector** | Read | Read | Read | Read | Field operations; no account mutation |
| **NOC** | — | Read | Read | — | Network context only |
| **Customer (Portal)** | Limited self-service | Self-service | View only | — | Cannot change account status |

---

## Lifecycle States

### State: Prospect

**Description:**  
A new customer record has been created. The customer has expressed interest in service but no active subscription exists. The account is visible to internal staff only.

**Entry Conditions:**
- A new Customer record is created by Sales or Customer Service
- Customer identity and at least one contact field (phone or email) are captured
- No active Subscription exists for this Customer

**Exit Conditions:**
- Customer's first Subscription is activated (→ Active)
- Customer record is permanently terminated without service (→ Terminated)

**Allowed Transitions:**

| To State | Trigger | Actor | Type |
|---|---|---|---|
| Active | First Subscription activated | System | Automatic |
| Terminated | Lead closed — no service interest | Sales / Admin | Manual |

**Business Rules:**
- Prospect Customers may not have Invoices generated
- Prospect Customers may not have Payments recorded
- Prospect state does not start monitoring or provisioning
- A Prospect Customer may have a Survey and Installation in progress (pre-activation)
- Multiple Prospect records for the same contact are not prohibited at this stage (deduplication is operational)
- Portal access is not granted while Customer is in Prospect state

**Generated Events:**
- `CustomerRegistered` — on Customer creation
- `CustomerUpdated` — on profile modification

**Timeline Entries:**
- Customer account created
- Customer profile updated (on each significant change)

**Notifications:**
- Optional welcome notification based on configured Settings Engine rule

**Customer Portal Visibility:**
- Not accessible in Prospect state

---

### State: Active

**Description:**  
The Customer has at least one active Subscription. Billing is live, monitoring is active, and the Customer has full portal access.

**Entry Conditions:**
- Customer is in Prospect state
- Customer's first Subscription transitions to Active via `SubscriptionActivated` event

**Exit Conditions:**
- Administrator manually suspends the Customer account (→ Suspended)
- All Subscriptions are terminated and the account is closed (→ Terminated)

**Allowed Transitions:**

| To State | Trigger | Actor | Type |
|---|---|---|---|
| Suspended | Administrative action (fraud, policy violation, contractual breach) | Admin / Customer Service | Manual only |
| Terminated | Account closure after all Subscriptions terminated | Admin | Manual |

**Business Rules:**
- Active state is the only state where Subscriptions may be created or activated
- A Suspended Customer account does **not** automatically suspend active Subscriptions; Subscription suspension is governed independently by the billing overdue policy
- A Customer may be Active even if all Subscriptions are currently suspended (billing-driven); the Customer account status and Subscription status are independent
- Portal account remains accessible while Customer is Active

**Generated Events:**
- `CustomerConverted` — on first transition from Prospect to Active
- `CustomerUpdated` — on profile modification

**Timeline Entries:**
- Customer account activated (on first Subscription activation)
- Customer profile updated

**Notifications:**
- `CustomerConverted` may trigger a welcome / activation notification based on configured rules

**Customer Portal Visibility:**
- Full portal access available
- Service status, invoices, payments, and tickets visible

---

### State: Suspended

**Description:**  
The Customer account has been suspended by an authorized administrator. Suspension is an administrative action only — it is **not** triggered by billing policy. Billing-driven service restriction operates on the Subscription level, not the Customer account level.

**Entry Conditions:**
- Customer is in Active state
- An authorized administrator or Customer Service representative explicitly suspends the account
- A documented reason must be provided (fraud, policy violation, legal hold, contractual breach)

**Exit Conditions:**
- Administrator reactivates the account (→ Active)
- Account is permanently closed (→ Terminated)

**Allowed Transitions:**

| To State | Trigger | Actor | Type |
|---|---|---|---|
| Active | Administrative reactivation decision | Admin / Customer Service | Manual only |
| Terminated | Permanent account closure | Admin | Manual |

**Business Rules:**
- Customer account suspension is **NOT** triggered by billing overdue policy; overdue-driven service restriction is a Subscription concern governed by `suspension-lifecycle.md`
- A Suspended Customer account blocks: new Subscription creation, new ServiceRequest submission, portal access
- A Suspended Customer account does **not** automatically suspend active Subscriptions; they continue to operate independently
- Suspension reason must be recorded in ActivityLog
- Customer Service may suspend; only Administrator may override or terminate a Suspended account

**Generated Events:**
- `CustomerSuspended` — on entry to Suspended state

**Timeline Entries:**
- Customer account suspended (with reason)
- Customer account reactivated (on exit)

**Notifications:**
- Suspension notification sent to Customer via configured channels
- Internal notification to Customer Service and Administrator

**Customer Portal Visibility:**
- Portal access is blocked while Customer is Suspended
- Customer receives a "account suspended" message on portal login attempt

---

### State: Terminated

**Description:**  
The Customer account is permanently closed. No new service contracts, payments, or interactions may be initiated. The record is retained for audit and financial history.

**Entry Conditions from Prospect:**
- Sales or Administrator closes the lead without service being established
- No financial records exist (no published Invoices, no Payments)

**Entry Conditions from Active or Suspended:**
- All Subscriptions are in Terminated state
- All outstanding Invoices are settled or written off (no open balance)
- Administrator explicitly terminates the account

**Exit Conditions:**
- None. Terminated is a terminal state. Accounts may not be reactivated after Termination. A new Customer record must be created for re-engagement.

**Allowed Transitions:**

| From State | Trigger | Actor | Type |
|---|---|---|---|
| Prospect | Lead closed without service | Sales / Admin | Manual |
| Active | All subscriptions terminated + balance cleared | Admin | Manual |
| Suspended | Permanent closure decision | Admin | Manual |

**Business Rules:**
- No new Subscriptions may be created for a Terminated Customer
- No new Payments may be recorded for a Terminated Customer
- Financial history (Invoices, Payments, Allocations) is retained indefinitely for audit
- Portal User account is disabled on Customer termination (UserStatus → Inactive)
- Terminated is a terminal state — no transition back to any other state
- A new Customer record must be created if the customer wishes to re-engage

**Generated Events:**
- `CustomerTerminated` — on entry to Terminated state

**Timeline Entries:**
- Customer account terminated (with reason)

**Notifications:**
- Termination confirmation notification sent to Customer
- Internal notification to Finance and Customer Service

**Customer Portal Visibility:**
- Portal access is permanently disabled

---

## State Transition Matrix

| From \ To | Prospect | Active | Suspended | Terminated |
|---|---|---|---|---|
| **Prospect** | — | ✅ System (first Subscription activated) | ❌ Not permitted | ✅ Manual (Admin/Sales, no financial records) |
| **Active** | ❌ Not permitted | — | ✅ Manual (Admin/CS, reason required) | ✅ Manual (Admin, all subs terminated, balance cleared) |
| **Suspended** | ❌ Not permitted | ✅ Manual (Admin/CS) | — | ✅ Manual (Admin) |
| **Terminated** | ❌ Not permitted | ❌ Not permitted | ❌ Not permitted | — |

---

## Customer vs Subscription Suspension — Canonical Distinction

This is a critical architectural boundary. Both Customer accounts and Subscriptions have a "suspended" state, but they are governed by completely different policies and must never be conflated.

| Dimension | Customer Account Suspension | Subscription Suspension |
|---|---|---|
| **Trigger** | Administrative action only (fraud, policy violation, legal) | Billing overdue policy (7+ days) OR manual operator action |
| **Actor** | Admin / Customer Service | System (automated) / Admin / Collector |
| **Effect** | Blocks new Subscriptions, ServiceRequests, portal access | Restricts internet service access at network level |
| **Cascade** | Does NOT automatically suspend active Subscriptions | Triggers provisioning restriction on ONU |
| **Recovery** | Manual Admin/CS action required | Payment settlement OR manual override |
| **Governed by** | This document (customer-workflow.md) | `subscription-lifecycle.md` |
| **Status field** | `customers.status` | `subscriptions.status` |
| **Events** | `CustomerSuspended`, `CustomerReactivated` | `SubscriptionSuspended`, `SubscriptionReactivated` |

**Key Rule:** A Customer may be `active` at the account level while having all Subscriptions in `suspended` state (billing-driven). These are orthogonal status axes that must be evaluated independently.

---

## Customer ↔ Subscription Lifecycle Cascade Rules

### Prospect → Active

- Triggered automatically when the Customer's first Subscription is activated (`SubscriptionActivated` event received)
- `CustomerService` listens to `SubscriptionActivated` and transitions Customer from Prospect to Active
- Fires `CustomerConverted` event

### Active (no cascade on Subscription suspension)

- Customer `active` status is NOT affected when a Subscription is suspended due to billing overdue
- Customer `active` status is NOT affected when a Subscription is manually suspended
- Customer account status tracks account-level eligibility, not service delivery state

### Terminated

- Before a Customer can be Terminated, ALL Subscriptions must be in `terminated` state
- `CustomerService::terminate()` validates: `$customer->subscriptions()->whereNotIn('status', ['terminated'])->exists()` → throws `BusinessRuleException` if any non-terminated Subscription exists
- On Customer termination, no automatic cascade to Subscriptions (they must already be terminated)

### No Active Subscription Creates "Customer without Service"

- An Active Customer with all Subscriptions terminated is still `active` at the account level
- This is a valid intermediate state (customer terminated service but account not yet formally closed)
- CustomerService does NOT auto-terminate the account when the last Subscription is terminated
- Account closure is always a deliberate manual action by Administrator

---

## Customer Number Generation

Every Customer record is assigned a unique, human-readable external identifier (`customer_number`) on creation.

**Format:** `CUST-{zero-padded 6-digit sequential number}`  
**Examples:** `CUST-000001`, `CUST-000002`, `CUST-001234`

**Generation Rules:**
- Generated by `CustomerService::create()` before insert
- Derived from a sequential counter using `SELECT MAX(customer_number)` with exclusive lock or auto-increment pattern
- Stored in the `customers` table as a unique string column
- Immutable after creation — never changes for the lifetime of the Customer record
- Used as the primary external identifier in: QR codes, invoice headers, Collector visit records, portal reference, Global Search

**Implementation note:** Use `DB::transaction` with `lockForUpdate()` to prevent race conditions during high-concurrency creation.

---

## Customer Type

All customers in v1.0 are treated as **individuals** by default. The `customer_type` column is included as a passive data field to support future differentiation.

**Enum values:**
- `individual` (default) — natural person, residential subscription
- `business` — legal entity (passive only in v1.0; no differential business rules applied)

**v1.0 scope limitation:** No differential billing, contact, or tax behavior based on `customer_type` is implemented in v1.0. The field exists for data capture and future v1.1 extension.

---

## Customer 360 Workspace

The Customer 360 workspace is the unified operational profile for a Customer. It follows the Entity 360 workspace pattern.

### Required Tabs

| Tab | Content | Available From State | Source Module |
|---|---|---|---|
| **Overview** | Profile card, status badge, active subscription summary, outstanding balance | All states | Customer, Billing |
| **Subscriptions** | List of all subscriptions with status badges and quick-action links | All states | Subscription |
| **Billing** | Invoice list with status, due dates, amounts, aging | Active, Terminated | Billing |
| **Payments** | Payment history with allocation summary | Active, Terminated | Payments |
| **Tickets** | Open and recent tickets with status and assignment | Active, Suspended | Tickets |
| **Timeline** | Chronological business event narrative | All states | Timeline |
| **Activity Log** | Detailed audit entries | All states | Activity Log |
| **Attachments** | Documents, photos, GPS records | All states | Attachments |

### Eager Load Chain for Customer 360

```
$customer->load([
    'cluster',
    'serviceArea',
    'subscriptions.package',
    'invoices' => fn($q) => $q->latest()->limit(10),
    'payments' => fn($q) => $q->latest()->limit(10),
    'tickets' => fn($q) => $q->whereIn('status', ['open', 'in_progress'])->latest()->limit(5),
    'timelineEvents' => fn($q) => $q->latest()->limit(20),
    'activityLogs' => fn($q) => $q->latest()->limit(20),
    'attachments',
])
```

The Overview tab requires the active Subscription eagerly loaded with its Package to display the service summary without additional queries.

---

## Customer Soft Delete Rules

### Pre-Delete Validation

`CustomerService::delete()` must validate ALL of the following before soft-deleting:

| Condition | Validation | On Failure |
|---|---|---|
| Financial Restrict | No Invoice with status `published`, `overdue`, or `paid` | Throw `BusinessRuleException` |
| Financial Restrict | No Payment record exists | Throw `BusinessRuleException` |
| Financial Restrict | No active PaymentAllocation exists | Throw `BusinessRuleException` |
| Operational Restrict | No active Subscription (`status = 'active'`) | Throw `BusinessRuleException` |
| Operational Restrict | No open Ticket (`status` not in `closed`, `cancelled`) | Throw `BusinessRuleException` |
| Operational Restrict | No pending ServiceRequest | Throw `BusinessRuleException` |

**Draft Invoices** (never published) do NOT trigger Restrict. They should be cancelled before deletion.

### Cascade on Soft Delete

When a Customer is soft-deleted (all validation passes):

1. Customer record: `deleted_at` set to now
2. Portal User account: no automatic action — portal access is controlled by User status, not Customer deletion
3. QR code record: `QRCodeReference` for this Customer is soft-deleted
4. No automatic cascade to Subscriptions, Invoices, or Payments (historical records preserved for audit)

---

## Customer Portal Account Lifecycle

### Portal Account Creation

A portal User account is optional for every Customer. The portal User is linked via the `customers.user_id` nullable FK.

**When a portal account is created:**
- Sales or Customer Service creates a portal User account explicitly
- OR Customer self-registers via a portal onboarding flow (future capability)

**Portal account creation does NOT automatically trigger Customer status change.**

A Customer in Prospect state may have a portal account created before their first Subscription is activated, to allow pre-activation portal communication.

### Portal Account on Customer Suspension

- When Customer transitions to Suspended: portal User account session is invalidated (existing sessions terminated) and portal login is blocked at the application layer, NOT by changing User status
- The portal `User` record is NOT modified to `suspended` status — the block is applied via Customer status check in portal middleware
- Rationale: the portal User may also be used for other purposes; Customer suspension should not permanently alter the User account

### Portal Account on Customer Termination

- When Customer transitions to Terminated: portal User account is set to `UserStatus::Inactive`
- The `user_id` FK on the Customer record is retained for audit but the User account is deactivated
- A terminated Customer's portal account is permanently deactivated

---

## Customer Business Events

The following events are produced by the Customer entity lifecycle. All events follow the Domain Event architecture standard (`docs/architecture/events.md`).

### CustomerRegistered

**Trigger:** Customer record created  
**Producer:** CustomerService::create()  
**Consumers:** Notification Engine, Timeline, Activity Log, Customer Portal  
**Timeline:** Yes — "Customer account created"  
**Notification:** Optional welcome notification based on Settings Engine rule

### CustomerUpdated

**Trigger:** Customer profile modified  
**Producer:** CustomerService::update()  
**Consumers:** Timeline, Activity Log, Notification Engine (if contact fields changed)  
**Timeline:** Yes — "Customer profile updated"  
**Notification:** Conditional — contact channel changes may trigger re-verification

### CustomerConverted

**Trigger:** Customer transitions from Prospect to Active (first Subscription activation)  
**Producer:** CustomerService listener on SubscriptionActivated  
**Consumers:** Notification Engine, Timeline, Activity Log, Reporting  
**Timeline:** Yes — "Customer account activated"  
**Notification:** Activation confirmation notification to Customer

### CustomerSuspended

**Trigger:** Administrator/CS suspends Customer account  
**Producer:** CustomerService::suspend()  
**Consumers:** Notification Engine, Timeline, Activity Log, Customer Portal  
**Timeline:** Yes — "Customer account suspended" (with reason)  
**Notification:** Suspension notification to Customer; internal alert to Customer Service

### CustomerReactivated

**Trigger:** Administrator/CS reactivates suspended Customer account  
**Producer:** CustomerService::reactivate()  
**Consumers:** Notification Engine, Timeline, Activity Log, Customer Portal  
**Timeline:** Yes — "Customer account reactivated"  
**Notification:** Reactivation notification to Customer

### CustomerTerminated

**Trigger:** Administrator terminates Customer account  
**Producer:** CustomerService::terminate()  
**Consumers:** Notification Engine, Timeline, Activity Log, Customer Portal, Billing  
**Timeline:** Yes — "Customer account terminated"  
**Notification:** Termination confirmation to Customer; internal notification to Finance

---

## Customer Notification Responsibilities

Customer notifications use contact fields stored directly on the `customers` table (`email`, `phone`, `whatsapp_phone`). The Notification Engine resolves Customer recipients from these fields directly, independent of the portal User account.

This allows notifications to be delivered even when a Customer has no portal User account (pre-activation Prospects).

**Contact field resolution priority (for Notification Engine):**
1. `customers.whatsapp_phone` — for WhatsApp channel
2. `customers.phone` — for SMS channel
3. `customers.email` — for Email channel

**Portal inbox notifications** require a linked portal User account.

---

## QR Code Generation

A Customer QR code is generated when the Customer transitions from Prospect to Active (i.e., when the first Subscription is activated).

**QR Code Payload:** Customer 360 deep link containing `customer_number`  
**QR Code Purpose:** Collector field lookup — resolves to Customer billing summary and outstanding invoices  
**Relationship:** `Customer 0..1 → 1 QRCodeReference`  
**Generation:** `CustomerService` dispatches `GenerateCustomerQrCodeJob` after `CustomerConverted` event  
**Regeneration:** A new QR code may be generated by an Administrator if the original is lost

---

## References

| Document | Purpose |
|---|---|
| `docs/architecture/decisions.md` | Architecture decisions — Customer Management section |
| `docs/architecture/glossary.md` | Canonical terminology |
| `docs/architecture/business-events.md` | Business events catalog |
| `docs/architecture/events.md` | Domain Event architecture standard |
| `docs/architecture/transactions.md` | Transaction ownership and locking |
| `docs/architecture/exceptions.md` | Exception hierarchy |
| `docs/database/entities.md` | Customer entity specification |
| `docs/database/erd.md` | Customer relationships and cardinalities |
| `docs/workflows/subscription-lifecycle.md` | Subscription lifecycle (distinct from Customer lifecycle) |
| `docs/workflows/notification-workflow.md` | Notification delivery lifecycle |
| `docs/development/definition-of-done.md` | Story completion checklist |

---

**End of Document**
