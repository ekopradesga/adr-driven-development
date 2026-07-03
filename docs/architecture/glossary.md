# ISP Platform Glossary

This glossary centralizes business and technical terminology for the ISP Billing, Monitoring, CRM, and Operations platform.

# Business

## Customer

### Definition
A person or organization purchasing internet services from the ISP.

### Notes
A customer may have only one active primary internet subscription at a time.

A customer can still have additional add-on services attached to the primary subscription.

### Related Terms
- Subscription
- Package
- Invoice
- Customer Portal

## Prospect

### Definition
The canonical initial lifecycle state of a Customer account. A Prospect is a Customer record that has been created but does not yet have an active Subscription.

### Notes
Prospect is the authoritative term for the initial Customer status. The term **Lead** is retired and must not appear in code, migrations, enums, or documentation.

A Prospect transitions to Active automatically when their first Subscription is activated.

### Related Terms
- Customer
- CustomerStatus
- Subscription
- CustomerConverted

## Customer Number

### Definition
A unique, human-readable external identifier assigned to every Customer at creation time.

### Notes
Format: `CUST-{zero-padded 6-digit sequential number}` (e.g., `CUST-000001`, `CUST-001234`).

Immutable after creation. Used in QR codes, invoice headers, Collector visit records, portal references, and Global Search.

### Related Terms
- Customer
- QR Code
- Global Search

## Employee

### Definition
An internal user operating or managing business processes in the platform.

### Notes
Employees may be assigned to one or more service areas and have role-based permissions.

### Related Terms
- User
- Role
- Permission
- Service Area

## Collector

### Definition
An employee role responsible for payment collection and field-facing billing operations.

### Notes
Collector workflows are mobile-first and commonly use customer QR lookup.

### Related Terms
- Payment
- Customer-Based QR
- Service Area
- Cluster

## CollectionTask

### Definition
A field collection assignment owned by a customer and executed by a collector.

### Notes
Collection tasks track assignment, scheduling, visit execution, follow-up, and payment handoff context without owning invoices or payments.

### Related Terms
- Collector
- Customer
- CollectionTaskInvoice
- Payment

## CollectionTaskInvoice

### Definition
A join record linking a collection task to a targeted invoice for a specific visit attempt.

### Notes
The same invoice may appear in multiple collection tasks over time.

### Related Terms
- CollectionTask
- Invoice
- Collector

## Technician

### Definition
An employee role responsible for installation, field maintenance, and service restoration tasks.

### Notes
Technicians primarily interact with operational and network-related workflows.

### Related Terms
- Installation
- Work Order
- ONT
- FAT

## NOC

### Definition
Network Operations Center role or function responsible for monitoring network health and incidents.

### Notes
NOC users may have broader area visibility depending on role permissions.

### Related Terms
- Monitoring
- Signal Health
- OLT
- ONU

## Package

### Definition
A commercial internet service plan assigned to subscriptions.

### Notes
Package price and profile can change over time, but financial snapshots in invoices remain immutable.

### Related Terms
- Subscription
- Invoice Item
- Billing Period

## Package Status

### Definition
The lifecycle state of a `Package` commercial plan. Expressed as the `packages.status` column and the `PackageStatus` PHP backed enum.

### Notes
The four canonical states are:
- **Draft** (`draft`) - prepared but not assignable. Default.
- **Active** (`active`) - assignable to new subscriptions.
- **Deprecated** (`deprecated`) - not assignable for new subscriptions; existing references remain valid.
- **Retired** (`retired`) - terminal historical state; not assignable.

Package status controls assignment eligibility but does not mutate historical invoice snapshots.

### Related Terms
- Package
- Subscription
- Invoice Item

## Subscription

### Definition
A service contract that links a customer to a package and billing lifecycle.

### Notes
A subscription is not the same as a customer.

One customer owns account identity, while subscription represents a specific service lifecycle.

### Related Terms
- Customer
- Package
- Billing Period
- Suspension

## Subscription Status

### Definition
The lifecycle state of a Subscription. Expressed as the `subscriptions.status` column and the `SubscriptionStatus` PHP backed enum.

### Notes
The five canonical states are:
- **Pending** (`pending`) — pre-activation; covers survey, installation, and provisioning phases. Default.
- **Active** (`active`) — service live; billing and monitoring running.
- **Suspended** (`suspended`) — service restricted; see `suspension_type` for sub-type.
- **Reactivation Pending** (`reactivation_pending`) — payment confirmed; awaiting service restoration.
- **Terminated** (`terminated`) — permanently closed; terminal state.

Pre-activation sub-phases are tracked through child entity records (Survey, Installation, ProvisioningRequest) while the Subscription remains in `pending` status.

### Related Terms
- Subscription
- Subscription Suspension
- SubscriptionStatus (enum)

## Subscription Suspension

### Definition
Service restriction applied to a Subscription either automatically by billing overdue policy or manually by an authorized operator. Governed by `docs/workflows/subscription-lifecycle.md`.

