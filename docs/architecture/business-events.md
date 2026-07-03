# Business Events Catalog

**Version:** 1.0

**Status:** Living Document

---

# Purpose

This document defines the authoritative catalog of business events used throughout the ISP Billing, Monitoring, CRM, and Operations platform.

Business events are the communication contract between workflows and shared components.

They enable loose coupling between modules by allowing producers to publish facts without knowing who consumes them, and consumers to subscribe to relevant events without knowing how they were produced.

This document is not an API specification.

This document is not a message queue specification.

All event definitions remain implementation-agnostic.

---

# Event Principles

## Business First

Events represent completed business facts.

An event is something that has already happened, not a command or a request.

Events describe what occurred in the business domain. Implementation details are never part of event definitions.

---

## Past Tense Naming

Event names use past tense to reinforce that they represent completed facts.

Examples:

- `InvoicePublished`
- `PaymentCompleted`
- `TicketAssigned`
- `SubscriptionActivated`

---

## Immutable

Published events are immutable records.

Once an event is published it cannot be modified or deleted.

Corrections to business state are represented by new corrective events, not edits to prior events.

---

## Loose Coupling

Producers never know which consumers subscribe to their events.

Consumers subscribe to business events independently.

This allows workflows to evolve independently without requiring coordinated changes between producers and consumers.

---

## Event Categories

### Domain Events
Events representing completed business facts within a specific domain.

Examples: `InvoicePublished`, `SubscriptionActivated`, `TicketResolved`

### System Events
Events representing operational or technical milestones that support business workflows but are not themselves primary business facts.

Examples: `NotificationDelivered`, `AttachmentAdded`, `ProvisioningStarted`

### Integration Events
Events that cross domain ownership boundaries or represent integration handoffs between major workflow areas.

Examples: `ProvisioningCompleted`, `PaymentCompleted`, `CollectorVisitCompleted`

---

# Standard Event Definition

Every event in this catalog is defined using the following fields.

| Field | Description |
|---|---|
| **Event Name** | Canonical PascalCase past-tense name |
| **Description** | What this event means in business terms |
| **Category** | Domain / System / Integration |
| **Producer** | The workflow or module that publishes this event |
| **Consumers** | Workflows and components that subscribe to this event |
| **Trigger** | The business action or state transition that causes this event |
| **Business Meaning** | The business significance of this event |
| **Related Workflow** | The workflow document where this event is produced |
| **Timeline Impact** | Whether this event generates a timeline entry |
| **Activity Log Impact** | Whether this event generates an activity log entry |
| **Notification Impact** | Whether this event triggers a notification |
| **Customer Portal Impact** | Whether this event affects customer portal visibility or content |
| **Audit Requirement** | What audit traceability is required |
| **Idempotency** | Whether duplicate processing must produce the same outcome |
| **Retry Consideration** | Whether the event or its consumers must support retry |
| **Future Extensions** | Reserved future capabilities related to this event |

---

# Domain Events

---

## CustomerRegistered

### Event Name
`CustomerRegistered`

### Description
A new customer record has been created and the customer is registered in the platform.

### Category
Domain Event

### Producer
Customer Management

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Customer record creation is confirmed after identity and contact information are captured.

### Business Meaning
The customer now exists in the platform and is eligible to receive services, communications, and a portal account.

### Related Workflow
Subscription Lifecycle — Prospect state

### Timeline Impact
Yes. A timeline entry is created on the customer record.

### Activity Log Impact
Yes. The creation action and actor are recorded.

### Notification Impact
Optional. A welcome notification may be sent to the customer based on configured rules.

### Customer Portal Impact
Yes. The customer portal account becomes accessible.

### Audit Requirement
Actor, timestamp, and registration source must be preserved.

### Idempotency
Yes. Duplicate registration events for the same customer must not create duplicate records.

### Retry Consideration
Consumer processing must be retryable without side effects.

### Future Extensions
AI-driven onboarding workflow initiation.

---

## ClusterCreated

### Event Name
`ClusterCreated`

### Description
A new operational cluster has been defined for future or active territory governance.

### Category
Domain Event

### Producer
Area and Assignment Management

### Consumers
- Timeline
- Activity Log

### Trigger
An authorized actor creates a new cluster record.

### Business Meaning
The platform now has a new operational grouping available for staged or active use.

### Related Workflow
Service Area Workflow — Cluster Planned state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
No.

### Customer Portal Impact
No.

### Audit Requirement
Cluster identity, actor, and creation timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Automatic cluster capacity and coverage reporting.

---

## ClusterActivated

### Event Name
`ClusterActivated`

### Description
An operational cluster has become active for customer and territory assignment.

### Category
Domain Event

### Producer
Area and Assignment Management

### Consumers
- Timeline
- Activity Log
- Reporting

### Trigger
An authorized actor activates a planned or inactive cluster.

### Business Meaning
The cluster is now valid for active service area and customer assignment.

### Related Workflow
Service Area Workflow — Cluster Active state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
No.

### Customer Portal Impact
No.

### Audit Requirement
Activation actor, previous state, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Automatic workload rebalance triggers.

---

## ClusterInactivated

### Event Name
`ClusterInactivated`

### Description
An operational cluster has been removed from new assignment use while retaining historical references.

### Category
Domain Event

### Producer
Area and Assignment Management

### Consumers
- Timeline
- Activity Log
- Reporting

### Trigger
An authorized actor inactivates an active cluster.

### Business Meaning
The cluster remains historical but can no longer receive new operational assignments.

### Related Workflow
Service Area Workflow — Cluster Inactive state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Internal governance notification when policy requires.

### Customer Portal Impact
No.

### Audit Requirement
Inactivation actor, reason, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Automated dependency warnings before inactivation.

---

## ServiceAreaCreated

### Event Name
`ServiceAreaCreated`

### Description
A new service area has been defined for operational territory governance.

### Category
Domain Event

### Producer
Area and Assignment Management

### Consumers
- Timeline
- Activity Log

### Trigger
An authorized actor creates a new service area in draft state.

### Business Meaning
The platform has a new territory definition available for staged preparation.

### Related Workflow
Service Area Workflow — Service Area Draft state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
No.

### Customer Portal Impact
No.

### Audit Requirement
Cluster context, hierarchy parent, actor, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Boundary validation and GIS enrichment.

---

## ServiceAreaActivated

### Event Name
`ServiceAreaActivated`

### Description
A service area has become active for customer assignment and area-based visibility.

### Category
Domain Event

### Producer
Area and Assignment Management

### Consumers
- Timeline
- Activity Log
- Reporting
- Identity & Access

### Trigger
An authorized actor activates a draft service area.

### Business Meaning
The service area is now operationally available for workload assignment and visibility scope.

### Related Workflow
Service Area Workflow — Service Area Active state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Internal operational notification when policy requires.

### Customer Portal Impact
No.

### Audit Requirement
Activation actor, prior state, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Automatic workload publication to dependent modules.

---

## ServiceAreaMerged

### Event Name
`ServiceAreaMerged`

### Description
A source service area has been consolidated into another active destination service area.

### Category
Integration Event

### Producer
Area and Assignment Management

### Consumers
- Customer Management
- Collector Workflow
- Ticket Workflow
- Timeline
- Activity Log
- Reporting

### Trigger
An authorized actor completes a merge after reassignment preconditions are satisfied.

### Business Meaning
The source service area is terminal and its operational scope has been transferred to a destination area.

### Related Workflow
Service Area Workflow — Service Area Merged state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Internal change notification when policy requires.

### Customer Portal Impact
No.

### Audit Requirement
Source area, destination area, reassignment context, actor, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Medium. Downstream consumers must treat merge propagation as safe to repeat.

### Future Extensions
Automatic customer and assignment migration assistance.

---

## ServiceAreaArchived

