# Purpose

This document defines the complete business lifecycle of an ISP subscription.

It is the authoritative workflow for subscription management and serves as the basis for billing, provisioning, monitoring, notifications, timeline events, customer portal visibility, and future automation.

This workflow follows the project architecture, glossary, and entity model as the source of truth for terminology and business behavior.

---

# Scope

This workflow is used by the following modules and business areas:

- Customer Management
- Billing
- Payments
- Provisioning
- Monitoring
- Notification Engine
- Customer Portal
- Timeline
- Activity Log

The workflow defines how a subscription moves from initial opportunity through activation, suspension, reactivation, and termination.

---

# Actor Matrix

The following matrix describes the primary actor responsibilities across the subscription lifecycle.

| Actor | Typical Lifecycle Responsibility | Responsible | Can Approve | Can Override | Read Only |
|---|---|---|---|---|---|
| Customer | Confirms survey, installation, service acceptance, and payment-related responses | Yes, for customer-facing steps | No | No | Yes, for status visibility |
| Sales | Qualifies prospects, schedules surveys, and advances pre-installation workflow | Yes, for lead and order handling | Yes, for pre-installation approvals | No | Yes |
| Customer Service | Coordinates customer communication, status updates, and exception handling | Yes, for service coordination | Yes, for customer-facing workflow approvals | Limited, by policy | Yes |
| Collector | Handles payment confirmation, overdue follow-up, and reactivation support | Yes, for payment-driven actions | Yes, for payment confirmation support | No | Yes |
| Technician | Executes installation, site validation, and field completion actions | Yes, for installation execution | Yes, for field completion confirmation | No | Yes |
| NOC | Supports provisioning, monitoring readiness, and restoration-related operations | Yes, for network coordination | Yes, for restoration-related approvals | Limited, by policy | Yes |
| System | Executes policy-driven automatic transitions and event generation | Yes, for automated transitions | No | Limited, by policy | Yes |
| Administrator | Governs exceptions, manual overrides, and policy-driven corrections | Yes, for controlled intervention | Yes | Yes | Yes |

The actor matrix describes responsibility by workflow phase rather than by individual record ownership.

---

# Lifecycle States

## Prospect

### Description
The customer has expressed interest in service, but no active service lifecycle has started yet.

### Entry Conditions
- Customer exists or is being created as part of a sales process.
- No installation or activation work has been completed.

### Exit Conditions
- A survey is scheduled.
- The opportunity is terminated or closed without service.

### Allowed Transitions
- Survey Scheduled
- Terminated

### Business Rules
- Prospect state does not generate billing.
- Prospect state does not start monitoring.
- Prospect state may be visible only to internal users.

### Generated Events
- Prospect Registered
- Prospect Updated

### Timeline Events
- Prospect created
- Prospect information updated
- Prospect closed without activation

### Notifications
- Internal sales or operations alerts, when configured.

### Customer Portal Visibility
- Not visible as an active service.
- May appear only as a service request status if the portal is used for lead-to-order tracking.

### Billing Impact
- No invoice generation.
- No billing cycle starts.

### Monitoring Impact
- No monitoring is started.

### Maximum Duration
Configurable (Default: Defined by business policy)

### Entry Validation
- Customer exists or lead record is available.
- Customer has expressed interest in service.
- No active subscription is already present for the same customer.

### Exit Validation
- Survey has been approved for scheduling, or the opportunity has been closed.

### Rollback
Return to the prior sales or lead state while preserving audit and timeline history.

### Automation
Semi Automatic

### Owner
Sales

### Metrics
- Lead conversion rate
- Prospect aging
- Survey scheduling rate

## Survey Scheduled

### Description
A site survey has been planned to confirm service feasibility and installation requirements.

### Entry Conditions
- Prospect has been qualified for survey.
- A survey request has been approved or scheduled by operations.

### Exit Conditions
- Survey is completed.
- Survey fails and requires exception handling.
- Opportunity is terminated.

### Allowed Transitions
- Survey Completed
- Terminated

### Business Rules
- Survey scheduling records the operational intent to inspect the service location.
- Survey work does not start billing.
- Survey work does not activate monitoring.