### Notes
Two suspension sub-types exist, tracked in the `subscriptions.suspension_type` column:
- **Overdue** (`overdue`) — triggered automatically when an invoice is 7+ days overdue. Eligible for automatic reactivation when the overdue balance is settled.
- **Manual** (`manual`) — triggered by an authorized operator for operational reasons. Requires explicit operator action to reactivate, regardless of payment state.

Distinguish from **Customer Suspension**, which is an administrative account action unrelated to billing policy.

### Related Terms
- Subscription Status
- Customer Suspension
- SubscriptionSuspended (event)

## Invoice Status

### Definition
The lifecycle state of an Invoice. Expressed as the `invoices.status` column and the `InvoiceStatus` PHP backed enum.

### Notes
The six canonical states are:
- **Draft** (`draft`) — generated; editable; not yet payable; not customer-visible. Default.
- **Published** (`published`) — frozen; payable; customer-visible; permanently immutable.
- **Partially Paid** (`partially_paid`) — payment received but balance remains.
- **Paid** (`paid`) — fully settled; balance is zero.
- **Overdue** (`overdue`) — past due date and grace period without full settlement.
- **Cancelled** (`cancelled`) — cancelled before publication; terminal state.

Invoices are **never deleted**. The `invoices` table has no `deleted_at` column. Corrections to published invoices require a void/reversal workflow.

### Related Terms
- Invoice
- InvoiceItem
- Financial Records Are Logically Immutable (decision)

# Customer Management

## Customer 360

### Definition
A unified profile workspace for customer operations across modules.

### Notes
The Customer 360 workspace contains eight standard tabs: **Overview** (profile card, status, active subscription summary, outstanding balance), **Subscriptions** (all subscriptions with status badges), **Billing** (invoice list with due dates and aging), **Payments** (payment history with allocation summary), **Tickets** (open and recent tickets), **Timeline** (chronological business event narrative), **Activity Log** (detailed audit entries), **Attachments** (documents, photos, GPS records).

Tab availability varies by Customer status — see `docs/workflows/customer-workflow.md` for the full tab inventory.

### Related Terms
- Entity 360
- Timeline
- Activity Log
- Customer Portal
- customer-workflow.md

## Customer Status

### Definition
The lifecycle state of a Customer account. Expressed as the `customers.status` column and the `CustomerStatus` PHP backed enum.

### Notes
The four canonical states are:
- **Prospect** (`prospect`) — new enquiry, no active Subscription yet. Default/initial state.
- **Active** (`active`) — has at least one active Subscription; full portal access.
- **Suspended** (`suspended`) — account suspended by administrative action only.
- **Terminated** (`terminated`) — account permanently closed; terminal state.

Customer Status is independent of Subscription Status. See `docs/workflows/customer-workflow.md` for full transition rules.

### Related Terms
- Customer
- Prospect
- CustomerStatus (enum)
- Customer Suspension

## Customer Suspension

### Definition
An administrative action that suspends a Customer account due to fraud, policy violation, legal hold, or contractual breach. Governed by `docs/workflows/customer-workflow.md`.

### Notes
Customer Suspension is **not** triggered by billing overdue policy. It is an explicit action by an authorized Administrator or Customer Service representative.

Customer Suspension does **not** automatically suspend active Subscriptions. The Customer account status and Subscription status are orthogonal axes evaluated independently.

Distinguish from **Subscription Suspension**, which is billing-driven.

### Related Terms
- Customer Status
- Subscription Suspension
- CustomerSuspended (event)

## Subscription Suspension

### Definition
A service restriction applied to a Subscription when billing overdue conditions are met (7+ days overdue) or when manually applied by an authorized operator. Governed by `docs/workflows/subscription-lifecycle.md`.

### Notes
Subscription Suspension restricts internet service access at the network level via ONU provisioning.

Distinguish from **Customer Suspension**, which is an administrative account action unrelated to billing policy.

Subscription Suspension does **not** affect Customer account status.

### Related Terms
- Subscription
- Customer Suspension
- SubscriptionSuspended (event)
- SuspensionCase

## Employee 360

### Definition
A unified profile workspace for employee operations and governance.

### Notes
Includes permissions, assignments, sessions, and impersonation history context.

### Related Terms
- Entity 360
- Role
- Permission
- Impersonation

## Entity 360

### Definition
A standardized tabbed profile pattern for core entities.

### Notes
Entity tabs are metadata-driven and can be extended by modules.

### Related Terms
- Customer 360
- Employee 360
- Metadata Driven CRUD

## Service Request

### Definition
A structured request for customer-initiated service changes handled through workflow.

### Notes
Requests are reviewed and processed operationally rather than directly changing core service state.

### Related Terms
- Portal Feature Requests
- Subscription
- Work Order

# Billing

## Billing Period

### Definition
A defined recurring time window for invoice generation per subscription.

### Notes
Only one invoice may exist for a subscription in the same billing period.

### Related Terms
- Invoice
- Subscription
- Due Date

## Invoice

### Definition
A financial document representing charges for a subscription and billing period.