### Event Name
`ServiceAreaArchived`

### Description
A service area has been retired from active use without merge.

### Category
Domain Event

### Producer
Area and Assignment Management

### Consumers
- Timeline
- Activity Log
- Reporting

### Trigger
An authorized actor archives an active or draft service area after dependency checks pass.

### Business Meaning
The service area remains historical but is no longer assignable.

### Related Workflow
Service Area Workflow — Service Area Archived state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Internal governance notification when policy requires.

### Customer Portal Impact
No.

### Audit Requirement
Archive actor, reason, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Archive retention automation.

---

## CustomerUpdated

### Event Name
`CustomerUpdated`

### Description
Core customer profile information has been modified by an authorized actor.

### Category
Domain Event

### Producer
Customer Management

### Consumers
- Timeline
- Activity Log
- Notification Workflow (when contact change affects delivery)

### Trigger
An authorized actor modifies customer identity, contact, or address information.

### Business Meaning
The customer record reflects updated verified information. Downstream systems relying on contact details should react accordingly.

### Related Workflow
Customer Management

### Timeline Impact
Yes. A timeline entry summarizes the change.

### Activity Log Impact
Yes. Before and after state snapshot is required.

### Notification Impact
Conditional. Contact channel updates may trigger re-verification or delivery configuration updates.

### Customer Portal Impact
Conditional. Displayed customer profile is updated.

### Audit Requirement
Actor, changed fields, before and after values, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk. Consumer idempotency is expected.

### Future Extensions
Customer data change approval workflow.

---

## CustomerConverted

### Event Name
`CustomerConverted`

### Description
A Customer has transitioned from Prospect to Active status for the first time. The customer's first Subscription has been activated and the account is now fully live.

### Category
Domain Event

### Producer
Customer Management (CustomerService — triggered by SubscriptionActivated listener)

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Reporting Engine

### Trigger
Customer's first Subscription transitions to Active. `CustomerService` listens to `SubscriptionActivated` and transitions Customer from Prospect to Active, then dispatches this event.

### Business Meaning
The customer is now an active service subscriber. Billing has started. The customer portal reflects active status. This is a key business KPI milestone (lead-to-activation conversion).

### Related Workflow
docs/workflows/customer-workflow.md — Prospect → Active transition

### Timeline Impact
Yes. A prominent timeline entry is created on the customer record: "Customer account activated."

### Activity Log Impact
Yes. Actor, trigger (Subscription ID), and timestamp recorded.

### Notification Impact
Yes. Activation confirmation notification sent to the customer via configured channels.

### Customer Portal Impact
Yes. Portal access becomes available. Service status shows active.

### Audit Requirement
Triggering Subscription ID, actor, and timestamp must be preserved.

### Idempotency
Yes. A Customer may only convert once per lifecycle. Duplicate events must be idempotent.

### Retry Consideration
Consumers must tolerate retry. Notification Engine must prevent duplicate activation notifications.

### Future Extensions
Activation-triggered upsell workflow or onboarding checklist.

---

## CustomerSuspended

### Event Name
`CustomerSuspended`

### Description
A Customer account has been suspended by an authorized administrator or Customer Service representative. Suspension is an administrative action only — it is never triggered by billing policy.

### Category
Domain Event

### Producer
Customer Management (CustomerService::suspend())

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
An authorized administrator or Customer Service representative explicitly suspends the Customer account with a documented reason (fraud, policy violation, legal hold, contractual breach).

### Business Meaning
The customer account is suspended. New Subscriptions, ServiceRequests, and portal access are blocked. Existing active Subscriptions are NOT automatically suspended — they continue to operate independently under their own lifecycle.

### Related Workflow
docs/workflows/customer-workflow.md — Active → Suspended transition

### Timeline Impact
Yes. A timeline entry records the suspension with the reason.

### Activity Log Impact
Yes. Actor, reason, and timestamp must be preserved.

### Notification Impact
Yes. Suspension notification sent to customer. Internal alert sent to Customer Service and Administrator.

### Customer Portal Impact
Yes. Portal access is blocked. Customer sees "account suspended" message on login attempt.

### Audit Requirement
Actor, suspension reason, timestamp, and triggering incident reference must be preserved.

### Idempotency
Yes.

### Retry Consideration
Portal access block must be applied synchronously before the customer's next request.

### Future Extensions
Time-limited suspension with automatic reactivation policy.

---

## CustomerReactivated

### Event Name
`CustomerReactivated`

### Description
A previously suspended Customer account has been reactivated by an authorized administrator or Customer Service representative.

### Category
Domain Event

### Producer
Customer Management (CustomerService::reactivate())

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
An authorized administrator or Customer Service representative explicitly reactivates a Suspended Customer account.

### Business Meaning
The customer account is active again. Portal access is restored. New Subscriptions and ServiceRequests may be submitted.

### Related Workflow
docs/workflows/customer-workflow.md — Suspended → Active transition

### Timeline Impact
Yes. A timeline entry records the reactivation.

### Activity Log Impact
Yes. Actor and timestamp must be preserved.

### Notification Impact
Yes. Reactivation notification sent to customer.

### Customer Portal Impact
Yes. Portal access is restored.

### Audit Requirement
Actor, timestamp, and resolution reason must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Reactivation-triggered satisfaction survey.

---

## CustomerTerminated

### Event Name
`CustomerTerminated`

### Description
A Customer account has been permanently closed. No new service contracts, payments, or interactions may be initiated. The account record is retained for audit and financial history.

### Category
Domain Event

### Producer
Customer Management (CustomerService::terminate())

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal
- Billing (financial closure confirmation)

### Trigger
An Administrator explicitly terminates the Customer account after all Subscriptions are in Terminated state and all outstanding balances are settled or written off.

### Business Meaning
The customer's relationship with the ISP is formally ended. The portal User account is disabled. Financial history is retained indefinitely. Terminated is a terminal state — no reactivation is permitted.

### Related Workflow
docs/workflows/customer-workflow.md — Active/Suspended → Terminated transition

### Timeline Impact
Yes. A prominent terminal timeline entry records the closure.

### Activity Log Impact
Yes. Actor, reason, and timestamp must be preserved.

### Notification Impact
Yes. Termination confirmation sent to customer. Internal notification to Finance and Customer Service.

### Customer Portal Impact
Yes. Portal access is permanently disabled. Portal User account status is set to Inactive.

### Audit Requirement
Actor, reason, financial clearance reference, and timestamp must be preserved.

### Idempotency
Yes. Terminated is a terminal state; duplicate events must be safe.

### Retry Consideration
Portal User account deactivation must be confirmed before event is considered processed.

### Future Extensions
Win-back campaign trigger on CustomerTerminated after configurable delay.

---

## SubscriptionCreated

### Event Name
`SubscriptionCreated`

### Description
A new Subscription record has been created and is in Pending status, awaiting the pre-activation workflow (survey, installation, provisioning).

### Category
Domain Event

### Producer
Customer Management (SubscriptionService::create())

### Consumers
- Timeline
- Activity Log

### Trigger
An authorized actor creates a Subscription for a Customer.

### Business Meaning
The subscription contract exists. Pre-activation work (survey, installation, provisioning) may now be scheduled.

### Related Workflow
docs/workflows/subscription-lifecycle.md — Pending state

### Timeline Impact
Yes. "Subscription created" entry on both Customer and Subscription records.

### Activity Log Impact
Yes. Actor and timestamp recorded.

### Notification Impact
Optional. Internal notification to Sales or Customer Service based on configured rules.

### Customer Portal Impact
Conditional. Visible as a pending service request if portal tracking is enabled.

### Audit Requirement
Actor, customer reference, package reference, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Automated survey scheduling trigger.

---

## SubscriptionReactivationPending

### Event Name
`SubscriptionReactivationPending`