### Generated Events
- Survey Scheduled
- Survey Rescheduled

### Timeline Events
- Survey scheduled
- Survey rescheduled
- Survey assignment acknowledged

### Notifications
- Customer notified of survey schedule when applicable.
- Internal assignment notification to field staff.

### Customer Portal Visibility
- Visible as a pre-activation progress step when portal tracking is enabled.

### Billing Impact
- No invoice generation.

### Monitoring Impact
- No monitoring is started.

### Maximum Duration
Configurable (Default: 3 Days)

### Entry Validation
- Prospect exists.
- Survey has been approved for scheduling.
- Address and contact details are available.

### Exit Validation
- Survey has been completed, failed, or cancelled.

### Rollback
Return to Prospect while preserving the original survey request history.

### Automation
Semi Automatic

### Owner
Customer Service

### Metrics
- Average survey scheduling time
- Survey reschedule count
- Survey appointment confirmation rate

## Survey Completed

### Description
The service feasibility review has been completed and the subscription can proceed to installation planning.

### Entry Conditions
- Survey has been successfully finished.
- Survey findings support service continuation.

### Exit Conditions
- Installation is scheduled.
- Opportunity is terminated if the survey outcome is unfavorable.

### Allowed Transitions
- Installation Scheduled
- Terminated

### Business Rules
- Survey completion must be recorded before installation planning continues.
- Survey completion is an operational milestone, not a billing milestone.

### Generated Events
- Survey Completed
- Survey Outcome Recorded

### Timeline Events
- Survey completed
- Survey outcome documented
- Survey findings reviewed

### Notifications
- Customer notified of survey completion when applicable.
- Internal follow-up notification for installation planning.

### Customer Portal Visibility
- Visible as a completed pre-installation milestone when portal tracking is enabled.

### Billing Impact
- No invoice generation.

### Monitoring Impact
- No monitoring is started.

### Maximum Duration
Configurable (Default: Defined by business policy)

### Entry Validation
- Survey appointment has been completed.
- Survey outcome has been recorded.
- Service feasibility supports progression.

### Exit Validation
- Installation has been approved for scheduling or the opportunity is closed.

### Rollback
Return to Survey Scheduled if the survey record is corrected or reopened.

### Automation
Semi Automatic

### Owner
Sales

### Metrics
- Survey completion rate
- Survey failure count
- Time to installation approval

## Installation Scheduled

### Description
An installation appointment has been planned and assigned to the operational team.

### Entry Conditions
- Survey has been completed successfully.
- Installation capacity and scheduling are available.

### Exit Conditions
- Installation starts.
- Installation is cancelled or the opportunity is terminated.

### Allowed Transitions
- Installation In Progress
- Terminated

### Business Rules
- Installation scheduling is a controlled operational commitment.
- Installation scheduling does not start billing or monitoring.

### Generated Events
- Installation Scheduled
- Installation Rescheduled

### Timeline Events
- Installation scheduled
- Installation rescheduled
- Installation assignment acknowledged

### Notifications
- Customer notified of installation schedule.
- Technician or field team notified of assignment.

### Customer Portal Visibility
- Visible as an upcoming service milestone.

### Billing Impact
- No invoice generation.

### Monitoring Impact
- No monitoring is started.

### Maximum Duration
Configurable (Default: 7 Days)

### Entry Validation
- Survey Completed.
- Customer confirmed installation.
- Address verified.
- GPS location recorded.
- Package selected.
- Technician assigned.

### Exit Validation
- Installation appointment has been scheduled or the workflow has been cancelled.

### Rollback
Return to Survey Completed if the installation slot is rejected or cancelled before work starts.

### Automation
Semi Automatic

### Owner
Customer Service

### Metrics
- Installation scheduling lead time
- Installation reschedule count
- Appointment confirmation rate

## Installation In Progress

### Description
The installation visit is actively being executed at the customer location.

### Entry Conditions
- Installation appointment has started.
- Assigned technician has begun work.

### Exit Conditions
- Installation is completed.
- Installation fails and requires exception handling.
- Installation is terminated by operation.

### Allowed Transitions
- Installation Completed
- Terminated