### Notes
Invoices are generated per subscription period and can be generated as PDF on-demand.

### Related Terms
- Invoice Item
- Billing Period
- Payment Allocation

## Invoice Item

### Definition
A line-level charge component within an invoice.

### Notes
Tax and discount are applied at invoice level in this platform design.

### Related Terms
- Invoice
- Tax
- Discount

## Invoice-Level Tax

### Definition
Tax calculated and stored at invoice summary level.

### Notes
Not applied separately per line item in the default model.

### Related Terms
- Invoice
- Invoice-Level Discount
- Immutable Financial Snapshot

## Invoice-Level Discount

### Definition
Discount calculated and stored at invoice summary level.

### Notes
Keeps billing rules simpler for recurring ISP charges.

### Related Terms
- Invoice
- Invoice-Level Tax
- Billing Engine

## Due Date

### Definition
The payment deadline for an invoice.

### Notes
Used with grace period and overdue age rules for suspension evaluation.

### Related Terms
- Grace Period
- Overdue Age
- Suspension

## Grace Period

### Definition
An additional allowed payment window after due date before service suspension policy applies.

### Notes
Default policy example uses 10 calendar days.

### Related Terms
- Due Date
- Auto Suspend Policy
- Reactivation

## Overdue Age

### Definition
The number of days an unpaid invoice has passed its due date.

### Notes
Auto suspension is evaluated by overdue age, not invoice count.

### Related Terms
- Due Date
- Grace Period
- Auto Suspend Policy

## Suspension

### Definition
A service state where a subscription is temporarily restricted due to policy conditions.

### Notes
Billing triggers suspension, while network changes execute through provisioning workflow.

### Related Terms
- Auto Suspend Policy
- Suspension Workflow
- Provisioning Queue

## Reactivation

### Definition
Restoration of subscription service after suspension conditions are cleared.

### Notes
Can be automatic or manual based on configuration.

### Related Terms
- Suspension
- Payment
- Provisioning Queue

## Auto Suspend Policy

### Definition
A billing policy that automatically suspends service based on overdue age.

### Notes
The oldest unpaid invoice due date is used to determine suspension eligibility.

### Related Terms
- Overdue Age
- Grace Period
- Suspension Workflow

## Suspension Workflow

### Definition
The controlled process that translates billing suspension events into provisioning actions.

### Notes
Billing does not directly modify network devices.

### Related Terms
- Auto Suspend Policy
- Provisioning Queue
- Reactivation

## Immutable Financial Snapshot

### Definition
A rule that stores all financial values as historical snapshots at invoice creation time.

### Notes
Invoice rendering must not depend on current package or pricing configuration.

### Related Terms
- Invoice
- Financial Records Are Logically Immutable
- Time and Currency Standards

# Payments

## Payment

### Definition
A recorded financial transaction used to settle one or more invoices.

### Notes
Payments are separate entities from invoices and can be partial.

### Related Terms
- Payment Allocation
- Invoice
- Reactivation

## Payment Allocation

### Definition
A mapping of a payment amount to specific invoice balances.

### Notes
One payment can allocate across multiple invoices.

### Related Terms
- Payment
- Invoice
- Outstanding Balance

## Payment Status

### Definition
Canonical lifecycle state of a payment record in the Payment Workflow.

### Notes
Canonical values: `intent_created`, `waiting_payment`, `received`, `validated`, `recorded`, `partially_allocated`, `fully_allocated`, `completed`, `reversed`, `failed`.

### Related Terms
- Payment
- Payment Allocation
- Payment Workflow

## Payment Allocation Status

### Definition
Lifecycle state of a payment allocation entry.

### Notes
Canonical values: `allocated`, `reversed`.
Allocation corrections preserve history and are represented through reallocation/reversal, not deletion.

### Related Terms
- Payment Allocation
- Financial Records Are Logically Immutable
- Payment Workflow

## Outstanding Balance

### Definition
The unpaid amount remaining on an invoice or customer account context.

### Notes
Used in overdue evaluation and collection workflows.

### Related Terms
- Invoice
- Payment Allocation
- Overdue Age

## Proof of Payment

### Definition
Supporting evidence attached to validate payment completion.

### Notes
Typically handled through attachment categories.

### Related Terms
- Attachment Categories
- Payment
- Audit Activity Log

# Customer Portal

## Customer Portal

### Definition
A self-service interface where customers access their own service and billing data.

### Notes
Uses the same core application and database as back-office.

### Related Terms
- Customer
- Role Based Access Control
- Portal Feature Requests

## Portal Feature Requests

### Definition
A controlled request mechanism for customer-initiated service changes.

### Notes
Examples include upgrade, downgrade, relocation, and termination.

### Related Terms
- Customer Portal
- Service Request
- Subscription

## Mobile-First Unified Portal

### Definition
A responsive platform strategy where customer and employee portals share one web codebase.

### Notes
Android access can be provided via WebView before native apps.

### Related Terms
- API First Architecture
- Collector
- Universal QR Search