### Description
A suspended Subscription has entered the Reactivation Pending state. Payment settlement has been confirmed and service restoration is in progress.

### Category
Domain Event

### Producer
Customer Management (SubscriptionService::requestReactivation())

### Consumers
- Provisioning Workflow (service restoration execution)
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Payment settlement confirms the overdue balance is cleared (for overdue-type suspension), or an authorized operator initiates reactivation (for manual-type suspension).

### Business Meaning
The customer has met the requirements for service restoration. The system is preparing to restore service access.

### Related Workflow
docs/workflows/subscription-lifecycle.md — Reactivation Pending state

### Timeline Impact
Yes. "Reactivation requested" entry.

### Activity Log Impact
Yes. Actor and trigger (payment reference where applicable) recorded.

### Notification Impact
Yes. Customer notified that reactivation is in progress.

### Customer Portal Impact
Yes. Visible as a restoration-in-progress state.

### Audit Requirement
Actor, trigger (payment reference or manual override), and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Provisioning restoration must be retryable.

### Future Extensions
Scheduled reactivation at a configured time after payment.

---

## SubscriptionActivated

### Event Name
`SubscriptionActivated`

### Description
A customer subscription has been successfully activated and internet service is live.

### Category
Integration Event

### Producer
Subscription Lifecycle

### Consumers
- Billing Workflow
- Provisioning Workflow (confirmation)
- Network Monitoring Workflow
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Provisioning confirms successful service activation and Subscription Lifecycle transitions to Active.

### Business Meaning
Billing may now begin. The customer has a live internet service. Monitoring is active. The customer portal reflects active service status.

### Related Workflow
Subscription Lifecycle — Active state

### Timeline Impact
Yes. A prominent timeline entry is created on both customer and subscription records.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer activation confirmation notification is sent.

### Customer Portal Impact
Yes. Service shows as active. Billing history becomes relevant.

### Audit Requirement
Activation actor, activation timestamp, provisioning reference, and billing start context must be preserved.

### Idempotency
Yes. A subscription may only be activated once per lifecycle.

### Retry Consideration
Consumers must tolerate retry. Billing workflow must prevent duplicate cycle starts.

### Future Extensions
Activation-triggered upsell workflow.

---

## SubscriptionSuspended

### Event Name
`SubscriptionSuspended`

### Description
A customer subscription has been suspended, restricting internet service access.

### Category
Integration Event

### Producer
Subscription Lifecycle

### Consumers
- Provisioning Workflow (service restriction execution)
- Collector Workflow (collection visibility update)
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Billing overdue policy conditions are met, or an authorized actor manually suspends the subscription.

### Business Meaning
Service access is restricted. The customer must settle outstanding invoices or receive an authorized override to resume service.

### Related Workflow
Subscription Lifecycle — Suspended state

### Timeline Impact
Yes. Suspension reason and trigger are recorded.

### Activity Log Impact
Yes. Suspension actor and policy trigger are recorded.

### Notification Impact
Yes. Customer suspension notification is sent.

### Customer Portal Impact
Yes. Service shows as suspended. Outstanding balance is prominently displayed.

### Audit Requirement
Suspension reason, trigger policy reference, actor, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Service restriction execution in Provisioning Workflow must be retryable.

### Future Extensions
Graduated suspension with partial restriction tiers.

---

## SubscriptionReactivated

### Event Name
`SubscriptionReactivated`

### Description
A previously suspended subscription has been reactivated and internet service is restored.

### Category
Integration Event

### Producer
Subscription Lifecycle

### Consumers
- Provisioning Workflow (service restoration execution)
- Billing Workflow (billing resumes)
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Payment settlement satisfies the reactivation policy conditions, or an authorized actor manually reactivates.

### Business Meaning
Service access is restored. Billing resumes. The customer portal reflects active service.

### Related Workflow
Subscription Lifecycle — Reactivated state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer reactivation notification is sent.

### Customer Portal Impact
Yes. Service shows as active again.

### Audit Requirement
Reactivation trigger, actor, payment reference where applicable, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Service restoration in Provisioning Workflow must be retryable.

### Future Extensions
Reactivation-triggered customer satisfaction survey.

---

## SubscriptionTerminated

### Event Name
`SubscriptionTerminated`

### Description
A customer subscription has been permanently terminated and service has ended.

### Category
Integration Event

### Producer
Subscription Lifecycle

### Consumers
- Provisioning Workflow (service deprovisioning)
- Billing Workflow (billing closure)
- Network Monitoring Workflow (monitoring deregistration)
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Customer requests termination, contract ends, or an authorized actor terminates the subscription by policy.

### Business Meaning
The service contract is permanently ended. Provisioning resources must be released. Billing lifecycle closes. Monitoring is deregistered.

### Related Workflow
Subscription Lifecycle — Terminated state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer termination confirmation is sent.

### Customer Portal Impact
Yes. Service shows as terminated. Historical billing records remain visible.

### Audit Requirement
Termination reason, actor, timestamp, and final settlement status must be preserved.

### Idempotency
Yes.

### Retry Consideration
Deprovisioning and resource release must be retryable.

### Future Extensions
Win-back campaign trigger based on termination reason.

---

## SurveyScheduled

### Event Name
`SurveyScheduled`

### Description
A site survey has been scheduled to confirm service feasibility at the customer location.

### Category
Domain Event

### Producer
Subscription Lifecycle

### Consumers
- Notification Workflow
- Timeline
- Activity Log

### Trigger
Sales or Customer Service schedules a survey appointment after customer qualification.

### Business Meaning
Operational intent to inspect the service location has been formally recorded.

### Related Workflow
Subscription Lifecycle — Survey Scheduled state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer and assigned field staff receive schedule notifications.

### Customer Portal Impact
Conditional. Visible as a pre-activation progress step when portal tracking is enabled.

### Audit Requirement
Scheduling actor, appointment details, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Notification delivery is independently retryable.

### Future Extensions
Calendar integration for automated appointment management.

---

## SurveyCompleted

### Event Name
`SurveyCompleted`

### Description
A site survey has been completed and the outcome has been recorded.

### Category
Domain Event

### Producer
Subscription Lifecycle

### Consumers
- Notification Workflow
- Timeline
- Activity Log

### Trigger
The assigned field team completes the survey and records the outcome.

### Business Meaning
Service feasibility is confirmed or rejected. Installation planning can proceed when outcome is positive.

### Related Workflow
Subscription Lifecycle — Survey Completed state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer and internal follow-up notification.

### Customer Portal Impact
Conditional. Visible as a completed pre-installation milestone.

### Audit Requirement
Outcome, actor, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Survey result capture via mobile form with photo evidence.

---

## InstallationStarted

### Event Name
`InstallationStarted`

### Description
The installation visit has begun at the customer location.

### Category
Domain Event

### Producer
Subscription Lifecycle

### Consumers
- Notification Workflow
- Timeline
- Activity Log

### Trigger
Assigned technician confirms the installation visit has started.

### Business Meaning
Active field work is in progress. Billing and monitoring have not started yet.

### Related Workflow
Subscription Lifecycle — Installation In Progress state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Internal progress notifications may be sent.

### Customer Portal Impact
Yes. Visible as an active installation milestone.

### Audit Requirement
Start time, technician identity, and location context must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Real-time GPS tracking during installation.

---

## InstallationCompleted

### Event Name
`InstallationCompleted`

### Description
The physical installation at the customer site has been successfully completed.

### Category
Integration Event

### Producer
Subscription Lifecycle

### Consumers
- Provisioning Workflow (provisioning handoff trigger)
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Technician confirms installation completion and records outcome evidence.

### Business Meaning
Physical installation is done. Provisioning may now begin. This is a critical milestone in the service onboarding journey.

