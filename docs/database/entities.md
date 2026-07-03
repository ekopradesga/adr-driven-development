# Business Entity Inventory

**Version:** 1.0

**Status:** Authoritative — Architecture Version 1.0

Purpose: Complete business entity inventory for the ISP Billing, CRM, Monitoring, and Operations platform. This document is the definitive reference for all entities including classification, ownership, lifecycle, immutability, and platform feature support.

---

# Entity Classification

Entities are classified into the following categories to communicate their architectural role and usage context.

## Core
Primary business entities representing the fundamental domain objects of the ISP. Core entities exist as independent aggregate roots and form the foundation of the business model.

Examples: Customer, Subscription, Package, Employee, Cluster, ServiceArea.

## Transaction
Entities recording completed business transactions, operational activities, or operational state changes. Transaction entities tend toward immutability or append-only semantics once committed.

Examples: Invoice, Payment, PaymentAllocation, CollectionTask, Survey, Installation, ProvisioningRequest, SuspensionCase.

## Infrastructure
Entities representing the physical and logical network topology, device state, and operational infrastructure required to deliver services.

Examples: OLT, ODF, FAT, Dropcore, ONT, ONU, MonitoringEvent, DeviceHealthState, MaintenanceWindow, BillingPeriod.

## Platform
Reusable shared components providing cross-cutting capabilities to all business modules. Platform entities are not owned by a single domain and are governed centrally.

Examples: Notification, NotificationDeliveryAttempt, NotificationTemplate, EventCatalogEntry, Attachment, TimelineEvent, ActivityLog, StateTransitionLog, Setting, SearchIndex.

## Operational
Entities managing work coordination, support activities, and field operations.

Examples: Ticket, TicketComment, TicketAssignment, ServiceRequest, WorkOrder, CollectionTaskInvoice.

## Identity
Entities governing authentication, authorization, and session management.

Examples: User, Role, Permission, UserSession, ImpersonationSession.

---

# Identity & Access

## User

### Purpose
Represents an authenticated identity that can access platform features.

### Owner Module
Identity & Access

### Lifecycle
Draft -> Active -> Suspended -> Disabled.

Deletion behavior: Soft Delete with Restrict when referenced by financial or audit records.

### Relationships
- Role N -> N User
- Permission N -> N User
- User 1 -> N UserSession
- User 1 -> N ActivityLog
- User 1 -> N TimelineEvent
- User 1 -> N NotificationPreference
- User 1 -> N Notification

### Key Attributes
Identity profile, authentication identifiers, account status, access scope.

### Business Rules
Users are authorized through RBAC and optional area-based visibility.

Customer-type users must not access administrative modules.

### Notes
User may represent employee or customer account context.

### Owner
None. User is an Aggregate Root of the Identity domain.

### Aggregate Root
Yes

### Classification
Identity

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
- UserImpersonationStarted (via ImpersonationSession)
- UserImpersonationEnded (via ImpersonationSession)

### Consumes Events
None

## Role

### Purpose
Groups permissions into reusable authorization sets.

### Owner Module
Identity & Access

### Lifecycle
Draft -> Active -> Deprecated.

Deletion behavior: Restrict when assigned to users; Soft Delete otherwise.

### Relationships
- Role N -> N Permission
- Role N -> N User

### Key Attributes
Role name, role scope, role status.

### Business Rules
Roles can grant global visibility or area-limited visibility.

### Notes
Examples include Super Admin, Regional Manager, Collector, NOC Supervisor.

### Owner
None. Shared governance resource.

### Aggregate Root
No

### Classification
Identity

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## Permission

### Purpose
Defines granular system capabilities.

### Owner Module
Identity & Access

### Lifecycle
Draft -> Active -> Deprecated.

Deletion behavior: Restrict when bound to roles or direct assignments.

### Relationships
- Permission N -> N Role
- Permission N -> N User

### Key Attributes
Permission key, permission description, permission category.

### Business Rules
Sensitive capabilities such as impersonation require explicit permission assignment.

### Notes
Permission keys should be stable and human-readable.

### Owner
None. Shared governance resource.

### Aggregate Root
No

### Classification
Identity

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## UserSession

### Purpose
Tracks active and historical login sessions.

### Owner Module
Identity & Access

### Lifecycle
Started -> Active -> Ended -> Expired.

Deletion behavior: Archive after retention policy; no hard delete during retention window.

### Relationships
- User 1 -> N UserSession

### Key Attributes
Session start time, end time, channel, device metadata, IP context.

### Business Rules
Sessions created during impersonation must preserve original actor context.

### Notes
Supports security analysis and user support diagnostics.

### Owner
User

### Aggregate Root
No

### Classification
Identity

### Lifecycle Reference
N/A

### Immutability
Append Only

### Soft Delete
No

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## ImpersonationSession

### Purpose
Represents a temporary support session where one user acts as another.

### Owner Module
Identity & Access

### Lifecycle
Requested -> Active -> Ended.

Deletion behavior: Archive only.

### Relationships
- User 1 -> N ImpersonationSession as Original User
- User 1 -> N ImpersonationSession as Impersonated User
- ImpersonationSession 1 -> N ActivityLog

### Key Attributes
Original user, impersonated user, reason, start time, end time, network metadata.

### Business Rules
Nested impersonation is prohibited.

Super administrators cannot be impersonated.

### Notes
All impersonation actions must be auditable.

### Owner
User (original actor)

### Aggregate Root
No

### Classification
Identity

### Lifecycle Reference
N/A

### Immutability
Append Only

### Soft Delete
Never

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
- UserImpersonationStarted
- UserImpersonationEnded

### Consumes Events
None

# Customer Management

## Customer

### Purpose
Represents a person or organization purchasing ISP services.

### Owner Module
Customer Management

### Lifecycle
Prospect -> Active -> Suspended -> Terminated.

Deletion behavior: Soft Delete with pre-condition validation; Restrict when financial records (published Invoice, Payment, or active PaymentAllocation) exist.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key, auto-increment |
| `customer_number` | VARCHAR(20) | No | Unique external identifier (CUST-000001 format). Immutable after creation. |
| `name` | VARCHAR(255) | No | Full name (individual) or company name (business) |
| `customer_type` | ENUM | No | `individual` (default) or `business`. Passive in v1.0; no differential rules. |
| `email` | VARCHAR(255) | Yes | Primary email address |
| `phone` | VARCHAR(30) | Yes | Primary phone (voice / SMS) |
| `whatsapp_phone` | VARCHAR(30) | Yes | WhatsApp number (may differ from phone) |
| `alt_phone` | VARCHAR(30) | Yes | Secondary / alternative phone |
| `address` | TEXT | Yes | Service installation address |
| `latitude` | DECIMAL(10,7) | Yes | GPS latitude for GIS and field navigation |
| `longitude` | DECIMAL(11,7) | Yes | GPS longitude for GIS and field navigation |
| `notes` | TEXT | Yes | Internal operational notes (not customer-visible) |
| `cluster_id` | BIGINT UNSIGNED | No | FK → clusters.id (RESTRICT) |
| `service_area_id` | BIGINT UNSIGNED | No | FK → service_areas.id (RESTRICT) |
| `user_id` | BIGINT UNSIGNED | Yes | FK → users.id (SET NULL) — portal User account |
| `status` | ENUM | No | `prospect` (default), `active`, `suspended`, `terminated` |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |
| `deleted_at` | TIMESTAMP | Yes | Soft delete |

### Relationships
- Customer 1 -> N Subscription
- Customer 1 -> N Invoice (direct FK for billing context — intentional denormalization for Customer 360)
- Customer 1 -> N Payment (direct FK for payment context — intentional denormalization for Customer 360)
- Customer 1 -> N Ticket
- Customer 1 -> N ServiceRequest
- Customer 1 -> N Attachment (polymorphic)
- Customer 1 -> N TimelineEvent (polymorphic)
- Customer 1 -> N ActivityLog (polymorphic)
- Customer N -> 1 Cluster
- Customer N -> 1 ServiceArea
- Customer 0..1 -> 1 User (optional portal account; nullable FK)
- Customer 0..1 -> 1 QRCodeReference (generated on first Subscription activation)

### Key Attributes
Customer number (external identifier), name, customer type, contact channels (email, phone, WhatsApp), service address with GPS coordinates, operational cluster and service area assignment, lifecycle status.