# Support & Tickets

## Ticket

### Definition
A support case used to track customer or operational issues.

### Notes
Tickets can appear in Entity 360 views and are searchable globally.

### Related Terms
- Customer 360
- Global Search
- Timeline

## Work Order

### Definition
An operational assignment for field execution such as installation or repair.

### Notes
Often linked to technicians, service areas, and network assets.

### Related Terms
- Technician
- Installation
- Service Request

## Installation

### Definition
The process of setting up customer service and required network endpoints.

### Notes
Installation artifacts can be stored through the attachment system.

### Related Terms
- Work Order
- ONT
- Subscription

# Network & Monitoring

## OLT

### Definition
Optical Line Terminal device in the access network hierarchy.

### Notes
Part of logical and physical topology models.

### Related Terms
- PON
- FAT
- ONU

## OLT Status

### Definition
The lifecycle state of an `OLT` asset record. Expressed as the `olts.status` column and the `OltStatus` PHP backed enum.

### Notes
The four canonical states are:
- **Planned** (`planned`) - registered but not operationally assignable. Default.
- **Active** (`active`) - assignable for topology and provisioning use.
- **Maintenance** (`maintenance`) - temporarily unavailable for new operational assignment.
- **Retired** (`retired`) - terminal historical state.

OLT Status is distinct from monitoring health states such as Healthy, Warning, Critical, Unknown, or Maintenance in the monitoring workflow.

### Related Terms
- OLT
- Monitoring
- ONU

## Router

### Definition
Network topology router asset used in provisioning and monitoring hierarchies.

### Notes
Router is managed as a first-class infrastructure asset. Core routers may parent distribution routers for topology propagation and operational scoping.

### Related Terms
- Router Status
- OLT
- Monitoring

## Router Status

### Definition
The lifecycle state of a `Router` asset record. Expressed as the `routers.status` column and the `RouterStatus` PHP backed enum.

### Notes
The four canonical states are:
- **Planned** (`planned`) - registered but not operationally assignable. Default.
- **Active** (`active`) - assignable for topology and provisioning use.
- **Maintenance** (`maintenance`) - temporarily unavailable for new operational assignment.
- **Retired** (`retired`) - terminal historical state.

Router Status is distinct from monitoring health states such as Healthy, Warning, Critical, Unknown, or Maintenance in the monitoring workflow.

### Related Terms
- Router
- Monitoring
- OLT

## ODF

### Definition
Optical Distribution Frame used in physical topology and fiber distribution.

### Notes
Appears in physical topology representation, not the simplified logical model.

### Related Terms
- OLT
- FAT
- Dropcore

## FAT

### Definition
Fiber Access Terminal used as a downstream distribution point in the network.

### Notes
Used in both topology context and operational area hierarchy examples.

Operational FAT reachability is derived from downstream ONU endpoint observations, not from the FAT lifecycle column itself.

### Related Terms
- OLT
- ONT
- Service Area

## FAT Status

### Definition
The lifecycle state of a `FAT` asset record. Expressed as the `fats.status` column and the `FatStatus` PHP backed enum.

### Notes
The four canonical states are:
- **Planned** (`planned`) - registered but not operationally assignable. Default.
- **Active** (`active`) - assignable for downstream distribution scope.
- **Maintenance** (`maintenance`) - temporarily unavailable for new assignment.
- **Retired** (`retired`) - terminal historical state.

FAT Status is distinct from monitoring-derived downstream reachability, which is computed from the mapped ONU set under the FAT.

### Related Terms
- FAT
- ONU
- ODF

## Dropcore

### Definition
Drop fiber segment connecting distribution points to customer endpoint.

### Notes
Explicitly part of physical topology.

### Related Terms
- ODF
- FAT
- ONT

## ONT

### Definition
Optical Network Terminal at customer side endpoint.

### Notes
Often used interchangeably with ONU depending vendor and context.

### Related Terms
- ONU
- OLT
- Dropcore

## ONU

### Definition
Optical Network Unit used for customer endpoint connectivity and monitoring.

### Notes
ONT and ONU terms may overlap in operations; this project supports both terminology references.

### Related Terms
- ONT
- OLT
- ONU Monitoring

## ONU Monitoring

### Definition
Operational monitoring of ONU or ONT status and performance indicators.

### Notes
End-user monitoring is abstracted into simplified health statuses.

### Related Terms
- Signal Health
- RX Power
- Monitoring

## Monitoring

### Definition
System capability for observing network/device health and operational state.

### Notes
Uses simplified status classes for operational readability.

### Related Terms
- ONU Monitoring
- Signal Health
- NOC

## RX Power

### Definition
Received optical signal strength metric used in fiber endpoint diagnostics.

### Notes
Used as technical signal-quality input, not always exposed directly to end users.

### Related Terms
- Signal Health
- ONU Monitoring
- TX Power

## TX Power

### Definition
Transmitted optical signal strength metric from device perspective.

### Notes
Commonly paired with RX Power for signal diagnostics.