### Business Rules
- Installation progress is tracked as an operational status.
- Billing does not start while installation is still in progress.

### Generated Events
- Installation Started
- Installation In Progress Updated

### Timeline Events
- Installation started
- Installation progress updated
- Site visit recorded

### Notifications
- Internal progress updates may be sent to operations.
- Customer notifications may be sent if status tracking is enabled.

### Customer Portal Visibility
- Visible as an active installation milestone.

### Billing Impact
- No invoice generation.

### Monitoring Impact
- No monitoring is started.

### Maximum Duration
Configurable (Default: Defined by operational capacity)

### Entry Validation
- Installation appointment has started.
- Technician is assigned and available.
- Required materials or access are ready.

### Exit Validation
- Installation has been completed or the field visit has been cancelled.

### Rollback
Return to Installation Scheduled if the visit is not started successfully.

### Automation
Manual

### Owner
Technician

### Metrics
- Installation duration
- Field retry count
- Installation failure count

## Installation Completed

### Description
Physical installation work has been completed and the subscription is ready for provisioning.

### Entry Conditions
- Installation work has been closed successfully.
- Required installation records have been captured.

### Exit Conditions
- Provisioning begins.
- The opportunity is terminated if post-installation review fails.

### Allowed Transitions
- Provisioning Pending
- Terminated

### Business Rules
- Installation completion is required before provisioning starts.
- Installation completion does not start billing or monitoring.

### Generated Events
- Installation Completed
- Installation Closure Recorded

### Timeline Events
- Installation completed
- Installation closure documented
- Ready for provisioning

### Notifications
- Customer notified that installation has been completed when applicable.
- Internal notification to provisioning or network operations.

### Customer Portal Visibility
- Visible as a completed installation milestone.

### Billing Impact
- No invoice generation yet unless a specific business policy requires pre-activation charges.

### Monitoring Impact
- No monitoring is started.

### Maximum Duration
Configurable (Default: Defined by business policy)

### Entry Validation
- Installation work has been completed.
- Required installation records have been captured.
- Installation outcome is ready for provisioning handoff.

### Exit Validation
- Provisioning has been requested or the workflow has been closed.

### Rollback
Return to Installation In Progress if completion was recorded in error and the field work is still open.

### Automation
Semi Automatic

### Owner
Technician

### Metrics
- Installation completion rate
- Handoff delay
- Post-installation defect count

## Provisioning Pending

### Description
The subscription is waiting for provisioning actions to be executed and confirmed.

### Entry Conditions
- Installation has been completed.
- Provisioning has been requested by the workflow.

### Exit Conditions
- Provisioning succeeds and activation can proceed.
- Provisioning fails and requires exception handling.
- The subscription is terminated.

### Allowed Transitions
- Active
- Terminated

### Business Rules
- Provisioning must complete before activation.
- Provisioning pending is not an active service state.
- Billing does not start until activation.

### Generated Events
- Provisioning Requested
- Provisioning Pending

### Timeline Events
- Provisioning requested
- Provisioning pending
- Provisioning handoff recorded

### Notifications
- Internal provisioning notification to responsible teams.
- Customer status notification when configured.

### Customer Portal Visibility
- Visible as a pending service activation step.

### Billing Impact
- No recurring invoice generation until activation.

### Monitoring Impact
- Monitoring is prepared but not active.

### Maximum Duration
Configurable (Default: 15 Minutes)

### Entry Validation
- Installation has been completed.
- Provisioning request has been created.
- Service is eligible for activation preparation.

### Exit Validation
- Provisioning has succeeded, failed, or the workflow has been terminated.

### Rollback
Return to Installation Completed if provisioning cannot proceed and the handoff is invalid.

### Automation
Fully Automatic

### Owner
Provisioning

### Metrics
- Provisioning time
- Retry count
- Provisioning failure count

## Active

### Description
The subscription is fully active and the customer receives billed service.

### Entry Conditions
- Provisioning has completed successfully.
- Activation has been confirmed.

### Exit Conditions
- The subscription becomes suspended.
- The subscription enters reactivation pending.
- The subscription is terminated.