### Business Rules
1. Customer may own only one active primary internet Subscription at a time (enforced via application-layer `lockForUpdate()` in `ActivateSubscriptionService`).
2. Customer `status` is independent of Subscription `status`. Account-level and service-level statuses are orthogonal.
3. Customer account suspension is an administrative action only — never triggered by billing overdue policy.
4. Customer number is generated on creation and is immutable.
5. Customer termination requires all Subscriptions to be in `terminated` state.
6. Soft delete requires: no published Invoice, no Payment, no active PaymentAllocation, no active Subscription, no open Ticket, no pending ServiceRequest.
7. Customer QR code is generated when Customer first transitions to Active.
8. Notification Engine resolves Customer contact from `customers.email`, `customers.phone`, `customers.whatsapp_phone` directly (not through portal User).

### Notes
Customer entity is account-level; Subscription is service-level. See `customer-workflow.md` for full lifecycle behavior, cascade rules, and business event specifications.

### Owner
None. Customer is an Aggregate Root.

### Aggregate Root
Yes

### Classification
Core

### Lifecycle Reference
docs/workflows/customer-workflow.md

### Immutability
Mutable

### Soft Delete
Yes — with pre-condition validation. See Business Rules above.

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: Yes
- Global Search: Yes (indexed fields: customer_number, name, email, phone)

### Produces Events
- CustomerRegistered
- CustomerUpdated
- CustomerConverted
- CustomerSuspended
- CustomerReactivated
- CustomerTerminated

### Consumes Events
- SubscriptionActivated (triggers Prospect → Active transition)

## Employee

### Purpose
Represents internal operational personnel.

### Owner Module
Customer Management

### Lifecycle
Active -> Inactive -> Archived.

Deletion behavior: Soft Delete with Restrict if linked to audit or financial approvals.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key, auto-increment |
| `user_id` | BIGINT UNSIGNED | Yes | FK → users.id (SET NULL). Links the employee profile to the authenticated user account. |
| `name` | VARCHAR(255) | No | Full employee display name |
| `email` | VARCHAR(255) | Yes | Work email address |
| `phone` | VARCHAR(30) | Yes | Contact phone number |
| `status` | ENUM | No | `active` (default), `inactive`, `archived` |
| `notes` | TEXT | Yes | Internal operational notes |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |
| `deleted_at` | TIMESTAMP | Yes | Soft delete |

### Relationships
- Employee 0..1 -> 1 User
- Employee 1 -> N Ticket
- Employee 1 -> N WorkOrder
- Employee N -> N ServiceArea
- Employee 1 -> N CollectionTask
- Employee 1 -> N UserSession through User
- Employee 1 -> N ActivityLog through User

### Key Attributes
Employee profile, functional role, assignment scope, work status.

### Business Rules
Employee access is role- and area-based.

### Notes
Collector, technician, and NOC are role patterns on employee identities.

### Owner
None. Employee is an Aggregate Root.

### Aggregate Root
Yes

### Classification
Core

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: Yes
- Global Search: No

### Produces Events
None

### Consumes Events
- CollectorAssigned
- TicketAssigned

## Cluster

### Purpose
Operational grouping for workload planning and reporting.

### Owner Module
Customer Management

### Lifecycle
Planned -> Active -> Inactive.

Deletion behavior: Soft Delete when no active dependent entities; Restrict otherwise.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key, auto-increment |
| `name` | VARCHAR(255) | No | Human-readable cluster name |
| `code` | VARCHAR(50) | No | Unique operational code |
| `status` | ENUM | No | `planned` (default), `active`, `inactive` |
| `description` | TEXT | Yes | Optional operational description |
| `notes` | TEXT | Yes | Internal notes |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |
| `deleted_at` | TIMESTAMP | Yes | Soft delete |

### Relationships
- Cluster 1 -> N Customer
- Cluster 1 -> N ServiceArea

### Key Attributes
Cluster name, operational code, reporting classification, lifecycle status.

### Business Rules
Cluster is an operational grouping, not a network cluster.

Active service areas and customer assignments require an active cluster.

### Notes
Cluster boundaries may be represented as polygons for planning.

### Owner
None. Cluster is an Aggregate Root.

### Aggregate Root
Yes

### Classification
Core

### Lifecycle Reference
docs/workflows/service-area-workflow.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
- ClusterCreated
- ClusterActivated
- ClusterInactivated

### Consumes Events
None

## ServiceArea

### Purpose
Geographic operational scope for assignments, visibility, and planning.

### Owner Module
Customer Management

### Lifecycle
Draft -> Active -> Merged -> Archived.

Deletion behavior: Soft Delete or Archive; Restrict if assigned customers exist.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key, auto-increment |
| `cluster_id` | BIGINT UNSIGNED | No | FK → clusters.id (RESTRICT) |
| `parent_id` | BIGINT UNSIGNED | Yes | Self FK → service_areas.id (RESTRICT). Nullable for root areas. |
| `merged_into_service_area_id` | BIGINT UNSIGNED | Yes | Self FK → service_areas.id (RESTRICT). Destination area when source is merged. |
| `name` | VARCHAR(255) | No | Service area display name |
| `code` | VARCHAR(50) | No | Unique operational code |
| `level` | ENUM | No | `region`, `branch`, or `area` |
| `boundary_geojson` | LONGTEXT | Yes | Optional administrative polygon or boundary document |
| `center_latitude` | DECIMAL(10,7) | Yes | Map/navigation reference point |
| `center_longitude` | DECIMAL(11,7) | Yes | Map/navigation reference point |
| `status` | ENUM | No | `draft` (default), `active`, `merged`, `archived` |
| `merged_at` | TIMESTAMP | Yes | When status became `merged` |
| `archived_at` | TIMESTAMP | Yes | When status became `archived` |
| `notes` | TEXT | Yes | Internal notes |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |
| `deleted_at` | TIMESTAMP | Yes | Soft delete |

### Relationships
- ServiceArea 1 -> N Customer
- ServiceArea N -> N Employee
- ServiceArea N -> 1 Cluster
- ServiceArea 0..1 -> N ServiceArea (hierarchy via parent_id)

### Key Attributes
Area name, operational code, hierarchy level, boundary definition, map center, lifecycle status.

### Business Rules
Area assignment determines data visibility and workload assignment.

Draft, merged, and archived service areas must not receive new customer assignments.

Service area hierarchies must remain acyclic.

Merge operations must reassign active customers and employee assignments before the source area becomes terminal.

Employee-to-service-area assignment uses the `employee_service_area` pivot with:

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `employee_id` | BIGINT UNSIGNED | No | FK → employees.id (CASCADE) |
| `service_area_id` | BIGINT UNSIGNED | No | FK → service_areas.id (CASCADE) |
| `is_primary` | BOOLEAN | No | Default `false`; only one active primary assignment per employee |
| `assigned_at` | TIMESTAMP | Yes | Administrative assignment timestamp |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

### Notes
Typical hierarchy: Region -> Branch -> Area.

### Owner
Cluster

### Aggregate Root
Yes

### Classification
Core

### Lifecycle Reference
docs/workflows/service-area-workflow.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
- ServiceAreaCreated
- ServiceAreaActivated
- ServiceAreaMerged
- ServiceAreaArchived

### Consumes Events
None

## Subscription

### Purpose
Represents a customer service contract and lifecycle.

### Owner Module
Customer Management

### Lifecycle
Pending -> Active -> Suspended -> Reactivation Pending -> Terminated.

Deletion behavior: Soft Delete for non-financial contexts; Restrict when invoices or payments exist.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key, auto-increment |
| `customer_id` | BIGINT UNSIGNED | No | FK → customers.id (RESTRICT) |
| `package_id` | BIGINT UNSIGNED | No | FK → packages.id (RESTRICT) |
| `onu_id` | BIGINT UNSIGNED | Yes | FK → onus.id (SET NULL) |
| `status` | ENUM | No | `pending` (default), `active`, `suspended`, `reactivation_pending`, `terminated` |
| `subscription_type` | ENUM | No | `primary` (default), `addon`. Passive in v1.0 for addon type. |
| `suspension_type` | ENUM | Yes | `overdue` or `manual`. NULL when not suspended. |
| `suspension_reason` | TEXT | Yes | Human-readable reason recorded on suspension |
| `suspended_at` | TIMESTAMP | Yes | When status last became suspended |
| `activated_at` | TIMESTAMP | Yes | When status first became active |
| `reactivation_requested_at` | TIMESTAMP | Yes | When reactivation_pending state was entered |
| `terminated_at` | TIMESTAMP | Yes | When status became terminated |
| `terminated_reason` | TEXT | Yes | Human-readable reason for termination |
| `billing_day` | TINYINT UNSIGNED | No | Billing anchor day (1–28). Default 1. |
| `notes` | TEXT | Yes | Internal operational notes |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |
| `deleted_at` | TIMESTAMP | Yes | Soft delete |