### Related Terms
- RX Power
- ONU Monitoring
- Signal Health

## Signal Health

### Definition
A user-facing health abstraction for network status.

### Notes
Standard statuses include Healthy, Warning, Critical, and Offline.

### Related Terms
- Monitoring
- ONU Monitoring
- Health Status Abstraction

## Network Hierarchy

### Definition
The modeled relationship of network components in logical and physical views.

### Notes
Logical and physical topology are intentionally separated.

### Related Terms
- Logical Topology
- Physical Topology
- OLT

## Logical Topology

### Definition
Service-oriented network path model used for monitoring and service management.

### Notes
Example path: OLT -> PON -> FAT -> ONT.

### Related Terms
- Network Hierarchy
- Physical Topology
- Monitoring

## Physical Topology

### Definition
Inventory and field-operations path model of physical network components.

### Notes
Example path: OLT -> ODF -> FAT -> Dropcore -> ONT.

### Related Terms
- Network Hierarchy
- Logical Topology
- Inventory

# Inventory & Assets

## Asset

### Definition
A managed physical or logical resource tracked by the platform.

### Notes
Assets can include devices, endpoints, and installation-related components.

### Related Terms
- OLT
- ONU
- Attachment

## Generic Attachment System

### Definition
A shared attachment infrastructure used by multiple modules.

### Notes
Supports documents, images, and other files with metadata stored in database.

### Related Terms
- Attachment Categories
- File Storage Strategy
- Attachment Access Control

## Attachment Categories

### Definition
A classification system for attachment purpose and type.

### Notes
Standard examples include Photo, Document, Contract, Identity, and Proof of Payment.

### Related Terms
- Generic Attachment System
- Proof of Payment
- Attachment Lifecycle

## File Storage Strategy

### Definition
Attachment file content is stored in filesystem or object storage, while database stores metadata only.

### Notes
Improves DB performance and backup efficiency.

### Related Terms
- Generic Attachment System
- Attachment Lifecycle
- Attachment Preview Support

## Attachment Access Control

### Definition
Authorization model where attachment permissions inherit from owning entity permissions.

### Notes
No separate standalone attachment permissions required for basic use.

### Related Terms
- Role Based Access Control
- Generic Attachment System
- Ticket

## Attachment Preview Support

### Definition
In-browser preview capability for selected file types.

### Notes
Images and PDFs are previewed; other file types are downloaded.

### Related Terms
- File Storage Strategy
- Generic Attachment System
- Attachment Lifecycle

## Attachment Lifecycle

### Definition
Lifecycle policy for attachment create, process, soft delete, recovery, and purge.

### Notes
Includes optional image optimization (resize/compress/thumbnail) and delayed physical deletion.

### Related Terms
- Soft Delete Policy
- Attachment Categories
- File Storage Strategy

# Notifications

## Event

### Definition
A domain signal that something meaningful happened in business flow.

### Notes
Notifications are generated from events, not hardcoded directly in modules.

### Related Terms
- Event-Driven Notification Engine
- Notification Event Catalog
- Timeline

## Event-Driven Notification Engine

### Definition
A centralized engine that listens to business events and handles notification decisioning.

### Notes
Chooses channels and templates based on event and preferences.

### Related Terms
- Notification Queue Processing
- Template-Based Notifications
- Notification Preferences

## Multi-Channel Notification Support

### Definition
Capability to deliver notifications through multiple channels.

### Notes
Initial channels include WhatsApp, Email, and In-App, with extensibility for others.

### Related Terms
- Notification Engine
- Notification Preferences
- Notification Queue Processing

## Notification Queue Processing

### Definition
Asynchronous notification delivery pipeline using queue workers.

### Notes
Business transactions should not wait for external channel delivery.

### Related Terms
- Event-Driven Notification Engine
- Notification Audit Trail
- Retry

## Notification Preferences

### Definition
User-configurable settings that control which notification types and channels are enabled.

### Notes
Preferences can differ per event type.

### Related Terms
- Multi-Channel Notification Support
- Settings Engine
- Notification Event Catalog

## Template-Based Notifications

### Definition
Notification content system using reusable, centrally managed templates with variables.

### Notes
Enables consistent messaging and easier non-code updates.

### Related Terms
- Event-Driven Notification Engine
- Notification Preferences
- Notification Audit Trail

## Notification Audit Trail

### Definition
Recorded history of notification delivery attempts and outcomes.

### Notes
Includes recipient, channel, status, sent time, and provider response.

### Related Terms
- Audit Activity Log
- Notification Queue Processing
- Notification Event Catalog

## Notification Event Catalog

### Definition
A controlled registry of allowed notification-triggering events.

### Notes
Prevents ad-hoc event names and duplicate behavior.

### Related Terms
- Event
- Event-Driven Notification Engine
- Template-Based Notifications

# Security & Access Control

## User

### Definition
An authenticated identity that can access system features.

### Notes
Users may represent internal staff or customers with different access scopes.

### Related Terms
- Role
- Permission
- Impersonation