### Related Workflow
Subscription Lifecycle — Installation Completed state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer and internal notifications confirm installation success and provisioning start.

### Customer Portal Impact
Yes. Installation milestone is marked complete.

### Audit Requirement
Completion time, technician, evidence references, and GPS context must be preserved.

### Idempotency
Yes.

### Retry Consideration
Provisioning Workflow intake must be idempotent.

### Future Extensions
Installation quality scoring based on attached evidence.

---

## ProvisioningStarted

### Event Name
`ProvisioningStarted`

### Description
The provisioning workflow has begun processing a service activation request.

### Category
System Event

### Producer
Provisioning Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log

### Trigger
Provisioning Workflow accepts the handoff after installation completion.

### Business Meaning
Technical service activation is underway. Billing has not started. Customer service will be live only after activation is confirmed.

### Related Workflow
Provisioning Workflow — Waiting For Provisioning state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Optional internal notification for provisioning intake.

### Customer Portal Impact
Conditional. Optional customer status update.

### Audit Requirement
Provisioning request reference, acceptance time, and correlation ID must be preserved.

### Idempotency
Yes.

### Retry Consideration
Queue intake must be idempotent.

### Future Extensions
Provisioning progress tracking dashboard.

---

## ProvisioningCompleted

### Event Name
`ProvisioningCompleted`

### Description
Provisioning has completed successfully and the service is ready for activation.

### Category
Integration Event

### Producer
Provisioning Workflow

### Consumers
- Subscription Lifecycle (activation trigger)
- Notification Workflow
- Timeline
- Activity Log

### Trigger
All provisioning steps including monitoring registration complete successfully.

### Business Meaning
The service is technically ready. Subscription Lifecycle can proceed to activation. Billing may begin after activation confirmation.

### Related Workflow
Provisioning Workflow — Activated state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Internal activation readiness notification. Optional customer notification.

### Customer Portal Impact
Conditional.

### Audit Requirement
Provisioning correlation ID, completion time, and resource assignment summary must be preserved.

### Idempotency
Yes.

### Retry Consideration
Subscription Lifecycle must handle duplicate activation signals.

### Future Extensions
Zero-touch provisioning completion tracking.

---

## InvoiceGenerated

### Event Name
`InvoiceGenerated`

### Description
A draft invoice has been generated with full billing data and immutable pricing snapshots.

### Category
Domain Event

### Producer
Billing Workflow

### Consumers
- Timeline
- Activity Log
- Notification Workflow (internal only, before publication)

### Trigger
Billing Workflow completes invoice calculation after a successful billing run.

### Business Meaning
A complete billing record exists in draft form. It is not yet visible to the customer or payable. Publication must follow.

### Related Workflow
Billing Workflow — Invoice Generated state

### Timeline Impact
Yes. Internal timeline entry.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Optional internal notification for invoice readiness review.

### Customer Portal Impact
No. Draft invoices are not visible to customers.

### Audit Requirement
Calculation inputs, actor, and timestamp must be preserved.

### Idempotency
Yes. Duplicate invoice generation is actively prevented.

### Retry Consideration
Publication step is separately retryable.

### Future Extensions
Invoice review and pre-publication approval workflow.

---

## InvoicePublished

### Event Name
`InvoicePublished`

### Description
An invoice has been published, frozen, and is now authoritative, payable, and visible to the customer.

### Category
Integration Event

### Producer
Billing Workflow

### Consumers
- Payment Workflow (payable invoice handoff)
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Billing Workflow freezes and publishes the generated invoice after all validation checks pass.

### Business Meaning
A receivable is officially created. The customer owes a defined amount by a defined due date. The invoice is permanently immutable.

### Related Workflow
Billing Workflow — Invoice Published state

### Timeline Impact
Yes. A customer-visible timeline entry is created.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer invoice publication notification is sent.

### Customer Portal Impact
Yes. Invoice becomes visible. PDF is available on demand.

### Audit Requirement
Publication actor, freeze timestamp, and invoice reference must be preserved.

### Idempotency
Yes. A published invoice cannot be published again.

### Retry Consideration
Notification delivery is independently retryable after successful publication.

### Future Extensions
Batch invoice publication reporting for finance reconciliation.

---

## InvoiceOverdue

### Event Name
`InvoiceOverdue`

### Description
A published invoice has exceeded its due date and grace period without full settlement.

### Category
Integration Event

### Producer
Billing Workflow

### Consumers
- Subscription Lifecycle (suspension candidate evaluation)
- Collector Workflow (collection eligibility)
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Billing policy evaluates unpaid invoice aging and detects a due date and grace period threshold breach.

### Business Meaning
The customer has an outstanding obligation that has not been met on time. Suspension and collection workflows may be triggered according to policy.

### Related Workflow
Billing Workflow — Overdue state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer overdue notification and internal escalation notification.

### Customer Portal Impact
Yes. Overdue status and aging are prominently displayed.

### Audit Requirement
Overdue detection timestamp, policy reference, and invoice reference must be preserved.

### Idempotency
Yes.

### Retry Consideration
Downstream suspension and collection triggers must be idempotent.

### Future Extensions
Smart overdue prediction before due date breach.

---

## InvoiceCancelled

### Event Name
`InvoiceCancelled`

### Description
A draft invoice has been cancelled before publication.

### Category
Domain Event

### Producer
Billing Workflow

### Consumers
- Timeline
- Activity Log
- Notification Workflow (internal)

### Trigger
An authorized actor or the billing engine cancels the invoice before it is published.

### Business Meaning
The billing cycle for the affected period does not produce a payable invoice. Cancellation reason must be preserved for audit.

### Related Workflow
Billing Workflow — Billing Closed state (cancellation path)

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Internal notification only.

### Customer Portal Impact
No. Draft cancellations are not customer-visible.

### Audit Requirement
Cancellation reason, actor, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Cancelled invoice summary in finance reporting.

---

## PaymentIntentCreated

### Event Name
`PaymentIntentCreated`

### Description
A payment intent has been created to represent a customer's intention to pay before payment confirmation.

### Category
Domain Event

### Producer
Payment Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Customer initiates a payment channel, generating a pending payment reference such as QRIS or virtual account.

### Business Meaning
The customer has expressed intent to pay. No invoice balance changes until payment is confirmed.

### Related Workflow
Payment Workflow — Payment Intent Created state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Optional customer payment instruction notification.

### Customer Portal Impact
Yes. Customer can see intent status and reference details.

### Audit Requirement
Intent channel, reference, actor, and timestamp must be preserved.

### Idempotency
Yes. Intent creation must not create duplicate payment records.

### Retry Consideration
Intent expiration and retry behavior are policy-driven.

### Future Extensions
Payment intent expiry notification.

---

## PaymentReceived

### Event Name
`PaymentReceived`

### Description
A payment has been received and an immutable payment record has been created.

### Category
Integration Event

### Producer
Payment Workflow

### Consumers
- Billing Workflow (invoice balance update trigger)
- Collector Workflow (collection outcome update)
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Payment is confirmed by the payment channel and an immutable record is created.

### Business Meaning
Money has been received. Allocation to invoices will follow. The payment record is permanent.

### Related Workflow
Payment Workflow — Payment Recorded state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer payment receipt notification.

### Customer Portal Impact
Yes. Payment appears in payment history.

### Audit Requirement
Payment amount, channel, reference, actor, and timestamp must be preserved.

### Idempotency
Yes. Duplicate payment confirmation must not create duplicate records.

### Retry Consideration
Allocation processing must be idempotent.

### Future Extensions
Real-time payment confirmation display.

---

## PaymentValidated

### Event Name
`PaymentValidated`

### Description
A received payment has been validated against all payment rules and is confirmed as a legitimate record.

### Category
System Event

### Producer
Payment Workflow

### Consumers
- Timeline
- Activity Log