### Relationships
- Customer 1 -> N Subscription
- Package 1 -> N Subscription
- Subscription 1 -> N Survey
- Subscription 1 -> N Installation
- Subscription 1 -> N ProvisioningRequest
- Subscription 1 -> N Invoice
- Subscription 1 -> N SuspensionCase
- Subscription 1 -> N ServiceRequest
- Subscription 0..1 -> 1 ONT/ONU assignment (via onu_id FK)
- Subscription 1 -> N TimelineEvent (polymorphic)
- Subscription 1 -> N ActivityLog (polymorphic)
- Subscription 1 -> N Attachment (polymorphic)

### Key Attributes
Subscription type (primary/addon), service status, package reference, activation timestamp, suspension state and type, billing anchor day, ONU assignment.

### Business Rules
1. A Customer may have only one active `primary` subscription at a time. Enforced via `lockForUpdate()` in `ActivateSubscriptionService`.
2. `suspension_type` must be set when transitioning to `suspended` and cleared on transition away from `suspended`.
3. Only `suspension_type = 'overdue'` subscriptions may be automatically reactivated on payment settlement. `manual` suspensions require explicit operator action.
4. Billing starts only when status becomes `active` (first activation).
5. Monitoring starts only when status becomes `active` (first activation).
6. Soft delete requires: no Invoice with status published/overdue/paid; no Payment record.
7. Subscription termination dispatches `SubscriptionTerminated` event consumed by Provisioning (service deprovisioning) and Billing (stop billing cycles).
8. When a Prospect Customer's first primary Subscription is activated, `CustomerService` transitions the Customer to Active and dispatches `CustomerConverted`.

### Notes
Pre-activation phases (Survey, Installation, Provisioning) are tracked through child entity records while Subscription remains in `pending` status. See `docs/workflows/subscription-lifecycle.md`.

### Owner
Customer

### Aggregate Root
Yes

### Classification
Core

### Lifecycle Reference
docs/workflows/subscription-lifecycle.md

### Immutability
Mutable

### Soft Delete
Yes — with pre-condition validation. Restrict when financial records exist.

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: Yes
- Global Search: Yes (indexed: customer_id, status, package_id)

### Produces Events
- SubscriptionCreated
- SubscriptionActivated
- SubscriptionSuspended
- SubscriptionReactivationPending
- SubscriptionReactivated
- SubscriptionTerminated

### Consumes Events
- ProvisioningCompleted (triggers Pending → Active)
- InvoiceOverdue (triggers Active → Suspended with type=overdue)
- PaymentCompleted (triggers Suspended[overdue] → Reactivation Pending)

## Package

### Purpose
Defines commercial service plan characteristics.

### Owner Module
Customer Management

### Lifecycle
Draft -> Active -> Deprecated -> Retired.

Deletion behavior: Soft Delete when not referenced by active subscription; Restrict otherwise.

### Relationships
- Package 1 -> N Subscription

### Key Attributes
Plan name, speed profile, pricing profile, commercial status.

### Business Rules
Package changes do not alter historical invoice snapshots.

### Notes
Used by billing engine through subscription linkage.

### Owner
None. Package is an Aggregate Root.

### Aggregate Root
Yes

### Classification
Core

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## ServiceRequest

### Purpose
Captures customer-initiated change requests for controlled processing.

### Owner Module
Customer Management

### Lifecycle
Submitted -> Reviewed -> Approved/Rejected -> Executed -> Closed.

Deletion behavior: Soft Delete; Archive after closure.

### Relationships
- Customer 1 -> N ServiceRequest
- Subscription 0..1 -> N ServiceRequest
- ServiceRequest 1 -> N WorkOrder
- ServiceRequest 1 -> N Attachment

### Key Attributes
Request type, request reason, approval state, execution status.

### Business Rules
Portal feature requests must be processed as requests, not direct service mutations.

### Notes
Supports upgrade, downgrade, relocation, and termination requests.

### Owner
Customer

### Aggregate Root
No

### Classification
Operational

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: Yes
- Global Search: No

### Produces Events
None

### Consumes Events
None

## WorkOrder

### Purpose
Represents a scheduled field work assignment created from an approved ServiceRequest.

### Owner Module
Customer Management

### Lifecycle
Pending -> Assigned -> In Progress -> Completed -> Cancelled.

Deletion behavior: Soft Delete or Archive after completion.

### Relationships
- ServiceRequest 1 -> N WorkOrder
- Employee 1 -> N WorkOrder

### Key Attributes
Work type, scheduled date, assigned employee, completion status, notes.

### Business Rules
WorkOrder execution depends on an approved ServiceRequest.
WorkOrder does not directly modify subscription state.

### Notes
Used for planned field tasks such as relocation, upgrades, and inspections initiated through ServiceRequest.

### Owner
ServiceRequest

### Aggregate Root
No

### Classification
Operational

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## Survey

### Purpose
Tracks site survey scheduling, execution, and outcome as part of the subscription pre-activation lifecycle.

### Owner Module
Customer Management

### Lifecycle
Scheduled -> Completed -> Cancelled.

Deletion behavior: Soft Delete; Archive after subscription closure.

### Relationships
- Subscription 1 -> N Survey
- Employee N -> 1 Survey
- Survey 1 -> N Attachment
- Survey 1 -> N TimelineEvent

### Key Attributes
Survey date, assigned employee, outcome, findings summary, GPS coordinates.

### Business Rules
Survey completion is a prerequisite for installation scheduling.
Multiple surveys may exist per subscription lifecycle when rescheduled.

### Notes
Supports survey history traceability through the pre-activation journey.

### Owner
Subscription

### Aggregate Root
No

### Classification
Transaction

### Lifecycle Reference
docs/workflows/subscription-lifecycle.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: Yes
- Global Search: No

### Produces Events
- SurveyScheduled
- SurveyCompleted

### Consumes Events
None

## Installation

### Purpose
Tracks installation scheduling, field execution, and completion as part of the subscription pre-activation lifecycle.

### Owner Module
Customer Management

### Lifecycle
Scheduled -> In Progress -> Completed -> Cancelled.

Deletion behavior: Soft Delete; Archive after subscription closure.

### Relationships
- Subscription 1 -> N Installation
- Employee N -> 1 Installation
- Installation 1 -> N Attachment
- Installation 1 -> N TimelineEvent

### Key Attributes
Installation date, assigned technician, current status, completion evidence references, GPS coordinates.

### Business Rules
Installation completion triggers provisioning request creation.
Multiple installations may exist per subscription when rescheduled.

### Notes
Supports installation history across potential rescheduling events.

### Owner
Subscription

### Aggregate Root
No

### Classification
Transaction

### Lifecycle Reference
docs/workflows/subscription-lifecycle.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: Yes
- Global Search: No

### Produces Events
- InstallationStarted
- InstallationCompleted

### Consumes Events
- SurveyCompleted

# Billing

## Invoice

### Purpose
Financial document for subscription charges in a billing period.

### Owner Module
Billing

### Lifecycle
Draft -> Published -> Partially Paid -> Paid -> Overdue -> Cancelled.

Deletion behavior: Restrict and logical immutability. Invoices are NEVER deleted (no soft delete). Corrections use void/reversal workflow.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key, auto-increment |
| `invoice_number` | VARCHAR(25) | No | Unique. Format: INV-YYYYMM-000001. Immutable. |
| `customer_id` | BIGINT UNSIGNED | No | FK → customers.id (RESTRICT) |
| `subscription_id` | BIGINT UNSIGNED | No | FK → subscriptions.id (RESTRICT) |
| `status` | ENUM | No | `draft` (default), `published`, `partially_paid`, `paid`, `overdue`, `cancelled` |
| `period_start` | DATE | No | Start of billing period |
| `period_end` | DATE | No | End of billing period |
| `issue_date` | DATE | No | Date invoice was generated |
| `due_date` | DATE | No | Payment due date |
| `subtotal_amount` | DECIMAL(12,2) | No | Sum of invoice item totals. Default 0. |
| `tax_amount` | DECIMAL(12,2) | No | Tax applied at invoice level. Default 0. |
| `discount_amount` | DECIMAL(12,2) | No | Discount applied at invoice level. Default 0. |
| `total_amount` | DECIMAL(12,2) | No | subtotal + tax - discount. Default 0. |
| `paid_amount` | DECIMAL(12,2) | No | Total allocated from Payments. Default 0. Updated by Payment Workflow only. |
| `balance_amount` | DECIMAL(12,2) | No | total - paid. Default 0. Recomputed on each payment allocation. |
| `published_at` | TIMESTAMP | Yes | When status became published. |
| `overdue_at` | TIMESTAMP | Yes | When status became overdue. |
| `cancelled_at` | TIMESTAMP | Yes | When status became cancelled. |
| `cancellation_reason` | TEXT | Yes | Required when cancelled. |
| `notes` | TEXT | Yes | Internal notes. |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

**No `deleted_at` column.** Invoices are never deleted per immutability rules.