### Allowed Transitions
- Suspended
- Reactivation Pending
- Terminated

### Business Rules
- Billing starts only after activation.
- Monitoring starts only after activation.
- One active subscription per customer must be enforced.
- Active service is the only lifecycle state that represents normal service delivery.

### Generated Events
- Subscription Activated
- Service Activated

### Timeline Events
- Subscription activated
- Billing started
- Monitoring started

### Notifications
- Customer notified of activation.
- Internal activation confirmation may be sent to operations.

### Customer Portal Visibility
- Full service visibility is available.

### Billing Impact
- Recurring billing begins.
- Invoice generation is enabled according to billing policy.

### Monitoring Impact
- Monitoring begins.
- Service health, availability, and operational status are tracked.

### Maximum Duration
No Maximum Duration

### Entry Validation
- Provisioning has completed successfully.
- Activation has been confirmed.
- One active subscription per customer rule has been satisfied.

### Exit Validation
- Suspension, reactivation, or termination conditions are met.

### Rollback
Return to Provisioning Pending if activation confirmation is invalidated before service use begins.

### Automation
Fully Automatic

### Owner
Billing

### Metrics
- Time to activate
- Activation success rate
- Active subscription count

## Suspended

### Description
The subscription is temporarily restricted and service delivery is paused according to billing or operational policy.

### Entry Conditions
- An overdue billing condition triggers suspension.
- A manual operational suspension is applied.

### Exit Conditions
- Reactivation is initiated.
- The subscription is terminated.

### Allowed Transitions
- Reactivation Pending
- Terminated

### Business Rules
- Suspended subscriptions cannot receive new invoices unless configured by billing policy.
- Suspended service must remain auditable and visible in historical records.
- Suspension must preserve the reason and source of the restriction.

### Generated Events
- Subscription Suspended
- Service Restricted

### Timeline Events
- Subscription suspended
- Suspension reason recorded
- Service restriction applied

### Notifications
- Customer suspension notification.
- Internal suspension confirmation notification.

### Customer Portal Visibility
- Visible with suspension reason, outstanding balance, or operational restriction details where allowed.

### Billing Impact
- New invoice generation is blocked unless billing policy explicitly allows it.
- Overdue balances remain open for collection.

### Monitoring Impact
- Monitoring may remain enabled for internal visibility, but the customer is marked as suspended rather than active.

### Maximum Duration
Unlimited until reactivated or terminated.

### Entry Validation
- Suspension policy applies.
- Suspension reason is recorded.
- Customer and internal stakeholders are notified as required.

### Exit Validation
- Reactivation conditions have been satisfied or the subscription is terminated.

### Rollback
Return to Active only when the suspension action is found invalid and the previous state remains authoritative.

### Automation
Semi Automatic

### Owner
Billing

### Metrics
- Suspension count
- Overdue suspension rate
- Average suspended duration

## Reactivation Pending

### Description
The subscription has met reactivation conditions and is waiting for confirmation or completion of restoration steps.

### Entry Conditions
- Payment confirmation has been received.
- Overdue balance is settled.
- Reactivation has been initiated by the workflow.

### Exit Conditions
- The subscription returns to active.
- Reactivation fails and the subscription remains suspended.
- The subscription is terminated.

### Allowed Transitions
- Active
- Suspended
- Terminated

### Business Rules
- Reactivation always creates timeline events.
- Reactivation must preserve the suspension history.
- Reactivation cannot bypass payment or policy requirements.

### Generated Events
- Reactivation Requested
- Reactivation Pending

### Timeline Events
- Reactivation requested
- Payment confirmed for reactivation
- Service restoration pending

### Notifications
- Customer notified that reactivation is in progress.
- Internal notification to billing or provisioning teams when needed.

### Customer Portal Visibility
- Visible as a restoration-in-progress state.

### Billing Impact
- Billing resumes when service returns to active.
- Collection-related balances remain traceable until restoration completes.

### Monitoring Impact
- Monitoring is prepared to resume normal service visibility.

### Maximum Duration
Configurable (Default: Defined by business policy)

### Entry Validation
- Payment confirmation has been received.
- Overdue balance is settled.
- Restoration has been requested or approved.