## Role

### Definition
A named group of permissions assigned to users.

### Notes
Roles can also influence navigation visibility and area-level access.

### Related Terms
- Permission
- User
- RBAC

## Permission

### Definition
A granular authorization capability used to allow or deny specific actions.

### Notes
Permissions support operational safeguards such as impersonation control.

### Related Terms
- Role
- User
- Area-Based Visibility

## RBAC

### Definition
Role-Based Access Control model used to enforce system authorization.

### Notes
RBAC combines roles, permissions, and scoped visibility controls.

### Related Terms
- Role
- Permission
- User

## Impersonation

### Definition
A temporary authorized session where one user acts as another for support or troubleshooting.

### Notes
Must be explicitly auditable and non-nested.

### Related Terms
- User
- Role
- Audit Activity Log

## Area-Based Visibility

### Definition
Access restriction mechanism based on assigned geographic or operational areas.

### Notes
Users without global scope only see area-assigned data.

### Related Terms
- Service Area
- Cluster
- Role Based Access Control

## Secret Setting Protection

### Definition
Security control requiring encryption at rest for sensitive configuration values.

### Notes
Applies to API keys, tokens, passwords, and other credentials.

### Related Terms
- Settings Engine
- Settings Change Audit
- Audit Activity Log

# Audit & Timeline

## Audit Activity Log

### Definition
A centralized security and compliance log for user and system actions.

### Notes
Captures before/after values, actor, timestamp, and technical metadata.

### Related Terms
- Timeline
- Impersonation
- Settings Change Audit

## Timeline

### Definition
A business-event narrative for an entity lifecycle.

### Notes
Timeline is not the same as Activity Log.

Timeline focuses on operationally meaningful events; Activity Log focuses on technical/accountability records.

### Related Terms
- Entity Timeline
- Activity Log
- Timeline Event Severity

## Activity Log

### Definition
An accountability-focused log of system and user actions for auditing and forensics.

### Notes
Usually more technical and complete than timeline entries.

### Related Terms
- Audit Activity Log
- Timeline
- State Transition Logging

## Entity Timeline

### Definition
Timeline view attached to a specific entity such as customer, invoice, or ticket.

### Notes
Provides chronological context for operations and support.

### Related Terms
- Timeline
- Human-Readable Timeline Events
- Customer 360

## Timeline Event Severity

### Definition
Priority marker for timeline entries indicating operational importance.

### Notes
Standard levels: Info, Success, Warning, Critical.

### Related Terms
- Entity Timeline
- Human-Readable Timeline Events
- Monitoring

## Human-Readable Timeline Events

### Definition
Rule that timeline entries must use business-friendly language.

### Notes
Avoid raw internal event names in user-facing timeline text.

### Related Terms
- Timeline
- Activity Log
- Event

## State Transition Logging

### Definition
Mandatory recording of lifecycle state changes for entities.

### Notes
Records previous state, new state, actor, reason, and timestamp.

### Related Terms
- Lifecycle State Machine
- Audit Activity Log
- Timeline

# Search

## Global Search

### Definition
A single search interface that queries across multiple entities.

### Notes
Designed for operational speed when users only know partial identifiers.

### Related Terms
- Search Index
- Search Result Ranking
- Universal QR Search

## Search Index

### Definition
A dedicated indexed data structure optimized for fast retrieval.

### Notes
Can be updated asynchronously by background jobs.

### Related Terms
- Global Search
- Search Result Ranking
- Search Indexing

## Search Indexing

### Definition
The process of building and maintaining searchable representations of entity data.

### Notes
Decouples high-speed lookup from primary transactional queries.

### Related Terms
- Search Index
- Global Search
- Background Jobs

## Search Result Ranking

### Definition
Rule set for ordering results by relevance.

### Notes
Typical order: Exact, Prefix, Partial, Fuzzy.

### Related Terms
- Global Search
- Search Index
- Fuzzy Match

## Search History

### Definition
Stored recent or frequent search terms per user.

### Notes
Improves repeated lookup speed in operational workflows.

### Related Terms
- Global Search
- User
- Search Result Ranking

## Universal QR Search

### Definition
A lookup mechanism where scanning a QR opens the corresponding entity view.

### Notes
Customer QR behavior opens billing summary and outstanding invoices for collection workflows.

### Related Terms
- Global Search
- Customer-Based QR
- Entity 360

## Customer-Based QR

### Definition
QR convention where code identifies customer entity rather than a single invoice.

### Notes
Supports multi-invoice collection scenarios.

### Related Terms
- Universal QR Search
- Customer
- Collector

# Geographic & Clusters

## Cluster

### Definition
A business-defined operational grouping used for collection, assignment, and reporting.

### Notes
Cluster is an operational grouping and not a network cluster.

### Related Terms
- Service Area
- Collector
- Geographic Service Coverage

## Service Area

### Definition
A geographic operational scope used for workload assignment and data visibility.

### Notes
Often represented in a hierarchy such as Region, Branch, Area, and FAT context.