### Relationships
- Subscription 1 -> N Invoice
- Customer 1 -> N Invoice (direct FK — intentional denormalization for Customer 360)
- Invoice 1 -> N InvoiceItem
- Invoice 1 -> N PaymentAllocation
- Invoice 1 -> N TimelineEvent (polymorphic)
- Invoice 1 -> N ActivityLog (polymorphic)
- Invoice 1 -> N Attachment (polymorphic)

### Key Attributes
Invoice number, customer reference, subscription reference, billing period dates, financial snapshot (subtotal, tax, discount, total), payment tracking (paid, balance), lifecycle status.

### Business Rules
1. One invoice per subscription per billing period (enforced by unique index on `(subscription_id, period_start, period_end)`).
2. Draft invoices may be edited (items added/removed) and cancelled.
3. Published invoices are permanently immutable. No modifications except `paid_amount`, `balance_amount`, and `status` (driven by Payment Workflow and billing policy only).
4. Invoice PDF is generated on demand; never stored permanently.
5. `paid_amount` and `balance_amount` are updated exclusively by `InvoiceService::recordPaymentAllocation()` — never by direct user action.
6. Cancellation is only permitted while status is `draft`.
7. `InvoiceService` enforces all immutability rules; throwing `DomainException` for illegal mutations.

### Notes
BillingPeriod entity (ERD relationship `BILLING_PERIOD ||--o{ INVOICE`) is deferred to v1.1 when the automated billing engine is implemented. In v1.0, period context is stored as `period_start`/`period_end` dates directly on the invoice.

### Owner
Subscription

### Aggregate Root
Yes

### Classification
Transaction

### Lifecycle Reference
docs/workflows/billing-workflow.md

### Immutability
Append Only

### Soft Delete
Never — no `deleted_at` column. Invoices are permanent records.

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: Yes
- Global Search: Yes (indexed: invoice_number, customer_id, status)

### Produces Events
- InvoiceGenerated
- InvoicePublished
- InvoiceOverdue
- InvoiceCancelled

### Consumes Events
- SubscriptionActivated
- PaymentCompleted

## InvoiceItem

### Purpose
Charge line details under an invoice.

### Owner Module
Billing

### Lifecycle
Generated -> Finalized.

Deletion behavior: Hard delete only while Invoice is in Draft status. Immutable once Invoice is Published.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key |
| `invoice_id` | BIGINT UNSIGNED | No | FK → invoices.id (RESTRICT) |
| `description` | VARCHAR(255) | No | Line item description (snapshot at creation time) |
| `item_type` | VARCHAR(30) | No | `subscription`, `installation`, `addon`, `discount`, `adjustment`. Default `subscription`. |
| `quantity` | UNSIGNED INT | No | Default 1 |
| `unit_price` | DECIMAL(12,2) | No | Price snapshot at invoice creation time |
| `total_amount` | DECIMAL(12,2) | No | quantity × unit_price |
| `sort_order` | UNSIGNED INT | No | Display ordering. Default 0. |
| `notes` | TEXT | Yes | Internal notes |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

**No `deleted_at` column.** InvoiceItems are hard-deleted only while Invoice is Draft, then immutable.

### Relationships
- Invoice 1 -> N InvoiceItem

### Key Attributes
Line description, item type, quantity, unit price snapshot, line total, display ordering.

### Business Rules
1. Tax and discount are applied at the invoice level, not per line item.
2. `unit_price` is a snapshot at billing time — changes to Package pricing do not affect existing InvoiceItems.
3. InvoiceItems may be added/removed only while parent Invoice is in `draft` status.
4. Once parent Invoice is published, InvoiceItems are permanently immutable.

### Owner
Invoice

### Aggregate Root
No

### Classification
Transaction

### Lifecycle Reference
docs/workflows/billing-workflow.md

### Immutability
Immutable

### Soft Delete
Never — hard delete only while Invoice is Draft.

### Shared Platform Features
- Timeline: No
- Activity Log: No
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## BillingPeriod

### Purpose
Represents cycle window used for recurring invoice generation.

### Owner Module
Billing

### Lifecycle
Open -> Closed -> Locked.

Deletion behavior: Archive.

### Relationships
- BillingPeriod 1 -> N Invoice
- Subscription 1 -> N BillingPeriod references over time

### Key Attributes
Period start, period end, cycle marker, lock status.

### Business Rules
Only one invoice for a subscription in the same billing period.

### Notes
Can be implemented as explicit entity or bounded value object with index constraints.

### Owner
None. Governed by Billing Engine.

### Aggregate Root
No

### Classification
Infrastructure

### Lifecycle Reference
docs/workflows/billing-workflow.md

### Immutability
Mutable

### Soft Delete
No

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## SuspensionCase

### Purpose
Tracks billing-policy suspension decision and execution context.

### Owner Module
Billing

### Lifecycle
Evaluated -> Eligible -> Queued -> Executed -> Reversed/Closed.

Deletion behavior: Archive only.

### Relationships
- Subscription 1 -> N SuspensionCase
- Invoice 1 -> N SuspensionCase triggers
- SuspensionCase 1 -> N TimelineEvent

### Key Attributes
Eligibility reason, overdue age, queue status, execution outcome.

### Business Rules
Suspension eligibility is based on overdue age, not invoice count.

### Notes
Separates policy decision from network provisioning execution.

### Owner
Subscription

### Aggregate Root
No

### Classification
Transaction

### Lifecycle Reference
docs/workflows/subscription-lifecycle.md
docs/workflows/billing-workflow.md

### Immutability
Append Only

### Soft Delete
Never

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None (suspension events are published by Subscription Lifecycle)

### Consumes Events
- InvoiceOverdue

# Payments

## Payment

### Purpose
Represents monetary receipt from customer.

### Owner Module
Payments

### Lifecycle
Intent Created -> Waiting Payment -> Received -> Validated -> Recorded -> Partially Allocated/Fully Allocated -> Completed -> Reversed/Failed.

Deletion behavior: Restrict and logical immutability; correction through reversal.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key, auto-increment |
| `payment_number` | VARCHAR(30) | No | Unique. Format: PAY-YYYYMM-000001. Immutable. |
| `customer_id` | BIGINT UNSIGNED | No | FK -> customers.id (RESTRICT) |
| `status` | ENUM | No | `intent_created`, `waiting_payment`, `received`, `validated`, `recorded`, `partially_allocated`, `fully_allocated`, `completed`, `reversed`, `failed` |
| `payment_date` | DATE | No | Payment transaction date |
| `amount` | DECIMAL(12,2) | No | Gross payment amount |
| `currency` | CHAR(3) | No | Default `IDR` |
| `method` | VARCHAR(30) | No | `cash`, `bank_transfer`, `va`, `qris`, `card`, `other` |
| `channel_reference` | VARCHAR(100) | Yes | External channel reference (VA number, gateway ref, etc.) |
| `received_by` | BIGINT UNSIGNED | Yes | FK -> users.id (NULL ON DELETE) |
| `recorded_at` | TIMESTAMP | Yes | Timestamp when immutable payment record is created |
| `completed_at` | TIMESTAMP | Yes | Timestamp when lifecycle reaches completed |
| `reversed_at` | TIMESTAMP | Yes | Timestamp when reversal is executed |
| `reversal_reason` | TEXT | Yes | Mandatory when reversed |
| `failure_reason` | TEXT | Yes | Failure reason for failed state |
| `notes` | TEXT | Yes | Internal notes |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

**No `deleted_at` column.** Payments are never deleted.

### Relationships
- Customer 1 -> N Payment
- Payment 1 -> N PaymentAllocation
- Payment 1 -> N Attachment
- Payment 1 -> N TimelineEvent

### Key Attributes
Payment number, status, channel, amount, immutable recording context, and correction metadata.

### Business Rules
1. Payment records are immutable after `recorded` state.
2. Corrections use reversal workflow; payment rows are never deleted.
3. One payment may allocate to one or many invoices.
4. Payment amount is always positive.
5. `completed` is reached only after allocation outcome finalization.
6. `reversed` requires reason, actor context, and timestamp.

### Notes
Payment confirmation may trigger reactivation flow.

### Owner
Customer

### Aggregate Root
Yes

### Classification
Transaction

### Lifecycle Reference
docs/workflows/payment-workflow.md

### Immutability
Append Only

### Soft Delete
Never

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: Yes
- Global Search: No

### Produces Events
- PaymentIntentCreated
- PaymentReceived
- PaymentValidated
- PaymentCompleted
- PaymentReallocated
- PaymentReversed
- PaymentFailed

### Consumes Events
- InvoicePublished

## PaymentAllocation

### Purpose
Maps part of payment amount to specific invoice balance.