### Trigger
Payment Workflow completes validation checks against amount, currency, customer identity, and duplicate rules.

### Business Meaning
The payment is structurally and business-rule valid and may proceed to allocation.

### Related Workflow
Payment Workflow — Payment Validation state

### Timeline Impact
Yes. Internal entry.

### Activity Log Impact
Yes.

### Notification Impact
No direct notification.

### Customer Portal Impact
No direct impact.

### Audit Requirement
Validation result, rules applied, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Validation may be retried after corrected input.

### Future Extensions
Fraud detection integration at validation stage.

---

## PaymentCompleted

### Event Name
`PaymentCompleted`

### Description
A payment lifecycle is complete after recording and allocation are finalized.

### Category
Integration Event

### Producer
Payment Workflow

### Consumers
- Billing Workflow (invoice status update)
- Subscription Lifecycle (reactivation eligibility trigger)
- Collector Workflow (collection closure)
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Payment recording and allocation are both finalized.

### Business Meaning
The customer's financial obligation is reduced or eliminated. Invoice statuses are updated. Reactivation eligibility may be triggered.

### Related Workflow
Payment Workflow — Payment Completed state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer payment completion notification.

### Customer Portal Impact
Yes. Payment shown as completed. Outstanding balance updated.

### Audit Requirement
Final payment amount, allocation summary, and completion timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Invoice status updates and reactivation triggers must be idempotent.

### Future Extensions
Payment completion-triggered loyalty recognition.

---

## PaymentReallocated

### Event Name
`PaymentReallocated`

### Description
An existing payment allocation has been changed under authorized policy.

### Category
Domain Event

### Producer
Payment Workflow

### Consumers
- Billing Workflow
- Notification Workflow
- Timeline
- Activity Log

### Trigger
An authorized actor changes how a payment is allocated across invoices.

### Business Meaning
The distribution of a confirmed payment across outstanding invoices has changed. The underlying payment record remains immutable.

### Related Workflow
Payment Workflow — Reallocation Policy

### Timeline Impact
Yes. Old and new allocation are recorded.

### Activity Log Impact
Yes. Reallocation actor and authorization context must be captured.

### Notification Impact
Conditional. Optional internal notification.

### Customer Portal Impact
Conditional. Updated allocation visible in payment history.

### Audit Requirement
Prior allocation, new allocation, authorizing actor, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Finance-driven automatic reallocation rules.

---

## PaymentReversed

### Event Name
`PaymentReversed`

### Description
An already recorded payment has been reversed through authorized correction workflow.

### Category
Integration Event

### Producer
Payment Workflow

### Consumers
- Billing Workflow (recompute balances)
- Subscription Lifecycle (reactivation eligibility re-evaluation)
- Collector Workflow
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Authorized reversal action is approved and executed on a recorded/completed payment.

### Business Meaning
Previously recognized settlement is rolled back through controlled financial correction while preserving full history.

### Related Workflow
Payment Workflow — Payment Reversed state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Customer and internal reversal notifications based on policy.

### Customer Portal Impact
Yes. Payment history reflects reversal and adjusted balances.

### Audit Requirement
Reversal reason, actor, authorization reference, and timestamp must be preserved.

### Idempotency
Yes. Duplicate reversal processing must not create duplicate balance rollback.

### Retry Consideration
Downstream recalculation must be idempotent.

### Future Extensions
Dual-approval reversal flow for high-value payments.

---

## PaymentFailed

### Event Name
`PaymentFailed`

### Description
A payment attempt failed during validation, recording, allocation, or policy checks.

### Category
Domain Event

### Producer
Payment Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Payment Workflow transitions to `failed` with a classified failure reason.

### Business Meaning
Settlement did not complete and no successful financial closure occurred.

### Related Workflow
Payment Workflow — Payment Failed state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Failure notifications according to policy.

### Customer Portal Impact
Yes. Failed status and next action guidance are visible.

### Audit Requirement
Failure class, reason, actor/system source, and timestamp must be preserved.

### Idempotency
Yes. Repeated failure signals for the same attempt must not duplicate records.

### Retry Consideration
Retry path is policy-driven and must preserve failed-attempt history.

### Future Extensions
Automated failure classification and remediation suggestions.

---

## CollectorAssigned

### Event Name
`CollectorAssigned`

### Description
A collector has been assigned to a collection task for an outstanding invoice.

### Category
Domain Event

### Producer
Collector Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log

### Trigger
An assignment policy assigns a collector to a collectible task, or a supervisor makes a manual assignment.

### Business Meaning
A specific collector is now responsible for following up on the outstanding balance with the customer.

### Related Workflow
Collector Workflow — Assigned state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Collector assignment notification.

### Customer Portal Impact
No. Internal assignment details are not exposed.

### Audit Requirement
Assignee identity, assignment method, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Workload-based automatic reassignment.

---

## CollectorVisitStarted

### Event Name
`CollectorVisitStarted`

### Description
A collector has started a field visit to the customer location for collection.

### Category
Domain Event

### Producer
Collector Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log

### Trigger
Collector confirms route start or arrival at the customer location.

### Business Meaning
Active collection field work has begun for the assigned customer.

### Related Workflow
Collector Workflow — On Route state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Internal route status notification.

### Customer Portal Impact
Conditional. Optional collector-arrival status when policy allows.

### Audit Requirement
Visit start time, collector identity, and location context must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Geo-fenced visit authenticity verification.

---

## CollectorVisitCompleted

### Event Name
`CollectorVisitCompleted`

### Description
A collector field visit has concluded with a recorded outcome.

### Category
Integration Event

### Producer
Collector Workflow

### Consumers
- Payment Workflow (if payment was collected)
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Collector records the visit outcome, which may include payment collected, follow-up required, or customer unavailable.

### Business Meaning
The collection visit is complete. Payment submission may follow if funds were collected. Outstanding invoices remain open until Payment Workflow confirms settlement.

### Related Workflow
Collector Workflow — Customer Visited and Payment Submitted states

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Internal outcome notification. Optional customer notification.

### Customer Portal Impact
Conditional. Visit outcome summary when policy allows.

### Audit Requirement
Visit outcome, evidence references, collector identity, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Payment submission to Payment Workflow must be idempotent.

### Future Extensions
Digital receipt generation on visit completion.

---

## TicketCreated

### Event Name
`TicketCreated`

### Description
A new ticket has been formally submitted and is open for triage.

### Category
Domain Event

### Producer
Ticket Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
A ticket is submitted by a customer, internal user, or automation rule.

### Business Meaning
A new operational issue or task requires attention. Triage and assignment will follow.

### Related Workflow
Ticket Workflow — Open state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer submission confirmation. Internal triage queue notification.

### Customer Portal Impact
Yes. Customer can see the submitted ticket.

### Audit Requirement
Creator identity, source channel, and submission timestamp must be preserved.

### Idempotency
Yes. Duplicate submission must be detected at triage.

### Retry Consideration
Low risk.

### Future Extensions
AI-assisted automatic triage and classification.

---

## TicketAssigned

### Event Name
`TicketAssigned`

### Description
A ticket has been assigned to a specific technician or team for execution.

### Category
Domain Event

### Producer
Ticket Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Supervisor or assignment engine selects a technician and records the assignment.

### Business Meaning
A responsible owner is now committed to the ticket. SLA tracking continues with the assignee accountable.

### Related Workflow
Ticket Workflow — Assigned state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Technician assignment notification. Supervisor notification when policy requires.

### Customer Portal Impact
Yes. Customer sees ticket as assigned.

### Audit Requirement
Assignee identity, assignment method, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
AI technician recommendation during assignment.

---

## TicketResolved

### Event Name
`TicketResolved`

### Description
A technician has completed the required work and recorded a resolution outcome with supporting evidence.