### Exit Validation
- Service is restored to Active, remains Suspended, or is terminated.

### Rollback
Return to Suspended while preserving all timeline and audit history.

### Automation
Semi Automatic

### Owner
Collector

### Metrics
- Reactivation time
- Reactivation success rate
- Payment-to-restoration delay

## Terminated

### Description
The subscription has been permanently closed and no further service lifecycle is allowed under the same subscription record.

### Entry Conditions
- Service is intentionally ended.
- Contract closure or account termination has been approved.
- Exceptional workflow forces permanent closure.

### Exit Conditions
- None.

### Allowed Transitions
- None.

### Business Rules
- Terminated subscriptions can never return to Active.
- Termination ends the service lifecycle for the subscription.
- Historical records must remain available for audit and reporting.

### Generated Events
- Subscription Terminated
- Service Closed

### Timeline Events
- Subscription terminated
- Final service closure recorded
- Historical record preserved

### Notifications
- Customer termination notification.
- Internal closure notification to billing, provisioning, and support as required.

### Customer Portal Visibility
- Visible only as historical, read-only information where portal policy allows.

### Billing Impact
- No future recurring invoices are generated.
- Final settlement, adjustment, or closing entries are handled according to billing policy.

### Monitoring Impact
- Monitoring is stopped or archived according to operational retention policy.

### Maximum Duration
No Maximum Duration

### Entry Validation
- Service closure has been approved.
- Final lifecycle decision is recorded.

### Exit Validation
- None.

### Rollback
No rollback to Active is permitted. Return to the last valid non-terminated state only if the termination was recorded in error and the record remains legally reversible.

### Automation
Manual

### Owner
Administrator

### Metrics
- Termination count
- Closure completion time
- Termination reversal requests

---

# State Transition Diagram

```mermaid
stateDiagram-v2
    [*] --> Prospect
    Prospect --> "Survey Scheduled" : normal flow
    "Survey Scheduled" --> "Survey Completed" : normal flow
    "Survey Completed" --> "Installation Scheduled" : normal flow
    "Installation Scheduled" --> "Installation In Progress" : normal flow
    "Installation In Progress" --> "Installation Completed" : normal flow
    "Installation Completed" --> "Provisioning Pending" : normal flow
    "Provisioning Pending" --> Active : normal flow
    Active --> Suspended : failure flow / policy suspension
    Suspended --> "Reactivation Pending" : normal flow
    "Reactivation Pending" --> Active : normal flow
    Prospect --> Terminated : cancellation
    "Survey Scheduled" --> Terminated : cancellation
    "Survey Completed" --> Terminated : cancellation
    "Installation Scheduled" --> Terminated : cancellation
    "Installation In Progress" --> Terminated : cancellation
    "Installation Completed" --> Terminated : termination
    "Provisioning Pending" --> Terminated : termination
    Active --> Terminated : termination
    Suspended --> Terminated : termination
    "Reactivation Pending" --> Suspended : failure flow
    "Provisioning Pending" --> "Installation Completed" : failure flow
    "Installation Scheduled" --> "Survey Completed" : manual override
    "Installation In Progress" --> "Installation Scheduled" : manual override
    Terminated --> [*]
```

---

# Event Matrix