### Owner Module
Payments

### Lifecycle
Allocated -> Reallocated -> Reversed.

Deletion behavior: Restrict; logical immutability for confirmed allocations.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key, auto-increment |
| `payment_id` | BIGINT UNSIGNED | No | FK -> payments.id (CASCADE) |
| `invoice_id` | BIGINT UNSIGNED | No | FK -> invoices.id (RESTRICT) |
| `allocated_amount` | DECIMAL(12,2) | No | Allocation amount for target invoice |
| `status` | ENUM | No | `allocated`, `reversed` |
| `allocated_at` | TIMESTAMP | No | Allocation timestamp |
| `reversed_at` | TIMESTAMP | Yes | Reversal timestamp |
| `reversal_reason` | TEXT | Yes | Mandatory when reversed |
| `notes` | TEXT | Yes | Internal notes |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

**No `deleted_at` column.** Allocation history is preserved through status transitions, not delete.

### Relationships
- Payment 1 -> N PaymentAllocation
- Invoice 1 -> N PaymentAllocation

### Key Attributes
Allocated amount, allocation status, allocation reason.

### Business Rules
Allocation must not exceed payment remaining amount.

Invoice balance must not become negative.

Reallocation must be auditable and preserve prior allocation history.

Only authorized actors may reverse allocations.

### Notes
PaymentAllocation belongs to Payment and references Invoice.

### Owner
Payment

### Aggregate Root
No

### Classification
Transaction

### Lifecycle Reference
docs/workflows/payment-workflow.md

### Immutability
Append Only

### Soft Delete
Never

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
- PaymentReallocated (when allocation changes under authorization)

### Consumes Events
None

# Provisioning

## ProvisioningRequest

### Purpose
Tracks a provisioning workflow execution context including resource allocation, external system interactions, retry history, and activation outcome.

### Owner Module
Provisioning

### Lifecycle
Queued -> In Progress -> Completed -> Failed -> Rolled Back.

Deletion behavior: Archive after subscription closure.

### Relationships
- Subscription 1 -> N ProvisioningRequest
- ONT 0..1 -> N ProvisioningRequest
- ONU 0..1 -> N ProvisioningRequest
- ProvisioningRequest 1 -> N StateTransitionLog
- ProvisioningRequest 1 -> N TimelineEvent

### Key Attributes
Correlation reference, current workflow state, resource allocation summary, retry count, failure reason, activation outcome, rollback context.

### Business Rules
Only one active provisioning request per subscription at any time.
Provisioning completion is required before subscription activation.
Rollback context must be preserved even after rollback execution.
Every state transition must be logged.

### Notes
Serves as the authoritative record for the provisioning workflow execution context and correlation reference.

### Owner
Subscription

### Aggregate Root
No

### Classification
Transaction

### Lifecycle Reference
docs/workflows/provisioning-workflow.md

### Immutability
Append Only

### Soft Delete
No

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
- ProvisioningStarted
- ProvisioningCompleted

### Consumes Events
- InstallationCompleted

# Collector

## CollectionTask

### Purpose
Tracks a field collection workflow task from assignment through visit completion and payment submission handoff.

### Owner Module
Collector

### Lifecycle
Waiting Assignment -> Assigned -> Scheduled -> On Route -> Customer Visited -> Completed -> Follow Up Required -> Cancelled.

Deletion behavior: Soft Delete; Archive after closure.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key, auto-increment |
| `customer_id` | BIGINT UNSIGNED | No | FK → customers.id (RESTRICT) |
| `employee_id` | BIGINT UNSIGNED | Yes | FK → employees.id (RESTRICT). Assigned collector; NULL while waiting assignment. |
| `status` | ENUM | No | `waiting_assignment`, `assigned`, `scheduled`, `on_route`, `customer_visited`, `completed`, `follow_up_required`, `cancelled` |
| `scheduled_for` | TIMESTAMP | Yes | Planned field visit time |
| `route_started_at` | TIMESTAMP | Yes | When route execution started |
| `visited_at` | TIMESTAMP | Yes | When the customer visit was recorded |
| `completed_at` | TIMESTAMP | Yes | When the task reached a terminal outcome |
| `follow_up_reason` | TEXT | Yes | Required when status becomes `follow_up_required` |
| `cancellation_reason` | TEXT | Yes | Required when status becomes `cancelled` |
| `payment_collected_amount` | DECIMAL(12,2) | Yes | Collection context amount captured during the visit; handoff only |
| `payment_submission_reference` | VARCHAR(100) | Yes | Reference passed to Payment Workflow |
| `payment_submission_status` | ENUM | Yes | `pending`, `submitted`, `acknowledged`, `failed` |
| `notes` | TEXT | Yes | Internal notes |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |
| `deleted_at` | TIMESTAMP | Yes | Soft delete |

### Relationships
- Customer 1 -> N CollectionTask
- Employee N -> 1 CollectionTask
- CollectionTask 1 -> N CollectionTaskInvoice
- CollectionTask 1 -> N Attachment
- CollectionTask 1 -> N TimelineEvent

### Key Attributes
Assignment state, scheduled visit date, visit outcome, payment collected context, follow-up reason, supervisor reference.

### Business Rules
CollectionTask does not own invoices or modify payment records.
Multiple invoices may be associated with one collection task.
Assignment changes must be auditable with reason and actor capture.

### Notes
Payment submission from a collection task is a handoff to Payment Workflow.

### Owner
Customer

### Aggregate Root
Yes

### Classification
Transaction

### Lifecycle Reference
docs/workflows/collector-workflow.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: Yes
- Global Search: No

### Produces Events
- CollectorAssigned
- CollectorVisitStarted
- CollectorVisitCompleted

### Consumes Events
- InvoiceOverdue

## CollectionTaskInvoice

### Purpose
Join entity mapping collection tasks to the invoices they target in a given visit.

### Owner Module
Collector

### Lifecycle
Created -> Resolved -> Cancelled.

Deletion behavior: Soft Delete.

### Columns

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | Primary key, auto-increment |
| `collection_task_id` | BIGINT UNSIGNED | No | FK → collection_tasks.id (CASCADE) |
| `invoice_id` | BIGINT UNSIGNED | No | FK → invoices.id (RESTRICT) |
| `status` | ENUM | No | `created`, `resolved`, `cancelled` |
| `inclusion_reason` | TEXT | Yes | Why the invoice was included in the visit |
| `resolution_outcome` | VARCHAR(100) | Yes | Outcome recorded for this invoice within the task |
| `resolved_at` | TIMESTAMP | Yes | When the invoice outcome was resolved |
| `cancelled_at` | TIMESTAMP | Yes | When the join row was cancelled |
| `notes` | TEXT | Yes | Internal notes |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |
| `deleted_at` | TIMESTAMP | Yes | Soft delete |

### Relationships
- CollectionTask 1 -> N CollectionTaskInvoice
- Invoice 1 -> N CollectionTaskInvoice

### Key Attributes
Collection task reference, invoice reference, inclusion reason, resolution outcome.

### Business Rules
An invoice may appear in multiple collection tasks across separate collection attempts.
Resolution outcome reflects the collection result for that invoice in that specific task.

### Notes
Supports batch collection where one field visit targets multiple outstanding invoices.

### Owner
CollectionTask

### Aggregate Root
No

### Classification
Operational

### Lifecycle Reference
docs/workflows/collector-workflow.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: No
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

# Support

## Ticket

### Purpose
Represents an operational work item or support case managed through a full lifecycle from creation through verification and closure.

### Owner Module
Support

### Lifecycle
Draft -> Open -> Triaged -> Assigned -> Accepted -> On Route -> In Progress -> Waiting Customer -> Waiting Material -> Waiting Third Party -> Resolved -> Verification -> Closed -> Cancelled -> Reopened.

Deletion behavior: Soft Delete; Archive after retention period.

### Relationships
- Customer 1 -> N Ticket
- Ticket 1 -> N TicketAssignment
- Ticket 1 -> N TicketComment
- Ticket 1 -> N Attachment
- Ticket 1 -> N TimelineEvent
- Ticket 1 -> N StateTransitionLog

### Key Attributes
Issue category, ticket type, priority, current status, current assignee reference, resolution summary, SLA context, source channel, reopen count.

### Business Rules
Ticket timeline must be human-readable and severity-aware.
Each ticket has exactly one active assignment at any time.
Assignment history is preserved through TicketAssignment records and is immutable.
SLA targets are determined at triage based on category and priority.

### Notes
Customer portal can create and track tickets.
Current assignee is referenced on Ticket for operational efficiency; full assignment history is preserved in TicketAssignment.

### Owner
Customer

### Aggregate Root
Yes

### Classification
Operational

### Lifecycle Reference
docs/workflows/ticket-workflow.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: Yes
- Global Search: Yes