### Related Terms
- Cluster
- Area-Based Visibility
- Collector

## Cluster Status

### Definition
The lifecycle state of a `Cluster` operational grouping.

### Notes
Canonical states are Planned, Active, and Inactive.

### Related Terms
- Cluster
- Service Area

## Service Area Status

### Definition
The lifecycle state of a `ServiceArea` operational territory.

### Notes
Canonical states are Draft, Active, Merged, and Archived.

### Related Terms
- Service Area
- Area-Based Visibility

## Employee Service Area Assignment

### Definition
The administrative linkage between an employee profile and one or more service areas for assignment and visibility purposes.

### Notes
Assignments may include one primary service area used as the default operational context.

### Related Terms
- Employee
- Service Area
- Area-Based Visibility

## Geographic Coordinates

### Definition
Latitude and longitude data assigned to network assets and customers.

### Notes
Supports mapping, outage analysis, and route planning.

### Related Terms
- Geographic Service Coverage
- Coverage Visualization
- Network Assets

## Geographic Service Coverage

### Definition
Derived or visualized service reach based on customer and asset location data.

### Notes
Coverage maps are informational and do not represent exact fiber routes.

### Related Terms
- Coverage Visualization
- Geographic Coordinates
- Cluster

## Coverage Visualization

### Definition
Map overlay visualization of points and optional cluster polygons for operational planning.

### Notes
Can support capacity planning and service expansion analysis.

### Related Terms
- Geographic Service Coverage
- Cluster
- Service Area

## Administrative Area Boundaries

### Definition
Polygon-based geometric boundaries used for operational or administrative grouping.

### Notes
Useful for reporting and automatic area or cluster assignment.

### Related Terms
- Cluster
- Coverage Visualization
- Geographic Coordinates

# Technical Architecture

## Modular Monolith

### Definition
A single deployable application organized into bounded internal modules.

### Notes
Chosen as initial architecture for delivery speed and reduced operational complexity.

### Related Terms
- Module
- API First Architecture
- Event

## Module

### Definition
A cohesive domain package that encapsulates related business logic and data behavior.

### Notes
Modules communicate through service boundaries and events.

### Related Terms
- Modular Monolith
- Event
- API First Architecture

## API-First Architecture

### Definition
Architecture rule that all business functions are exposed through application services and APIs.

### Notes
UI layers should consume the same APIs as future external clients.

### Related Terms
- Mobile-First Unified Portal
- Service
- Endpoint

## Universal CRUD

### Definition
A shared CRUD framework using reusable views and configurable metadata.

### Notes
Used to reduce duplication across many modules.

### Related Terms
- Metadata Driven CRUD
- Universal CRUD Views
- Entity 360

## Metadata Driven CRUD

### Definition
CRUD behavior generated from metadata definitions such as fields, columns, and validation rules.

### Notes
Supports rapid module onboarding and consistency.

### Related Terms
- Universal CRUD
- Universal CRUD Views
- Module

## Universal CRUD Views

### Definition
Reusable UI templates for index, datatable, form, and detail pages.

### Notes
Module-specific custom fields can be injected when required.

### Related Terms
- Universal CRUD
- Metadata Driven CRUD
- View Layer

## Lifecycle State Machine

### Definition
A controlled model of allowed entity states and transitions.

### Notes
Direct state mutation outside rules is prohibited.

### Related Terms
- State Transition Logging
- Timeline
- Audit Activity Log

## Provisioning Queue

### Definition
An asynchronous queue that executes service provisioning actions against network systems.

### Notes
Used for suspension and reactivation execution to decouple billing from network operations.

### Related Terms
- Suspension Workflow
- Auto Suspend Policy
- Event

## Settings Engine

### Definition
A centralized configuration subsystem for operational and business rules.

### Notes
Prevents hardcoded operational values and supports runtime governance.

### Related Terms
- Typed Settings
- Feature Flags
- Settings Change Audit

## Feature Flags

### Definition
Runtime switches that enable or disable functionality without code deployment.

### Notes
Managed through settings registry and governance controls.

### Related Terms
- Settings Engine
- Registered Settings Only
- Deployment

## Registered Settings Only

### Definition
Policy requiring all settings keys to be declared in a registry before use.

### Notes
Avoids configuration sprawl and hidden behavior.

### Related Terms
- Settings Engine
- Typed Settings
- Setting Categories

## Typed Settings

### Definition
Settings model where each key has explicit data type and validation expectations.

### Notes
Common types include string, integer, decimal, boolean, JSON, and enum.

### Related Terms
- Settings Engine
- Registered Settings Only
- Secret Setting Protection

## Soft Delete Policy

### Definition
Default deletion behavior where records are marked as deleted without immediate physical removal.

### Notes
Financial records use correction workflows instead of normal soft-delete operations.

### Related Terms
- Attachment Lifecycle
- Financial Records Are Logically Immutable
- Audit Activity Log

## Financial Records Are Logically Immutable

### Definition
Financial records must be corrected through explicit reversal or void mechanisms, not deletion.