| Transition | Trigger | Actor | Preconditions | Actions | Generated Events | Notifications | Timeline Entries | Audit Log Entries |
|---|---|---|---|---|---|---|---|---|
| Prospect -> Survey Scheduled | Survey request approved | Sales or operations staff | Prospect exists and survey is required | Schedule survey assignment | Survey Scheduled | Customer and internal scheduling notice | Survey scheduled | Survey scheduling record |
| Survey Scheduled -> Survey Completed | Survey result submitted | Field staff | Survey visit completed | Record survey outcome and feasibility status | Survey Completed | Customer completion notice if enabled | Survey completed | Survey outcome submitted |
| Survey Scheduled -> Terminated | Opportunity closed | Sales or operations staff | Prospect is no longer pursued | Close opportunity and preserve history | Opportunity Closed | Optional closure notice | Opportunity closed without service | Closure decision recorded |
| Survey Completed -> Installation Scheduled | Installation planning approved | Operations staff | Survey supports service continuation | Schedule installation visit | Installation Scheduled | Customer installation notice | Installation scheduled | Installation planning recorded |
| Installation Scheduled -> Installation In Progress | Technician starts work | Technician | Installation slot is active | Begin site work and update status | Installation Started | Internal progress notice | Installation started | Installation start recorded |
| Installation In Progress -> Installation Completed | Installation closure submitted | Technician | Installation work completed successfully | Close installation and hand off to provisioning | Installation Completed | Completion notice if enabled | Installation completed | Installation closure recorded |
| Installation In Progress -> Terminated | Installation cancelled | Operations staff | Service should no longer continue | Abort installation and close workflow | Installation Cancelled | Cancellation notice as appropriate | Installation cancelled | Cancellation reason recorded |
| Installation Completed -> Provisioning Pending | Provisioning request created | Operations or automation | Installation is complete | Queue provisioning handoff | Provisioning Requested | Internal provisioning notice | Provisioning requested | Provisioning handoff recorded |
| Provisioning Pending -> Active | Provisioning confirmation received | Provisioning system or operations | Provisioning completed successfully | Mark service active and start billing and monitoring | Subscription Activated | Customer activation notice | Activation recorded | Activation confirmation recorded |
| Provisioning Pending -> Terminated | Service abandoned | Operations staff | Service will not be activated | Close lifecycle before activation | Service Closed | Closure notice as appropriate | Lifecycle closed before activation | Closure recorded |
| Active -> Suspended | Overdue threshold or manual restriction | Billing engine or operations staff | Suspension policy applies | Restrict service and record reason | Subscription Suspended | Suspension notice | Suspension recorded | Suspension decision logged |
| Suspended -> Reactivation Pending | Payment confirmed or restoration approved | Billing or operations staff | Overdue balance settled or policy allows restoration | Start reactivation workflow | Reactivation Requested | Reactivation notice | Reactivation requested | Reactivation request logged |
| Reactivation Pending -> Active | Restoration confirmed | Provisioning or operations staff | Restoration steps completed | Restore service and resume billing and monitoring | Service Restored | Customer restoration notice | Reactivation completed | Restoration confirmation recorded |
| Reactivation Pending -> Suspended | Restoration fails | Provisioning or operations staff | Reactivation cannot be completed | Return subscription to suspended state | Reactivation Failed | Customer failure notice | Reactivation failed | Failure reason recorded |
| Active -> Terminated | Contract ends or manual closure | Operations staff | Termination is approved | Close subscription permanently | Subscription Terminated | Termination notice | Final closure recorded | Termination approval recorded |
| Suspended -> Terminated | Permanent closure from suspended state | Operations staff | Subscription will not be restored | Close subscription permanently | Subscription Terminated | Termination notice | Final closure recorded | Termination approval recorded |

---

# Business Rules

- One active subscription per customer.
- Billing starts only after activation.
- Monitoring starts only after activation.
- Provisioning must complete before activation.
- Suspended subscriptions cannot receive new invoices unless configured by billing policy.
- Terminated subscriptions can never return to Active.
- Reactivation always creates timeline events.

Additional rules:
- Each subscription must have a single authoritative lifecycle state.
- State changes must be traceable through timeline and activity records.
- Billing, provisioning, and monitoring must not advance independently of lifecycle policy.
- Customer-facing visibility must reflect the authoritative lifecycle state.
- Manual overrides must never erase the historical trail of state changes.

---

# Exception Handling

## Survey Failed
A survey failure means the site cannot be confirmed for progression under current conditions.

Handling:
- Record the failure reason.
- Notify internal operations.
- Decide whether to reschedule, revise the order, or close the opportunity.

## Installation Failed
An installation failure means the field work could not be completed successfully.

Handling:
- Record the failure reason.
- Notify field operations and support teams.
- Decide whether to retry, reschedule, or terminate the subscription workflow.

## Provisioning Failed
A provisioning failure means activation prerequisites were met but service restoration or activation could not be completed.

Handling:
- Record the failure reason.
- Notify provisioning and operations teams.
- Keep the subscription from entering Active until the failure is resolved.