### Produces Events
- TicketCreated
- TicketAssigned
- TicketResolved
- TicketClosed

### Consumes Events
- DeviceWarningRaised
- DeviceCriticalRaised

## TicketComment

### Purpose
Stores conversation and updates for ticket workflow.

### Owner Module
Support

### Lifecycle
Created -> Edited -> Hidden (moderation).

Deletion behavior: Soft Delete with audit trace.

### Relationships
- Ticket 1 -> N TicketComment
- User 1 -> N TicketComment

### Key Attributes
Comment text, author context, visibility scope, timestamp.

### Business Rules
Comments must respect customer/staff visibility boundaries.

### Notes
Supports collaborative resolution history.

### Owner
Ticket

### Aggregate Root
No

### Classification
Operational

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## TicketAssignment

### Purpose
Records individual assignment events for tickets, preserving full immutable assignment history.

### Owner Module
Support

### Lifecycle
Active -> Superseded -> Revoked.

Deletion behavior: Archive only; assignment history must never be deleted.

### Relationships
- Ticket 1 -> N TicketAssignment
- Employee 1 -> N TicketAssignment as assignee
- User 1 -> N TicketAssignment as assigning actor

### Key Attributes
Assignee reference, assigned by reference, assignment method, assignment reason, assigned at timestamp, superseded at timestamp.

### Business Rules
Only one TicketAssignment per ticket may be Active at any time.
Reassignment supersedes the prior record and creates a new active record.
Assignment history is immutable once created.

### Notes
Separates the current assignee reference on Ticket from the full auditable assignment trail.

### Owner
Ticket

### Aggregate Root
No

### Classification
Operational

### Lifecycle Reference
docs/workflows/ticket-workflow.md

### Immutability
Append Only

### Soft Delete
Never

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
- TicketAssigned

### Consumes Events
None

# Monitoring & Network

## OLT

### Purpose
Core access network head-end entity for optical service delivery.

### Owner Module
Monitoring & Network

### Lifecycle
Planned -> Active -> Degraded -> Maintenance -> Retired.

Deletion behavior: Soft Delete when detached; Restrict with active topology dependencies.

### Relationships
- OLT 1 -> N ODF
- OLT 1 -> N FAT through topology
- OLT 1 -> N ONT/ONU logical path
- OLT 1 -> N MonitoringEvent

### Key Attributes
Vendor identity, operational status, topology references, location context.

### Business Rules
Participates in logical and physical topology models.

### Notes
Operational health should be abstracted for end users.

### Owner
None. OLT is an Aggregate Root of the network topology.

### Aggregate Root
Yes

### Classification
Infrastructure

### Lifecycle Reference
docs/workflows/network-monitoring-workflow.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: Yes
- Global Search: Yes

### Produces Events
None (health events are produced by DeviceHealthState)

### Consumes Events
None

## ODF

### Purpose
Physical fiber distribution frame in physical topology.

### Owner Module
Monitoring & Network

### Lifecycle
Installed -> Active -> Retired.

Deletion behavior: Restrict when downstream assets exist.

### Relationships
- OLT 1 -> N ODF
- ODF 1 -> N FAT

### Key Attributes
Frame identity, segment mapping, physical route context.

### Business Rules
Used in physical topology only, not simplified logical topology.

### Notes
Essential for field documentation and maintenance routing.

### Owner
OLT

### Aggregate Root
No

### Classification
Infrastructure

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## FAT

### Purpose
Distribution terminal connecting feeder network to last-mile drop.

### Owner Module
Monitoring & Network

### Lifecycle
Planned -> Active -> Saturated -> Retired.

Deletion behavior: Restrict when ONT/Dropcore still attached.

### Relationships
- ODF 1 -> N FAT
- FAT 1 -> N Dropcore
- FAT 1 -> N ONT
- FAT 1 -> N MonitoringEvent

### Key Attributes
Location, capacity context, service area linkage.

### Business Rules
FAT may be used in operational area hierarchy references.

### Notes
Can act as planning anchor for field operations.

### Owner
ODF

### Aggregate Root
No

### Classification
Infrastructure

### Lifecycle Reference
docs/workflows/network-monitoring-workflow.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: Yes
- Global Search: Yes

### Produces Events
None

### Consumes Events
None

## Dropcore

### Purpose
Represents physical last-mile drop segment.

### Owner Module
Monitoring & Network

### Lifecycle
Provisioned -> Active -> Damaged -> Replaced -> Retired.

Deletion behavior: Archive after replacement history closure.

### Relationships
- FAT 1 -> N Dropcore
- Dropcore 1 -> 1 ONT

### Key Attributes
Route segment identity, status, endpoint references.

### Business Rules
Dropcore exists in physical topology path.

### Notes
Used heavily for technician workflows.

### Owner
FAT

### Aggregate Root
No

### Classification
Infrastructure

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
No

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## ONT

### Purpose
Customer-side optical endpoint for service delivery.

### Owner Module
Monitoring & Network

### Lifecycle
Unprovisioned -> Provisioned -> Active -> Offline -> Retired.

Deletion behavior: Soft Delete when detached from active subscription; Restrict with active links.

### Relationships
- FAT 1 -> N ONT
- Customer 1 -> N ONT over history
- Subscription 0..1 -> 1 active ONT
- ONT 1 -> N MonitoringEvent

### Key Attributes
Endpoint identity, signal profile, activation status.

### Business Rules
ONT and ONU terminology may overlap by vendor context.

### Notes
For this platform, ONT is customer endpoint in topology view.

### Owner
FAT

### Aggregate Root
No

### Classification
Infrastructure

### Lifecycle Reference
docs/workflows/provisioning-workflow.md
docs/workflows/network-monitoring-workflow.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: Yes
- Global Search: No

### Produces Events
None

### Consumes Events
None

## ONU

### Purpose
Operational endpoint term for customer optical unit used in monitoring and provisioning contexts.

### Owner Module
Monitoring & Network

### Lifecycle
Unprovisioned -> Active -> Offline -> Suspended -> Retired.

Deletion behavior: Soft Delete with Restrict on active monitoring references.

### Relationships
- OLT 1 -> N ONU
- ONU 1 -> N MonitoringEvent
- Subscription 0..1 -> 1 active ONU

### Key Attributes
Device identity, operational status, health metrics.

### Business Rules
ONU monitoring should expose simplified health status to users.

### Notes
Include mapping rules when ONT and ONU are represented separately.

### Owner
OLT

### Aggregate Root
No

### Classification
Infrastructure

### Lifecycle Reference
docs/workflows/provisioning-workflow.md
docs/workflows/network-monitoring-workflow.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: Yes
- Global Search: Yes

### Produces Events
None

### Consumes Events
None

## MonitoringEvent

### Purpose
Represents observed network health or status event.

### Owner Module
Monitoring & Network

### Lifecycle
Detected -> Correlated -> Acknowledged -> Resolved.

Deletion behavior: Archive only.

### Relationships
- OLT 1 -> N MonitoringEvent
- FAT 1 -> N MonitoringEvent
- ONT 1 -> N MonitoringEvent
- ONU 1 -> N MonitoringEvent
- MonitoringEvent 1 -> N TimelineEvent

### Key Attributes
Severity, event type, source asset, detection time, resolution status.

### Business Rules
User-facing health should map to Healthy/Warning/Critical/Offline abstraction.

### Notes
Core event source for NOC operations.
MonitoringEvents publish into the business event system through the EventCatalogEntry mechanism. Notifications are triggered by the Notification Engine, not directly by MonitoringEvent records.

### Owner
Source asset (OLT, FAT, ONT, or ONU) — polymorphic reference.

### Aggregate Root
No

### Classification
Infrastructure

### Lifecycle Reference
docs/workflows/network-monitoring-workflow.md

### Immutability
Immutable

### Soft Delete
Never

### Shared Platform Features
- Timeline: Yes
- Activity Log: No
- Attachment: No
- Global Search: No

### Produces Events
- DeviceWarningRaised
- DeviceCriticalRaised
- DeviceRecovered

### Consumes Events
None

## MaintenanceWindow

### Purpose
Tracks planned maintenance periods for network assets during which health signal interpretation follows maintenance context rules.

### Owner Module
Monitoring & Network

### Lifecycle
Scheduled -> Active -> Completed -> Cancelled.

Deletion behavior: Archive after completion.

### Relationships
- MaintenanceWindow 1 -> N TimelineEvent
- MaintenanceWindow scope references one or more monitored assets (OLT, ODF, FAT, ONT, ONU)

### Key Attributes
Scheduled start time, scheduled end time, actual start time, actual end time, scope description, authorizing actor reference.