### Notes
Supports accounting traceability and compliance.

### Related Terms
- Immutable Financial Snapshot
- Soft Delete Policy
- Invoice

## Event

### Definition
A technical and business signal used for decoupled processing across modules.

### Notes
Drives notifications, timeline entries, and asynchronous workflows.

### Related Terms
- Notification Event Catalog
- Provisioning Queue
- Lifecycle State Machine

# Naming Conventions

## Tables

### Definition
Database tables use plural snake_case names.

### Notes
Examples: customers, subscriptions, invoices, invoice_items, payment_allocations, onu_statistics.

Junction and allocation tables should describe relationship purpose.

### Related Terms
- Models
- Settings Keys
- Search Index

## Models

### Definition
Application models use singular PascalCase names.

### Notes
Examples: Customer, Subscription, Invoice, PaymentAllocation, OnuStatistic.

### Related Terms
- Tables
- Services
- Controllers

## Controllers

### Definition
Controllers use PascalCase and end with Controller.

### Notes
Examples: CustomerController, InvoiceController, NotificationPreferenceController.

### Related Terms
- Services
- API-First Architecture
- Universal CRUD

## Services

### Definition
Service classes use PascalCase and end with Service.

### Notes
Examples: BillingService, SuspensionService, ProvisioningService, NotificationService.

### Related Terms
- Controllers
- Events
- API-First Architecture

## Events

### Definition
Event classes use PascalCase and past-tense or domain-event naming.

### Notes
Examples: InvoiceGenerated, PaymentReceived, SubscriptionSuspended, SubscriptionReactivated.

Event names should align with Notification Event Catalog.

### Related Terms
- Notification Event Catalog
- Timeline
- Audit Activity Log

## Notifications

### Definition
Notification classes use PascalCase and end with Notification.

### Notes
Examples: InvoiceDueNotification, PaymentReceivedNotification, ServiceSuspendedNotification.

### Related Terms
- Template-Based Notifications
- Multi-Channel Notification Support
- Notification Queue Processing

## Settings Keys

### Definition
Settings keys use dot notation with domain prefixes.

### Notes
Examples:
- billing.auto_suspend.enabled
- billing.auto_suspend.grace_days
- notifications.channel.whatsapp.enabled
- portal.features.self_service.enabled
- security.impersonation.enabled

Keys must be registered before use.

### Related Terms
- Registered Settings Only
- Typed Settings
- Feature Flags

# Abbreviations

## OLT

### Definition
Optical Line Terminal.

### Notes
Access network head-end device.

### Related Terms
- ONU
- ONT
- PON

## Router

### Definition
Managed network routing asset used in topology and provisioning.

### Notes
Router is a first-class infrastructure term; core and distribution roles are handled by the Router model and workflow.

### Related Terms
- Router Status
- OLT
- Monitoring

## ODF

### Definition
Optical Distribution Frame.

### Notes
Fiber termination and distribution component in physical topology.

### Related Terms
- OLT
- FAT
- Dropcore

## FAT

### Definition
Fiber Access Terminal.

### Notes
Distribution point in access network.

### Related Terms
- OLT
- ONT
- Service Area

## ONT

### Definition
Optical Network Terminal.

### Notes
Customer-side optical endpoint; often used similarly to ONU terminology.

### Related Terms
- ONU
- OLT
- Dropcore

## ONU

### Definition
Optical Network Unit.

### Notes
Customer endpoint network unit monitored by platform.

### Related Terms
- ONT
- ONU Monitoring
- Signal Health

## NOC

### Definition
Network Operations Center.

### Notes
Operational team for monitoring and incident response.

### Related Terms
- Monitoring
- Signal Health
- Service Area

## CRM

### Definition
Customer Relationship Management.

### Notes
Business capability for managing customer data and interactions.

### Related Terms
- Customer 360
- Customer Portal
- Ticket

## RBAC

### Definition
Role-Based Access Control.

### Notes
Authorization model based on user roles and permissions.

### Related Terms
- Role
- Permission
- User

## GIS

### Definition
Geographic Information System.

### Notes
Future integration direction for advanced spatial analysis.

### Related Terms
- Geographic Coordinates
- Coverage Visualization
- Administrative Area Boundaries

## API

### Definition
Application Programming Interface.

### Notes
Primary integration and interface contract layer in API-first architecture.

### Related Terms
- API-First Architecture
- Services
- Controllers

## OTP

### Definition
One-Time Password.

### Notes
Optional customer authentication factor for portal login.

### Related Terms
- Customer Portal
- User
- Security

## PPPoE

### Definition
Point-to-Point Protocol over Ethernet.

### Notes
May be modified by provisioning actions during suspension and restoration workflows.

### Related Terms
- Provisioning Queue
- Suspension Workflow
- Network Hierarchy

## QR

### Definition
Quick Response code.

### Notes
Used for rapid customer or entity lookup in field workflows.

### Related Terms
- Universal QR Search
- Customer-Based QR
- Global Search