## Activation Failed
An activation failure means the service could not move into Active after provisioning or restoration.

Handling:
- Preserve the prior state or return the subscription to Suspended if reactivation is in progress.
- Record the activation failure reason.
- Notify billing, provisioning, and operations as needed.

## Suspension Failed
A suspension failure means the system could not enforce service restriction after a valid suspension decision.

Handling:
- Preserve the financial suspension state.
- Record the operational failure.
- Notify provisioning and operations to retry or resolve manually.

## Unexpected Rollback
An unexpected rollback means a state transition must be reversed because the initiating process failed after partial completion.

Handling:
- Restore the last valid state.
- Record the rollback reason and point of failure.
- Notify internal teams so the workflow can be reconciled.

## Manual Override
A manual override is an explicit human decision to continue, pause, or close the workflow outside normal automation.

Handling:
- Require audit logging.
- Preserve the original state transition history.
- Record the reason for the override.
- Notify affected internal teams.

---

# Integration Points

## Billing Engine
- Starts billing only when the subscription becomes Active.
- Evaluates overdue rules for suspension.
- Controls whether suspended subscriptions may receive invoices.
- Handles final settlement behavior for terminated subscriptions according to policy.

## Provisioning
- Receives installation completion handoff.
- Executes activation or restoration actions.
- Reports provisioning success or failure.
- Supports suspension, reactivation, and termination workflows where relevant.

## Monitoring
- Starts monitoring only after activation.
- Stops or archives monitoring when the subscription is terminated.
- May keep internal visibility during suspension for operational awareness.

## Notification Engine
- Sends customer and internal notifications for schedule, activation, suspension, reactivation, and termination events.
- Uses lifecycle events as the basis for notification triggers.

## Timeline
- Records every major lifecycle transition.
- Captures both business progress and exception handling.
- Preserves reactivation history explicitly.

## Customer Portal
- Shows the current lifecycle state to the customer according to portal policy.
- Exposes progress before activation and service status after activation.
- Reflects suspension, reactivation, and termination states clearly.

## Settings Engine
- Controls policy-driven behavior such as suspension timing, reactivation rules, and invoice eligibility during suspension.
- Governs whether certain steps require human approval or may proceed automatically.

---

# Automation

## Automatic Transitions
The following transitions should occur automatically when the governing conditions are met:
- Survey Scheduled -> Survey Completed when the survey result is submitted.
- Installation Scheduled -> Installation In Progress when the appointment starts.
- Installation In Progress -> Installation Completed when the field work is closed successfully.
- Installation Completed -> Provisioning Pending when the workflow hands off to provisioning.
- Provisioning Pending -> Active when provisioning is confirmed.
- Active -> Suspended when policy-based overdue conditions are met.
- Suspended -> Reactivation Pending when payment confirmation or restoration conditions are satisfied.
- Reactivation Pending -> Active when restoration is confirmed.

## Human Approval Transitions
The following transitions require human approval or explicit operational action:
- Prospect -> Survey Scheduled
- Survey Scheduled -> Terminated
- Survey Completed -> Installation Scheduled
- Installation Scheduled -> Terminated
- Installation In Progress -> Terminated
- Provisioning Pending -> Terminated
- Active -> Terminated
- Suspended -> Terminated
- Manual Override actions

---

# Future Extensions

The following lifecycle extensions are reserved for future design:

## Service Upgrade
Support moving a subscription to a higher service package without ending the service relationship.

## Service Downgrade
Support moving a subscription to a lower service package while preserving continuity where policy allows.

## Address Relocation
Support changing the service location while preserving business history and billing traceability.

## Temporary Suspension
Support a non-permanent pause distinct from policy-based overdue suspension.

## Multi-Service Support
Support multiple service types under a customer account while preserving clear service-level lifecycles.

---

# Workflow Principles

- Every state transition must be auditable.
- Every transition generates timeline events.
- Every transition may generate notifications.
- Rollback never removes historical records.
- Automation should be preferred whenever business rules allow.
- Human approval should only be required when business risk justifies it.
- All configurable behavior must be controlled through the Settings Engine.