### Business Rules
Active maintenance windows suppress non-actionable alert noise according to configured policy.
Maintenance scope must be explicitly defined before activation.
Maintenance does not modify business records.

### Notes
Maintenance scope may reference individual devices, FAT segments, OLT ranges, or service areas.

### Owner
None. MaintenanceWindow is an Aggregate Root.

### Aggregate Root
Yes

### Classification
Infrastructure

### Lifecycle Reference
docs/workflows/network-monitoring-workflow.md

### Immutability
Mutable

### Soft Delete
No

### Shared Platform Features
- Timeline: Yes
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
- MaintenanceScheduled
- MaintenanceStarted
- MaintenanceCompleted

### Consumes Events
None

## DeviceHealthState

### Purpose
Stores the current health state snapshot for each monitored network asset, supporting efficient health views without full MonitoringEvent history traversal.

### Owner Module
Monitoring & Network

### Lifecycle
Updated continuously. States: Unknown -> Healthy -> Warning -> Critical -> Maintenance -> Archived.

Deletion behavior: Soft Delete when the associated device is archived.

### Relationships
- OLT 1 -> 1 DeviceHealthState
- FAT 1 -> 1 DeviceHealthState
- ONT 1 -> 1 DeviceHealthState
- ONU 1 -> 1 DeviceHealthState

### Key Attributes
Current health status, health score, last observation time, active maintenance flag, correlation context reference.

### Business Rules
DeviceHealthState is derived from the MonitoringEvent stream according to configured health thresholds.
Historical health state is traceable through MonitoringEvent records.
Parent device health may classify child devices as Unknown when the parent is offline.

### Notes
Supports efficient current-state dashboards and topology health views without scanning the full MonitoringEvent history.

### Owner
Source device (OLT, FAT, ONT, or ONU) — one-to-one.

### Aggregate Root
No

### Classification
Infrastructure

### Lifecycle Reference
docs/workflows/network-monitoring-workflow.md

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: No
- Attachment: No
- Global Search: No

### Produces Events
- DeviceWarningRaised
- DeviceCriticalRaised
- DeviceRecovered

### Consumes Events
None

# Notifications

## Notification

### Purpose
Represents a delivery instance generated from business or monitoring events.

### Owner Module
Notifications

### Lifecycle
Queued -> Sent -> Delivered/Failed -> Retried -> Finalized.

Deletion behavior: Archive after retention period.

### Relationships
- EventCatalogEntry 1 -> N Notification
- User 1 -> N Notification
- NotificationTemplate 1 -> N Notification
- Notification 1 -> N NotificationDeliveryAttempt

### Key Attributes
Recipient, channel, current status, delivery attempt count, provider response.

### Business Rules
Notification processing is asynchronous and must not block core transactions.

### Notes
Supports multi-channel delivery.

### Owner
EventCatalogEntry (trigger source)

### Aggregate Root
Yes

### Classification
Platform

### Lifecycle Reference
docs/workflows/notification-workflow.md

### Immutability
Append Only

### Soft Delete
No

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
- NotificationScheduled
- NotificationDelivered
- NotificationFailed

### Consumes Events
All domain business events routed through EventCatalogEntry

## NotificationDeliveryAttempt

### Purpose
Records individual delivery attempts for a notification, enabling per-attempt retry and fallback channel traceability.

### Owner Module
Notifications

### Lifecycle
Initiated -> Succeeded -> Failed.

Deletion behavior: Archive after retention period.

### Relationships
- Notification 1 -> N NotificationDeliveryAttempt

### Key Attributes
Attempt sequence number, channel, provider reference, attempt status, failure reason, attempted at timestamp.

### Business Rules
Each retry or fallback channel change creates a new attempt record.
Attempt records are immutable once the attempt result is captured.
Failure reason must be preserved for audit and retry analysis.

### Notes
Enables delivery analytics including retry rate, channel performance, and fallback frequency reporting.

### Owner
Notification

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
docs/workflows/notification-workflow.md

### Immutability
Immutable

### Soft Delete
Never

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
- NotificationDelivered
- NotificationFailed

### Consumes Events
None

## NotificationTemplate

### Purpose
Stores reusable message templates with variables.

### Owner Module
Notifications

### Lifecycle
Draft -> Active -> Deprecated.

Deletion behavior: Soft Delete with Restrict if referenced by active rules.

### Relationships
- NotificationTemplate 1 -> N Notification
- EventCatalogEntry N -> N NotificationTemplate

### Key Attributes
Template code, channel type, variable set, active status.

### Business Rules
Template changes should not break registered variable contracts.

### Notes
Centralized for consistency and easier content management.

### Owner
None. Shared platform resource.

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## NotificationPreference

### Purpose
Stores per-user channel preferences by event type.

### Owner Module
Notifications

### Lifecycle
Created -> Updated -> Disabled.

Deletion behavior: Soft Delete.

### Relationships
- User 1 -> N NotificationPreference
- EventCatalogEntry 1 -> N NotificationPreference

### Key Attributes
Recipient preference scope, channel enablement, event mapping.

### Business Rules
Preferences are evaluated before send decision.

### Notes
Improves user control and reduces notification fatigue.

### Owner
User

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## EventCatalogEntry

### Purpose
Defines allowed notification-triggering event types.

### Owner Module
Notifications

### Lifecycle
Registered -> Active -> Deprecated.

Deletion behavior: Restrict when referenced by preferences or templates.

### Relationships
- EventCatalogEntry 1 -> N Notification
- EventCatalogEntry 1 -> N NotificationPreference
- EventCatalogEntry N -> N NotificationTemplate

### Key Attributes
Event code, domain source, default behavior, active flag.

### Business Rules
Ad-hoc event codes are prohibited.

### Notes
Acts as governance entity for notification consistency.

### Owner
None. Shared governance resource.

### Aggregate Root
Yes

### Classification
Platform

### Lifecycle Reference
docs/architecture/business-events.md

### Immutability
Mutable

### Soft Delete
No

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

# Attachments

## Attachment

### Purpose
Stores metadata for files attached to business entities.

### Owner Module
Attachments

### Lifecycle
Uploaded -> Processed -> Active -> Soft Deleted -> Purged.

Deletion behavior: Soft Delete first, then delayed physical purge.

### Relationships
- Customer 1 -> N Attachment
- Employee 1 -> N Attachment
- Subscription 1 -> N Attachment
- Invoice 1 -> N Attachment
- Payment 1 -> N Attachment
- Ticket 1 -> N Attachment
- OLT 1 -> N Attachment
- FAT 1 -> N Attachment
- ONT/ONU 1 -> N Attachment
- AttachmentCategory 1 -> N Attachment

### Key Attributes
Owner entity reference, file classification, storage reference, preview capability.

### Business Rules
Attachment access inherits owning entity permissions.

### Notes
File content is stored outside database; metadata stays in database.

### Owner
Target entity (polymorphic) — Customer, Invoice, Payment, Ticket, OLT, FAT, ONT/ONU, Survey, Installation, or CollectionTask.

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Mutable (metadata); file content is immutable once stored.

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: N/A
- Global Search: No

### Produces Events
- AttachmentAdded
- AttachmentRemoved

### Consumes Events
None

## AttachmentCategory

### Purpose
Classifies attachment purpose for business context.

### Owner Module
Attachments

### Lifecycle
Active -> Deprecated.

Deletion behavior: Restrict when referenced.

### Relationships
- AttachmentCategory 1 -> N Attachment

### Key Attributes
Category name, category scope, visibility rule.

### Business Rules
Standard categories should be shared across modules, with extension allowed.

### Notes
Examples include Contract, Identity, Proof of Payment, Installation.

### Owner
None. Shared reference data.

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: No
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

# Audit & Timeline

## ActivityLog

### Purpose
Central accountability ledger for security, compliance, and forensic tracing.

### Owner Module
Audit & Timeline

### Lifecycle
Recorded -> Retained -> Archived.

Deletion behavior: Archive only under retention policy.

### Relationships
- User 1 -> N ActivityLog
- ActivityLog N -> 1 related entity polymorphic reference

### Key Attributes
Actor, action, entity reference, before/after snapshot, network metadata, timestamp.

### Business Rules
Critical actions such as permission change and impersonation must be logged.

### Notes
More technical than timeline narrative.

### Owner
None. Platform-wide accountability ledger.

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Append Only

### Soft Delete
Never

### Shared Platform Features
- Timeline: No
- Activity Log: N/A
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
All significant platform actions across every module

## TimelineEvent

### Purpose
Business-readable event entries attached to entities.

### Owner Module
Audit & Timeline

### Lifecycle
Created -> Visible -> Archived.

Deletion behavior: Soft Delete with audit trace, or Archive.