### Category
Domain Event

### Producer
Ticket Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Technician records resolution summary and attaches required evidence.

### Business Meaning
The reported issue or task is believed to be resolved. Verification or auto-closure will follow based on category policy.

### Related Workflow
Ticket Workflow — Resolved state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer resolution notification with optional confirmation prompt.

### Customer Portal Impact
Yes. Resolution summary is visible to the customer.

### Audit Requirement
Resolution actor, summary, evidence references, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Verification trigger must be idempotent.

### Future Extensions
Resolution quality scoring.

---

## TicketClosed

### Event Name
`TicketClosed`

### Description
A ticket has been fully resolved, verified, and closed. All work is complete.

### Category
Domain Event

### Producer
Ticket Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Verification is confirmed, auto-close policy is met, or an authorized actor closes the ticket.

### Business Meaning
The operational work is definitively complete. The ticket is no longer active but remains available for audit and reporting.

### Related Workflow
Ticket Workflow — Closed state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer closure notification. Optional satisfaction prompt.

### Customer Portal Impact
Yes. Ticket shows as closed with full history.

### Audit Requirement
Closure reason, actor, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Customer satisfaction survey trigger on closure.

---

## DeviceWarningRaised

### Event Name
`DeviceWarningRaised`

### Description
A network device has entered a warning health state indicating elevated risk but not critical failure.

### Category
Domain Event

### Producer
Network Monitoring Workflow