### Relationships
- Customer 1 -> N TimelineEvent
- Subscription 1 -> N TimelineEvent
- Invoice 1 -> N TimelineEvent
- Payment 1 -> N TimelineEvent
- Ticket 1 -> N TimelineEvent
- ONT/ONU 1 -> N TimelineEvent
- User 1 -> N TimelineEvent as actor

### Key Attributes
Event text, severity, source event code, actor context, timestamp.

### Business Rules
Timeline entries must be human-readable and severity-classified.

### Notes
Timeline is not the same as ActivityLog.

### Owner
Target entity (polymorphic) — Customer, Subscription, Invoice, Payment, Ticket, ONT/ONU, ProvisioningRequest, or CollectionTask.

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Append Only

### Soft Delete
Never

### Shared Platform Features
- Timeline: N/A
- Activity Log: No
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
Business events that generate human-readable timeline entries

## StateTransitionLog

### Purpose
Stores explicit state changes for lifecycle-managed entities.

### Owner Module
Audit & Timeline

### Lifecycle
Recorded -> Retained -> Archived.

Deletion behavior: Archive only.

### Relationships
- StateTransitionLog N -> 1 related lifecycle entity polymorphic reference
- User 1 -> N StateTransitionLog

### Key Attributes
Entity type, entity reference, previous state, new state, reason, timestamp.

### Business Rules
Every controlled lifecycle transition must be logged.

### Notes
Supports auditability and workflow diagnostics.

### Owner
Target lifecycle entity (polymorphic).

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Append Only

### Soft Delete
Never

### Shared Platform Features
- Timeline: No
- Activity Log: No
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
All lifecycle state transitions across workflow-managed entities

# Settings

## Setting

### Purpose
Stores configurable operational and business values.

### Owner Module
Settings

### Lifecycle
Registered -> Active -> Updated -> Deprecated.

Deletion behavior: Soft Delete for non-critical keys; Archive for governance history.

### Relationships
- SettingCategory 1 -> N Setting
- SettingRegistryEntry 1 -> 1 Setting definition mapping

### Key Attributes
Setting key, typed value, scope level, effective status.

### Business Rules
Operational values must not be hardcoded.

### Notes
Supports global, area/cluster, and customer scopes.

### Owner
SettingCategory

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## SettingCategory

### Purpose
Groups settings for governance and administration.

### Owner Module
Settings

### Lifecycle
Active -> Deprecated.

Deletion behavior: Restrict when category has active settings.

### Relationships
- SettingCategory 1 -> N Setting

### Key Attributes
Category code, category label, management visibility.

### Business Rules
Categories should align with operational domains (billing, notification, monitoring, security).

### Notes
Improves discoverability of configuration.

### Owner
None. Shared governance resource.

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: No
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## SettingRegistryEntry

### Purpose
Defines approved settings schema and constraints.

### Owner Module
Settings

### Lifecycle
Registered -> Active -> Deprecated.

Deletion behavior: Restrict when in-use settings exist.

### Relationships
- SettingRegistryEntry 1 -> 1..N Setting instances by scope

### Key Attributes
Key, data type, default value, validation rules, visibility.

### Business Rules
Only registered settings are allowed.

### Notes
Prevents configuration sprawl.

### Owner
None. Shared governance resource.

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
No

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## FeatureFlag

### Purpose
Controls runtime enablement of features without deployment.

### Owner Module
Settings

### Lifecycle
Defined -> Enabled/Disabled -> Retired.

Deletion behavior: Archive after feature retirement.

### Relationships
- FeatureFlag N -> 1 SettingRegistryEntry (or Setting key linkage)

### Key Attributes
Flag key, rollout scope, default state, override scope.

### Business Rules
Feature rollout and rollback should be controlled operationally.

### Notes
Used for gradual delivery and safer release management.

### Owner
SettingRegistryEntry

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
No

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

# Search

## SearchIndex

### Purpose
Stores indexed representations of entities for fast global search.

### Owner Module
Search

### Lifecycle
Indexed -> Updated -> Rebuilt -> Archived.

Deletion behavior: Cascade or rebuild-driven cleanup from source entity changes.

### Relationships
- SearchIndex N -> 1 source entity polymorphic reference
- SearchIndex 1 -> N SearchRankEntry conceptual scoring records

### Key Attributes
Searchable tokens, entity reference, relevance metadata, index timestamp.

### Business Rules
Search should support exact, prefix, partial, and fuzzy ranking.

### Notes
Built asynchronously to reduce transactional load.

### Owner
Source entity (polymorphic).

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
No

### Shared Platform Features
- Timeline: No
- Activity Log: No
- Attachment: No
- Global Search: N/A

### Produces Events
None

### Consumes Events
CustomerRegistered, CustomerUpdated, InvoicePublished, TicketCreated, and all entity create or update events that affect indexed attributes

## SearchHistory

### Purpose
Tracks user search behavior for quick recall and productivity.

### Owner Module
Search

### Lifecycle
Recorded -> Recent -> Expired -> Archived.

Deletion behavior: Soft Delete or TTL archive.

### Relationships
- User 1 -> N SearchHistory

### Key Attributes
Search term, context, timestamp, result interaction signal.

### Business Rules
History is user-scoped and should respect privacy/security policy.

### Notes
Improves repeated operational lookups.

### Owner
User

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Append Only

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: No
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

## QRCodeReference

### Purpose
Maps scannable QR identifiers to target entity references.

### Owner Module
Search

### Lifecycle
Generated -> Active -> Rotated -> Revoked.

Deletion behavior: Soft Delete with optional rotation archive.

### Relationships
- QRCodeReference N -> 1 target entity polymorphic reference

### Key Attributes
QR token, target entity type, target entity reference, status.

### Business Rules
Customer QR must resolve to customer context and billing summary workflow.

### Notes
Enables universal QR search experience.

### Owner
Target entity (polymorphic).

### Aggregate Root
No

### Classification
Platform

### Lifecycle Reference
N/A

### Immutability
Mutable

### Soft Delete
Yes

### Shared Platform Features
- Timeline: No
- Activity Log: Yes
- Attachment: No
- Global Search: No

### Produces Events
None

### Consumes Events
None

# Value Objects

Value Objects represent reusable concepts defined entirely by their attributes rather than by an identity key. They are documented here as typed structures to encourage consistent representation across entities.

## Money
A typed monetary value with explicit currency context.

Fields: amount (decimal), currency code (ISO 4217).

Used by: Invoice, InvoiceItem, Payment, PaymentAllocation.

Business Rules: Currency is configurable through Settings Engine. All financial values must carry explicit currency context.

## Address
A structured physical location representation.

Fields: street, district or subdistrict, city, province, postal code, country.

Used by: Customer (service location), ServiceArea (boundary reference), Survey, Installation, FAT.

## Coordinate
A geographic point defined by latitude and longitude.

Fields: latitude, longitude, accuracy (optional), captured at (timestamp, optional).

Used by: Customer, Survey, Installation, CollectionTask, FAT.

Notes: Used for GPS verification during field visits and asset placement records.

## ContactProfile
A grouped representation of customer contact channels.

Fields: primary phone, secondary phone (optional), email address (optional), preferred contact method.

Used by: Customer.

## TimeRange
A bounded time period with a start and optional end timestamp.

Fields: started at (timestamp), ended at (timestamp, nullable).

Used by: MaintenanceWindow, BillingPeriod.

## SignalProfile
A snapshot of optical signal characteristics for a customer endpoint.

Fields: RX power (dBm), TX power (dBm), OLT RX power (dBm, optional), optical distance (optional), last measured at (timestamp).

Used by: ONT, ONU.

Notes: Used for monitoring quality verification and installation validation.

# Cross-Entity Cardinality Reference

- Customer 1 -> N Invoice
- Invoice 1 -> N InvoiceItem
- Payment 1 -> N PaymentAllocation
- Customer 1 -> 1 Active Subscription
- Subscription 1 -> N Invoice
- Invoice 1 -> N PaymentAllocation
- Cluster 1 -> N Customer
- ServiceArea N -> N Employee
- FAT 1 -> N ONT
- OLT 1 -> N ONU
- Subscription 1 -> N Survey
- Subscription 1 -> N Installation
- Subscription 1 -> N ProvisioningRequest
- Customer 1 -> N CollectionTask
- CollectionTask N -> N Invoice (through CollectionTaskInvoice)
- Ticket 1 -> N TicketAssignment
- Ticket 1 -> N TicketComment
- Notification 1 -> N NotificationDeliveryAttempt
- OLT 1 -> 1 DeviceHealthState
- FAT 1 -> 1 DeviceHealthState
- ONT 1 -> 1 DeviceHealthState
- ONU 1 -> 1 DeviceHealthState
- User 1 -> N ActivityLog
- User 1 -> N UserSession