### Consumers
- Notification Workflow
- Ticket Workflow (ticket candidate recommendation)
- Timeline
- Activity Log
- Customer Portal (when customer's ONT is affected)

### Trigger
Monitoring health scoring detects warning threshold conditions for a monitored device.

### Business Meaning
A device is experiencing elevated risk. Operations should monitor and may prepare corrective action. Correlation may reveal upstream cause.

### Related Workflow
Network Monitoring Workflow — Warning state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Internal warning notification to NOC.

### Customer Portal Impact
Conditional. Customer may see service degradation notice when their device is affected.

### Audit Requirement
Device identity, health score, threshold breach details, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Ticket candidate creation must be idempotent.

### Future Extensions
AI-assisted warning correlation and probable cause suggestion.

---

## DeviceCriticalRaised

### Event Name
`DeviceCriticalRaised`

### Description
A network device has entered a critical health state indicating high-impact service or infrastructure risk.

### Category
Integration Event

### Producer
Network Monitoring Workflow

### Consumers
- Notification Workflow
- Ticket Workflow (ticket candidate recommendation)
- Timeline
- Activity Log
- Customer Portal (when customer connectivity is affected)

### Trigger
Monitoring health scoring detects critical threshold conditions for a monitored device.

### Business Meaning
Urgent operational response is required. Service impact may be immediate. Escalation and ticket creation are expected.

### Related Workflow
Network Monitoring Workflow — Critical state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Internal critical alert to NOC and escalation recipients.

### Customer Portal Impact
Yes. Customer may see outage or critical service status when their device is affected.

### Audit Requirement
Device identity, severity classification, correlation context, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Critical alert delivery must be retryable with high priority.

### Future Extensions
Automated escalation and ticket creation for critical device events.

---

## DeviceRecovered

### Event Name
`DeviceRecovered`

### Description
A previously degraded or critical network device has returned to stable monitored conditions.

### Category
Domain Event

### Producer
Network Monitoring Workflow

### Consumers
- Notification Workflow
- Ticket Workflow (recovery reference for open tickets)
- Timeline
- Activity Log
- Customer Portal

### Trigger
Monitoring health scoring detects that a prior degraded condition has cleared.

### Business Meaning
The device is healthy again. Service may be restored. Related tickets should be reviewed for closure eligibility.

### Related Workflow
Network Monitoring Workflow — Recovered state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Internal recovery notification. Optional customer service-restored notice.

### Customer Portal Impact
Yes. Customer may see service restored status.

### Audit Requirement
Recovery timestamp, prior event chain reference, and health classification must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Mean-time-to-recovery analytics.

---

## MaintenanceScheduled

### Event Name
`MaintenanceScheduled`

### Description
A planned maintenance window has been scheduled for network infrastructure or equipment.

### Category
Domain Event

### Producer
Network Monitoring Workflow / Operations

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
An authorized operator creates a maintenance window and defines its scope and schedule.

### Business Meaning
A planned service impact window is defined. Customers and operations teams should be informed in advance.

### Related Workflow
Network Monitoring Workflow — Maintenance state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Yes. Customer maintenance advisory. Internal operations notification.

### Customer Portal Impact
Yes. Customer may see upcoming maintenance notice.

### Audit Requirement
Maintenance scope, scheduled window, authorizing actor, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Notification delivery is independently retryable.

### Future Extensions
Customer self-service maintenance acknowledgment.

---

## MaintenanceStarted

### Event Name
`MaintenanceStarted`

### Description
A scheduled maintenance window has begun.

### Category
Domain Event

### Producer
Network Monitoring Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Maintenance window start time is reached and the maintenance context is activated.

### Business Meaning
Maintenance is in progress. Health signals during this window are interpreted within maintenance context. Alert noise suppression applies.

### Related Workflow
Network Monitoring Workflow — Maintenance state

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Internal maintenance start notification.

### Customer Portal Impact
Conditional. Maintenance banner or status is shown.

### Audit Requirement
Start time and scope must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Real-time maintenance impact visibility map.

---

## MaintenanceCompleted

### Event Name
`MaintenanceCompleted`

### Description
A maintenance window has ended and normal monitoring has resumed.

### Category
Domain Event

### Producer
Network Monitoring Workflow

### Consumers
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal

### Trigger
Maintenance window end time is reached or an authorized operator marks the maintenance as complete.

### Business Meaning
Normal operations and monitoring resume. Health signal interpretation returns to standard thresholds.

### Related Workflow
Network Monitoring Workflow — Recovered state (post-maintenance)

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
Conditional. Internal completion notification.

### Customer Portal Impact
Yes. Maintenance banner is removed.

### Audit Requirement
Completion time, outcome, and actor must be preserved.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Post-maintenance health trend analysis.

---

## NotificationScheduled

### Event Name
`NotificationScheduled`

### Description
A notification has been placed in the delivery queue with scheduling rules applied.

### Category
System Event

### Producer
Notification Workflow

### Consumers
- Timeline
- Activity Log

### Trigger
Notification Workflow completes template rendering and schedule evaluation for a business event.

### Business Meaning
A notification will be delivered at the scheduled time or immediately if immediate delivery is configured.

### Related Workflow
Notification Workflow — Delivery Scheduled state

### Timeline Impact
Conditional. Timeline entries are generated when the underlying business event warrants it.

### Activity Log Impact
Yes.

### Notification Impact
Not applicable. This event is produced by the Notification Workflow itself.

### Customer Portal Impact
No direct impact at scheduling time.

### Audit Requirement
Scheduling timestamp, channel, recipient reference, and priority must be preserved.

### Idempotency
Yes. Duplicate scheduling must be prevented.

### Retry Consideration
Scheduling state must be retryable without duplicate queue entries.

### Future Extensions
Smart delivery time optimization.

---

## NotificationDelivered

### Event Name
`NotificationDelivered`

### Description
A notification has been successfully delivered to the recipient through the selected channel.

### Category
System Event

### Producer
Notification Workflow

### Consumers
- Timeline
- Activity Log
- Customer Portal (for Portal Inbox channel)

### Trigger
Channel provider confirms successful delivery.

### Business Meaning
The intended recipient has received the notification. Delivery tracking is complete for this attempt.

### Related Workflow
Notification Workflow — Delivered state

### Timeline Impact
Conditional.

### Activity Log Impact
Yes.

### Notification Impact
Not applicable.

### Customer Portal Impact
Yes, when the Portal Inbox is the delivery channel. Notification becomes visible in inbox.

### Audit Requirement
Delivery confirmation, channel, recipient, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Not applicable after successful delivery.

### Future Extensions
Read receipt tracking.

---

## NotificationFailed

### Event Name
`NotificationFailed`

### Description
A notification delivery attempt has failed due to provider error, channel unavailability, or configuration issues.

### Category
System Event

### Producer
Notification Workflow

### Consumers
- Notification Workflow (retry or fallback trigger)
- Timeline
- Activity Log

### Trigger
Channel provider returns a failure response or delivery confirmation is not received within the configured window.

### Business Meaning
The recipient did not receive the notification through the attempted channel. Retry or fallback delivery will be evaluated.

### Related Workflow
Notification Workflow — Failed state

### Timeline Impact
Conditional.

### Activity Log Impact
Yes.

### Notification Impact
Not applicable directly. May trigger internal operations notification when thresholds are exceeded.

### Customer Portal Impact
No direct impact unless fallback delivery restores visibility.

### Audit Requirement
Failure reason, channel, attempt count, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Retry scheduling must be idempotent and must not overwrite prior failure records.

### Future Extensions
Delivery failure pattern analysis for provider quality monitoring.

---

## AttachmentAdded

### Event Name
`AttachmentAdded`

### Description
A file attachment has been added to a business entity such as a ticket, invoice, or customer record.

### Category
System Event

### Producer
Attachment System

### Consumers
- Timeline
- Activity Log

### Trigger
An authorized actor uploads a file and it is successfully processed and associated with a business entity.

### Business Meaning
Evidentiary or documentary content is now linked to the business record. Attachments support resolution, compliance, and audit requirements.

### Related Workflow
Ticket Workflow, Collector Workflow, and all workflows that support attachments

### Timeline Impact
Yes.

### Activity Log Impact
Yes.

### Notification Impact
No direct notification.

### Customer Portal Impact
Conditional. Attachments on customer-visible entities may be accessible according to visibility policy.

### Audit Requirement
Uploader identity, entity reference, file category, and timestamp must be preserved.

### Idempotency
Yes.

### Retry Consideration
Upload retry must not create duplicate attachment records.

### Future Extensions
Attachment AI classification and auto-tagging.

---

## AttachmentRemoved

### Event Name
`AttachmentRemoved`

### Description
An attachment has been logically removed from a business entity.

### Category
System Event

### Producer
Attachment System

### Consumers
- Timeline
- Activity Log

### Trigger
An authorized actor removes a file from a business entity. Physical deletion follows retention policy.

### Business Meaning
The attachment is no longer active on the record. The removal is auditable and the physical file is retained per retention policy.

### Related Workflow
All workflows that support attachments

### Timeline Impact
Yes.

### Activity Log Impact
Yes. Removal actor and reason must be recorded.

### Notification Impact
No direct notification.

### Customer Portal Impact
Conditional.

### Audit Requirement
Removal actor, reason, entity reference, and timestamp must be preserved. The physical file must not be immediately destroyed.

### Idempotency
Yes.

### Retry Consideration
Low risk.

### Future Extensions
Attachment restore workflow for accidentally removed files.

---

## UserImpersonationStarted

### Event Name
`UserImpersonationStarted`

### Description
An authorized administrator has started an impersonation session, acting as another user for support purposes.

### Category
System Event

### Producer
Identity and Access

### Consumers
- Activity Log

### Trigger
An authorized administrator initiates an impersonation session with a recorded reason.

### Business Meaning
Actions taken during this session are performed under the impersonated user's context but attributed to both the original and impersonated actors for audit purposes.

### Related Workflow
Identity and Access — Impersonation

### Timeline Impact
Yes. A security-sensitive entry is created.

### Activity Log Impact
Yes. Both actors are captured with the start reason and session reference.

### Notification Impact
Conditional. Internal security notification for impersonation start.

### Customer Portal Impact
No. Impersonation is an internal security event.

### Audit Requirement
Impersonating actor, impersonated actor, reason, session reference, and start timestamp are all mandatory.

### Idempotency
Yes.

### Retry Consideration
Not applicable.

### Future Extensions
Real-time impersonation visibility dashboard for security officers.

---

## UserImpersonationEnded

### Event Name
`UserImpersonationEnded`

### Description
An impersonation session has ended and the administrator has returned to their own identity context.

### Category
System Event

### Producer
Identity and Access

### Consumers
- Activity Log

### Trigger
The impersonation session is explicitly ended by the administrator or expires according to session policy.

### Business Meaning
All subsequent actions are attributed normally to the original actor. The impersonation session is fully closed.

### Related Workflow
Identity and Access — Impersonation

### Timeline Impact
Yes.

### Activity Log Impact
Yes. Session duration, end reason, and both actor references must be captured.

### Notification Impact
Conditional. Internal security notification for session end.

### Customer Portal Impact
No.

### Audit Requirement
Session reference, end timestamp, end reason, and duration must be preserved.

### Idempotency
Yes.

### Retry Consideration
Not applicable.

### Future Extensions
Impersonation session summary report for compliance review.

---

# Event Matrix

| Event | Producer | Consumers | Priority | Category |
|---|---|---|---|---|
| CustomerRegistered | Customer Management | Notification, Timeline, Activity Log, Customer Portal | Normal | Domain |
| CustomerUpdated | Customer Management | Timeline, Activity Log, Notification | Low | Domain |
| ClusterCreated | Area and Assignment Management | Timeline, Activity Log | Low | Domain |
| ClusterActivated | Area and Assignment Management | Timeline, Activity Log, Reporting | Low | Domain |
| ClusterInactivated | Area and Assignment Management | Timeline, Activity Log, Reporting | Low | Domain |
| ServiceAreaCreated | Area and Assignment Management | Timeline, Activity Log | Low | Domain |
| ServiceAreaActivated | Area and Assignment Management | Identity & Access, Timeline, Activity Log, Reporting | Low | Domain |
| ServiceAreaMerged | Area and Assignment Management | Customer Management, Collector Workflow, Ticket Workflow, Timeline, Activity Log, Reporting | Normal | Integration |
| ServiceAreaArchived | Area and Assignment Management | Timeline, Activity Log, Reporting | Low | Domain |
| SubscriptionActivated | Subscription Lifecycle | Billing, Provisioning, Monitoring, Notification, Timeline, Activity Log, Customer Portal | High | Integration |
| SubscriptionSuspended | Subscription Lifecycle | Provisioning, Collector, Notification, Timeline, Activity Log, Customer Portal | High | Integration |
| SubscriptionReactivated | Subscription Lifecycle | Provisioning, Billing, Notification, Timeline, Activity Log, Customer Portal | High | Integration |
| SubscriptionTerminated | Subscription Lifecycle | Provisioning, Billing, Monitoring, Notification, Timeline, Activity Log, Customer Portal | Normal | Integration |
| SurveyScheduled | Subscription Lifecycle | Notification, Timeline, Activity Log | Normal | Domain |
| SurveyCompleted | Subscription Lifecycle | Notification, Timeline, Activity Log | Normal | Domain |
| InstallationStarted | Subscription Lifecycle | Notification, Timeline, Activity Log | Normal | Domain |
| InstallationCompleted | Subscription Lifecycle | Provisioning, Notification, Timeline, Activity Log, Customer Portal | High | Integration |
| ProvisioningStarted | Provisioning Workflow | Notification, Timeline, Activity Log | Normal | System |
| ProvisioningCompleted | Provisioning Workflow | Subscription Lifecycle, Notification, Timeline, Activity Log | High | Integration |
| InvoiceGenerated | Billing Workflow | Timeline, Activity Log, Notification (internal) | Normal | Domain |
| InvoicePublished | Billing Workflow | Payment, Notification, Timeline, Activity Log, Customer Portal | High | Integration |
| InvoiceOverdue | Billing Workflow | Subscription Lifecycle, Collector, Notification, Timeline, Activity Log, Customer Portal | High | Integration |
| InvoiceCancelled | Billing Workflow | Timeline, Activity Log, Notification (internal) | Normal | Domain |
| PaymentIntentCreated | Payment Workflow | Notification, Timeline, Activity Log, Customer Portal | Normal | Domain |
| PaymentReceived | Payment Workflow | Billing, Collector, Notification, Timeline, Activity Log, Customer Portal | High | Integration |
| PaymentValidated | Payment Workflow | Timeline, Activity Log | Normal | System |
| PaymentCompleted | Payment Workflow | Billing, Subscription Lifecycle, Collector, Notification, Timeline, Activity Log, Customer Portal | High | Integration |
| PaymentReallocated | Payment Workflow | Billing, Notification, Timeline, Activity Log | Normal | Domain |
| PaymentReversed | Payment Workflow | Billing, Subscription Lifecycle, Collector, Notification, Timeline, Activity Log, Customer Portal | High | Integration |
| PaymentFailed | Payment Workflow | Notification, Timeline, Activity Log, Customer Portal | Normal | Domain |
| CollectorAssigned | Collector Workflow | Notification, Timeline, Activity Log | Normal | Domain |
| CollectorVisitStarted | Collector Workflow | Notification, Timeline, Activity Log | Normal | Domain |
| CollectorVisitCompleted | Collector Workflow | Payment, Notification, Timeline, Activity Log, Customer Portal | Normal | Integration |
| TicketCreated | Ticket Workflow | Notification, Timeline, Activity Log, Customer Portal | Normal | Domain |
| TicketAssigned | Ticket Workflow | Notification, Timeline, Activity Log, Customer Portal | Normal | Domain |
| TicketResolved | Ticket Workflow | Notification, Timeline, Activity Log, Customer Portal | Normal | Domain |
| TicketClosed | Ticket Workflow | Notification, Timeline, Activity Log, Customer Portal | Normal | Domain |
| DeviceWarningRaised | Network Monitoring | Notification, Ticket Workflow, Timeline, Activity Log, Customer Portal | High | Domain |
| DeviceCriticalRaised | Network Monitoring | Notification, Ticket Workflow, Timeline, Activity Log, Customer Portal | Critical | Integration |
| DeviceRecovered | Network Monitoring | Notification, Ticket Workflow, Timeline, Activity Log, Customer Portal | Normal | Domain |
| MaintenanceScheduled | Network Monitoring / Operations | Notification, Timeline, Activity Log, Customer Portal | Normal | Domain |
| MaintenanceStarted | Network Monitoring | Notification, Timeline, Activity Log, Customer Portal | Normal | Domain |
| MaintenanceCompleted | Network Monitoring | Notification, Timeline, Activity Log, Customer Portal | Normal | Domain |
| NotificationScheduled | Notification Workflow | Timeline, Activity Log | Low | System |
| NotificationDelivered | Notification Workflow | Timeline, Activity Log, Customer Portal | Low | System |
| NotificationFailed | Notification Workflow | Notification Workflow (retry), Timeline, Activity Log | Normal | System |
| AttachmentAdded | Attachment System | Timeline, Activity Log | Low | System |
| AttachmentRemoved | Attachment System | Timeline, Activity Log | Low | System |
| UserImpersonationStarted | Identity and Access | Activity Log | High | System |
| UserImpersonationEnded | Identity and Access | Activity Log | High | System |

---

# Event Naming Rules

All business events must follow the naming conventions defined here.

## PascalCase
Event names use PascalCase with no spaces, underscores, or hyphens.

Correct: `InvoicePublished`
Incorrect: `invoice_published`, `Invoice-Published`

## Past Tense Verb

Event names must use a past-tense verb to represent a completed fact.

Correct: `TicketAssigned`, `PaymentCompleted`, `DeviceRecovered`
Incorrect: `AssignTicket`, `CompletePayment`, `DeviceRecovery`

## Entity First

The name should begin with the primary business entity affected, followed by the past-tense action.

Pattern: `[Entity][PastTenseAction]`

Examples:
- `Invoice` + `Published` → `InvoicePublished`
- `Subscription` + `Activated` → `SubscriptionActivated`
- `Device` + `Recovered` → `DeviceRecovered`

## Specific and Unambiguous

Event names must be specific enough to distinguish between similar events.

Avoid generic names like `Updated` or `Changed`.

Correct: `PaymentCompleted`, `PaymentReallocated`
Incorrect: `PaymentChanged`

## No Technical Terms

Event names must reflect business language, not technical implementation details.

Correct: `InvoicePublished`
Incorrect: `InvoiceRecordInserted`

## No Abbreviations

Event names must use full words unless the abbreviation is a universally understood business term.

Correct: `SubscriptionActivated`
Incorrect: `SubActivated`

---

# Event Versioning

Event versioning is a conceptual design concern.

## Versioning Intent

Event schemas may evolve over time as the platform grows. Versioning ensures consumers can adapt to schema changes without breaking existing integrations.

## Additive Changes

Adding new optional fields to an event schema is a non-breaking change.

Consumers that do not yet handle new fields must continue processing without failure.

## Breaking Changes

Removing required fields, renaming fields, or changing field semantics constitutes a breaking change.

Breaking changes require a new event version.

## Version Identification

Events should carry a version identifier to allow consumers to handle multiple schema generations when transitional coexistence is required.

## Backward Compatibility

Where possible, new event versions should maintain backward compatibility to minimize consumer migration requirements.

## Deprecation

Event versions that are no longer required should be formally deprecated.

Deprecated versions remain consumable during the deprecation window before retirement.

## Event Store and Replay

When an event store is introduced, versioning must ensure that replayed historical events remain processable by current consumers.

Event versioning decisions must be recorded as architecture decisions when breaking changes are introduced.

---

# Event Lifecycle

Every business event passes through the following lifecycle stages.

## Raised

The event has been generated by the producer in response to a business state transition.

The event exists within the producer context and has not yet been made available to consumers.

A raised event is not yet consumable.

## Published

The event has been made available on the event distribution mechanism for consumers.

Once published, the event is immutable.

Publication is a commitment that the event represents a business fact that has occurred.

Events that fail to publish must be retried until they are successfully published or escalated for manual review.

## Consumed

One or more consumers have received and processed the event.

Consumption is independent per consumer. One consumer's processing failure does not prevent other consumers from processing.

Consumers must be designed to be idempotent so that duplicate delivery does not produce inconsistent outcomes.

Consumption acknowledgment must be recorded per consumer for auditability.

## Archived

The event has completed its active processing lifecycle and is retained for historical, audit, and replay purposes.

Archived events must remain accessible for reporting, compliance, and debugging.

Archived events must never be permanently deleted within the configured retention period.

---

# Future Extensions

The following event infrastructure extensions are reserved for future design.

## External Event Bus
Support routing of business events to and from an external event bus for multi-system integration scenarios.

## Kafka Integration
Support Apache Kafka as a high-throughput event distribution backbone for event-driven scalability.

## RabbitMQ Integration
Support RabbitMQ as a message broker for reliable event delivery with queue-based consumer management.

## MQTT Integration
Support MQTT for lightweight event publishing from network devices and IoT-class endpoints.

## Webhook Integration
Support delivering selected business events to external systems through configurable webhook endpoints.

## Cross-System Events
Support consuming events published by external partner or vendor systems into the platform event model.

## Event Replay
Support replaying historical events from an event store to rebuild consumer state or support debugging and analytics.

## Event Store
Support a dedicated event store as the authoritative immutable record of all business events, enabling full event sourcing and audit replay capabilities.
