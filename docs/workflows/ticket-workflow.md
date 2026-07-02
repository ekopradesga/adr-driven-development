# Purpose

This document defines the complete Ticket Workflow responsible for managing operational work from issue detection through verification and closure.

The Ticket Workflow coordinates operational activities but never owns business entities such as invoices, payments, subscriptions, or monitoring records.

The Ticket Workflow is responsible for:

- Receiving and tracking operational issues
- Classifying and prioritizing tickets
- Assigning tickets to the appropriate owner
- Coordinating field execution and resolution
- Managing SLA targets and escalation
- Capturing evidence, notes, and attachments
- Verifying resolution outcomes
- Maintaining a full auditable history

The Ticket Workflow is NOT responsible for:

- Generating invoices
- Receiving payments
- Provisioning services
- Executing monitoring changes
- Modifying subscription state directly

---

# Scope

This workflow integrates with:

- Network Monitoring Workflow
- Provisioning Workflow
- Subscription Lifecycle
- Billing Workflow
- Payment Workflow
- Collector Workflow
- Notification Workflow
- Timeline
- Activity Log
- Customer Portal
- Attachment System
- Settings Engine
- Reporting

---

# Ticket Principles

## Single Source of Operational Truth

A ticket is the authoritative operational record for an issue or task.

All actions, communications, evidence, and decisions related to an operational work item must be captured on the ticket.

---

## Multi Source

Tickets may originate from:

- Customer (via Customer Portal or Customer Service)
- Network Monitoring Workflow (device warning or critical event)
- Collector Workflow (field exception requiring technical resolution)
- Technician (field-initiated issue)
- Customer Service Representative
- Administrator
- Automation Rules

Source identity must be preserved for audit and categorization purposes.

---

## Assignment Based

Each ticket has exactly one current owner at any point in its lifecycle.

Assignment history is fully preserved and immutable.

Reassignment must be auditable with reason and actor capture.

---

## SLA Driven

Response and resolution targets are configurable through Settings Engine.

SLA tracking must be aware of pause conditions such as Waiting Customer, Waiting Material, and Waiting Third Party.

SLA breach events must be traceable and must trigger escalation according to policy.

---

## Status Driven

Operational behavior is controlled by lifecycle state.

State transitions enforce business rules and generate traceable events.

Unauthorized transitions are prevented by the state machine.

---

## Full Auditability

Every ticket action generates:

- Timeline event
- Activity log entry
- Attachment reference where applicable
- Note or comment record where applicable

---

# Actor Matrix

| Actor | Responsible | Approver | Override | Read Only |
|---|---|---|---|---|
| Customer | Yes, for issue submission, confirmation, and resolution acknowledgment | No | No | Yes, for status and history |
| Collector | Yes, for field exception escalation and billing-related ticket creation | No | No | Yes |
| Technician | Yes, for assignment acceptance, field execution, resolution capture, and evidence submission | No | No | Yes |
| Customer Service | Yes, for ticket creation, triage support, customer communication, and escalation coordination | Yes, for customer-facing decisions | Limited, by policy | Yes |
| NOC | Yes, for monitoring-sourced ticket initiation and network-related resolution support | Yes, for network-side approvals | Limited, by policy | Yes |
| Finance | No | No | No | Yes |
| Supervisor | Yes, for assignment oversight, SLA escalation management, and resolution approval | Yes | Yes, by policy | Yes |
| Administrator | Yes, for controlled intervention, exception resolution, and policy governance | Yes | Yes | Yes |
| System | Yes, for automated ticket creation, SLA evaluation, escalation triggers, and state orchestration | No | Limited, by policy | Yes |

The actor matrix describes workflow responsibility rather than ticket record ownership.

---

# Ticket Lifecycle

## Draft

### Description
A ticket has been created but not yet submitted for processing. Draft tickets may be created by the customer portal, internal users, or automation rules before formal submission.

### Entry Validation
- Ticket creator identity is known.
- Minimum required ticket fields are present.
- Ticket source is traceable.

### Exit Validation
- Ticket has been formally submitted.
- Required fields meet submission criteria.
- Draft is discarded or cancelled by actor decision.

### Allowed Transitions
- Open
- Cancelled

### Business Rules
- Draft tickets are not visible in operational queues.
- Draft tickets do not generate SLA targets.
- Draft tickets may be abandoned without formal cancellation when policy allows.
- Automation-created drafts must be submitted automatically unless policy requires review.

### Generated Events
- Ticket Draft Created
- Ticket Draft Updated

### Timeline
- Ticket draft created
- Draft fields recorded
- Draft submission pending

### Notifications
- No customer notification until submission.
- Optional internal notification for pending draft review when configured.

### Customer Portal Visibility
- Visible only to the customer who created the draft.

### Automation Level
- Manual or Semi Automatic

### Owner
- Ticket Creator

### Metrics
- Draft creation count
- Draft-to-open conversion rate
- Draft abandonment rate

### SLA Impact
- SLA does not start in Draft state.

### Rollback Policy
Return to draft content if submission fails validation. Preserve all draft revision history.

### Maximum Duration
Configurable through Settings Engine.

---

## Open

### Description
The ticket has been formally submitted and is awaiting triage. Open tickets are visible in the operational queue and require classification.

### Entry Validation
- Ticket has been submitted.
- Required fields are complete.
- Ticket source and category context are available.

### Exit Validation
- Triage has been completed.
- Ticket is invalid or duplicate and is cancelled.

### Allowed Transitions
- Triaged
- Cancelled

### Business Rules
- Every submitted ticket must be triaged.
- Open tickets must be visible in the triage queue.
- Duplicate detection must be applied at this stage.
- Response SLA timer starts when the ticket enters Open state.

### Generated Events
- Ticket Opened
- Ticket Submitted

### Timeline
- Ticket opened
- Ticket received
- SLA response timer started

### Notifications
- Customer submission confirmation notification.
- Internal triage queue notification.

### Customer Portal Visibility
- Customer can see ticket as submitted and pending review.

### Automation Level
- Fully Automatic

### Owner
- Customer Service

### Metrics
- Open ticket count
- Time in open queue
- Triage queue depth

### SLA Impact
- Response SLA starts. SLA clock is running.

### Rollback Policy
Return to Draft if the ticket was opened in error and no triage has begun. Preserve all submission history.

### Maximum Duration
Configurable through Settings Engine.

---

## Triaged

### Description
The ticket has been reviewed, classified, prioritized, and is ready for assignment to a responsible technician or team.

### Entry Validation
- Ticket is open.
- Category is assigned.
- Priority is assigned.
- Triage actor is identified.

### Exit Validation
- Ticket is assigned to a responsible owner.
- Ticket is cancelled if invalid.

### Allowed Transitions
- Assigned
- Cancelled

### Business Rules
- Category and priority must be assigned during triage.
- SLA targets are determined at triage based on category and priority.
- Duplicate tickets identified during triage must be merged or cancelled with reference to the primary ticket.
- Triage must not be bypassed except through authorized automated triage policy.

### Generated Events
- Ticket Triaged
- Priority Assigned
- Category Assigned

### Timeline
- Ticket triaged
- Category recorded
- Priority recorded
- SLA targets determined

### Notifications
- Internal notification to assignment queue or supervisor.

### Customer Portal Visibility
- Customer sees ticket as under review.

### Automation Level
- Semi Automatic

### Owner
- Customer Service

### Metrics
- Triage duration
- Category distribution
- Priority distribution

### SLA Impact
- SLA targets are confirmed at triage. Response SLA continues running.

### Rollback Policy
Return to Open if triage is incomplete or incorrectly applied. Preserve triage attempt records.

### Maximum Duration
Configurable through Settings Engine.

---

## Assigned

### Description
The ticket has been assigned to a specific technician or team owner for execution.

### Entry Validation
- Triage has been completed.
- Assignee identity is valid and available.
- Assignment policy has been applied.

### Exit Validation
- Assignee has accepted the ticket.
- Ticket is reassigned to a different owner.
- Ticket is cancelled.

### Allowed Transitions
- Accepted
- Triaged
- Cancelled

### Business Rules
- Each ticket has exactly one active assignee.
- Assignment must follow the configured assignment policy.
- Reassignment must preserve the full assignment history with reason capture.
- Priority rules may override default assignment order.
- Assignment may be manual, area-based, skill-based, or workload-balanced.

### Generated Events
- Ticket Assigned
- Assignment Confirmed

### Timeline
- Ticket assigned
- Assignee notified
- Assignment policy applied

### Notifications
- Technician assignment notification.
- Supervisor notification when configured or escalation policy requires.

### Customer Portal Visibility
- Customer sees ticket as assigned.

### Automation Level
- Semi Automatic

### Owner
- Supervisor

### Metrics
- Assignment acceptance rate
- Reassignment count
- Assignment turnaround time
- Workload distribution

### SLA Impact
- SLA continues running. Response SLA may be marked met when assignment is confirmed.

### Rollback Policy
Return to Triaged if assignment cannot be completed or is revoked. Preserve all assignment events.

### Maximum Duration
Configurable through Settings Engine.

---

## Accepted

### Description
The assigned technician has accepted the ticket and committed to resolving it.

### Entry Validation
- Ticket is assigned.
- Assignee confirms acceptance.
- Required information for execution is available.

### Exit Validation
- Technician begins travel to site.
- Ticket enters a waiting state.
- Ticket is returned for reassignment.

### Allowed Transitions
- On Route
- Waiting Material
- Waiting Third Party
- Triaged

### Business Rules
- Acceptance is an explicit technician commitment.
- Acceptance must be timestamped and attributed to the technician.
- If a technician cannot accept, the ticket must be returned to Triaged for reassignment.
- Field preparation such as equipment and material checks may occur in this state.

### Generated Events
- Ticket Accepted
- Acceptance Commitment Recorded

### Timeline
- Ticket accepted by technician
- Acceptance commitment recorded
- Field preparation status updated

### Notifications
- Customer notification that a technician has been assigned and has accepted the ticket.
- Internal readiness notification.

### Customer Portal Visibility
- Customer can see technician has accepted and work is being prepared.

### Automation Level
- Manual

### Owner
- Technician

### Metrics
- Acceptance time from assignment
- Rejection or return rate
- Pre-execution preparation duration

### SLA Impact
- SLA continues running. Acceptance time is tracked as a sub-metric.

### Rollback Policy
Return to Assigned if acceptance is reversed before travel begins. Preserve acceptance attempt records.

### Maximum Duration
Configurable through Settings Engine.

---

## On Route

### Description
The technician is traveling to the customer location to perform field work.

### Entry Validation
- Ticket has been accepted.
- Technician confirms route start.
- Destination and task context are available.

### Exit Validation
- Technician arrives at site and begins work.
- Route is interrupted and technician returns to Accepted state.

### Allowed Transitions
- In Progress
- Accepted

### Business Rules
- Route start must be recorded with timestamp.
- GPS tracking may be captured when policy allows.
- Route interruption must be recorded with reason.
- Customer notification of imminent arrival may be sent by policy.

### Generated Events
- Route Started
- Route Progress Updated

### Timeline
- Technician on route
- Route start recorded
- ETA context captured

### Notifications
- Optional customer arrival notification.
- Internal route status update.

### Customer Portal Visibility
- Customer may see technician on route status when policy allows.

### Automation Level
- Semi Automatic

### Owner
- Technician

### Metrics
- Route start latency from acceptance
- Route completion ratio
- Route interruption count
- Average travel time

### SLA Impact
- SLA continues running.

### Rollback Policy
Return to Accepted if route is cancelled before arrival. Preserve route history.

### Maximum Duration
Configurable through Settings Engine.

---

## In Progress

### Description
The technician has arrived at the customer location and is actively performing the work required to resolve the ticket.

### Entry Validation
- Technician has arrived at site.
- Arrival is recorded.
- Customer identity is verified when required by policy.

### Exit Validation
- Work is completed and resolution is recorded.
- A blocking condition is encountered and ticket enters a waiting state.
- Escalation or reassignment is required.

### Allowed Transitions
- Waiting Customer
- Waiting Material
- Waiting Third Party
- Resolved
- Accepted

### Business Rules
- Arrival must be timestamped and attributed.
- Customer identity verification may be required by category policy.
- Work updates, notes, and evidence must be capturable in this state.
- Photo and signal evidence may be required for resolution depending on ticket category.
- Escalation to a different technician must preserve all work-in-progress records.

### Generated Events
- Work Started
- Work Progress Updated

### Timeline
- Technician arrived at site
- Work started
- Progress update recorded

### Notifications
- Internal progress notification when configured.
- Customer update notification when significant progress is made.

### Customer Portal Visibility
- Customer can see technician is actively working.

### Automation Level
- Manual

### Owner
- Technician

### Metrics
- In-progress duration
- Work update frequency
- Escalation from in-progress count

### SLA Impact
- Resolution SLA is actively running. Time in In Progress contributes to resolution measurement.

### Rollback Policy
Return to Accepted or re-enter Assigned for escalation while preserving all work records captured in this state.

### Maximum Duration
Configurable through Settings Engine.

---

## Waiting Customer

### Description
Work has been paused because the customer is unavailable, must perform a required action, or must be physically present before work can continue.

### Entry Validation
- In-progress work has been attempted.
- Customer unavailability or required customer action is recorded.

### Exit Validation
- Customer becomes available and work can resume.
- Ticket is cancelled due to persistent unavailability by policy.

### Allowed Transitions
- In Progress
- Cancelled

### Business Rules
- Waiting Customer reason must be recorded.
- SLA clock must pause according to configured policy when ticket enters this state.
- A maximum waiting duration must be configurable before escalation or cancellation.
- Customer notification must be sent to request availability.

### Generated Events
- Work Paused Waiting Customer
- Customer Notification Sent

### Timeline
- Work paused
- Waiting customer recorded
- Customer contact attempted

### Notifications
- Customer notification requesting availability or action.
- Internal notification to customer service for follow-up.

### Customer Portal Visibility
- Customer sees that action or availability is required from them.

### Automation Level
- Semi Automatic

### Owner
- Customer Service

### Metrics
- Waiting customer frequency
- Customer response time
- SLA pause duration
- Timeout-to-cancellation rate

### SLA Impact
- Resolution SLA is paused while in Waiting Customer state according to configured pause policy.

### Rollback Policy
Return to In Progress records as of last valid work state when customer becomes available. Preserve all wait records.

### Maximum Duration
Configurable through Settings Engine.

---

## Waiting Material

### Description
Work has been paused because required materials, equipment, or parts are not available at the site or in the field inventory.

### Entry Validation
- In-progress work has been attempted or accepted work revealed material gap.
- Missing material is identified and recorded.

### Exit Validation
- Required materials are available and work can resume.
- Ticket is reassigned to a different technician with available materials.

### Allowed Transitions
- In Progress
- Assigned

### Business Rules
- Missing material must be recorded with specifics.
- SLA clock must pause according to configured policy when waiting for materials.
- Procurement or inventory escalation must be traceable.
- Return to Assigned is allowed when a different technician with the required materials can be assigned.

### Generated Events
- Work Paused Waiting Material
- Material Gap Recorded

### Timeline
- Work paused
- Material gap recorded
- Procurement escalation initiated

### Notifications
- Internal notification to operations or inventory management.
- Supervisor notification when waiting duration exceeds threshold.

### Customer Portal Visibility
- Customer sees that work is pending material procurement. Internal material details are not exposed.

### Automation Level
- Semi Automatic

### Owner
- Supervisor

### Metrics
- Waiting material frequency
- Material gap resolution time
- SLA pause duration
- Material availability rate

### SLA Impact
- Resolution SLA may be paused while in Waiting Material state according to configured pause policy.

### Rollback Policy
Return to In Progress or Accepted context when material is available. Preserve all wait records.

### Maximum Duration
Configurable through Settings Engine.

---

## Waiting Third Party

### Description
Work has been paused because a dependency on an external party, vendor, or third-party provider must be resolved before work can continue.

### Entry Validation
- In-progress work has been attempted or pre-execution planning revealed third-party dependency.
- Third-party dependency and escalation action are recorded.

### Exit Validation
- Third-party response or completion is received and work can resume.
- Ticket is reassigned due to dependency resolution path change.

### Allowed Transitions
- In Progress
- Assigned

### Business Rules
- Third-party dependency must be identified and recorded.
- SLA clock must pause according to configured policy when waiting on third parties.
- Escalation to supervisor is required when waiting duration exceeds configured threshold.
- Third-party identity and interaction context must be preserved for audit.

### Generated Events
- Work Paused Waiting Third Party
- Third Party Dependency Recorded

### Timeline
- Work paused
- Third party dependency recorded
- Escalation context captured

### Notifications
- Internal notification to supervisor and responsible team.
- Optional customer notification indicating external dependency.

### Customer Portal Visibility
- Customer sees that work is pending external resolution. Third-party details are not exposed.

### Automation Level
- Semi Automatic

### Owner
- Supervisor

### Metrics
- Waiting third party frequency
- Third-party resolution time
- SLA pause duration
- Escalation rate

### SLA Impact
- Resolution SLA may be paused while in Waiting Third Party state according to configured pause policy.

### Rollback Policy
Return to In Progress or Accepted context when dependency is resolved. Preserve all wait records.

### Maximum Duration
Configurable through Settings Engine.

---

## Resolved

### Description
The technician has completed the required work and has recorded a resolution outcome with supporting evidence.

### Entry Validation
- Work has been completed.
- Resolution summary is recorded.
- Required evidence has been attached when policy mandates it.
- Resolution actor is identified.

### Exit Validation
- Resolution moves to Verification when required by category or policy.
- Resolution is auto-closed when verification is not required.

### Allowed Transitions
- Verification
- Closed

### Business Rules
- Resolution must include a written summary.
- Photo or signal measurement evidence may be required by category policy.
- GPS capture at resolution may be required by policy.
- Resolution does not automatically close the ticket when verification is configured.
- Auto-close without verification is allowed only when category policy permits.

### Generated Events
- Ticket Resolved
- Resolution Evidence Captured

### Timeline
- Ticket resolved
- Resolution summary recorded
- Evidence attached

### Notifications
- Customer notification that the issue has been resolved and confirmation may be requested.
- Internal notification to verification owner or supervisor.

### Customer Portal Visibility
- Customer sees resolution summary and is prompted to confirm or dispute.

### Automation Level
- Manual

### Owner
- Technician

### Metrics
- First-contact resolution rate
- Resolution-to-close duration
- Evidence completeness rate

### SLA Impact
- Resolution SLA timer stops when the ticket enters Resolved state.

### Rollback Policy
Return to In Progress if resolution is determined to be incomplete or incorrect. Preserve resolution attempt records.

### Maximum Duration
Configurable through Settings Engine.

---

## Verification

### Description
The resolution is under active verification by the customer, customer service, or a quality assurance process before the ticket is closed.

### Entry Validation
- Ticket has been resolved.
- Verification is required by category or policy configuration.
- Verification actor or policy is identified.

### Exit Validation
- Verification is confirmed and ticket closes.
- Verification fails and ticket is reopened.

### Allowed Transitions
- Closed
- Reopened

### Business Rules
- Verification may be performed by the customer, customer service, or an automated check.
- Verification timeout must be configurable. Auto-close may occur if the customer does not respond within the configured window.
- Verification failure must produce a Reopened outcome rather than directly returning to In Progress.
- Verification evidence must be recorded.

### Generated Events
- Verification Started
- Verification Outcome Recorded

### Timeline
- Verification started
- Verification outcome recorded
- Confirmation or dispute captured

### Notifications
- Customer prompt to confirm resolution.
- Internal notification of verification outcome.

### Customer Portal Visibility
- Customer is prompted to confirm or dispute the resolution.

### Automation Level
- Semi Automatic

### Owner
- Customer Service

### Metrics
- Verification duration
- Verification confirmation rate
- Verification failure rate
- Auto-close rate

### SLA Impact
- SLA measurement is complete. Verification duration is tracked as a post-resolution metric.

### Rollback Policy
Verification cannot be rolled back once completed. Disputes must proceed through Reopened state. Preserve verification records.

### Maximum Duration
Configurable through Settings Engine.

---

## Closed

### Description
The ticket has been fully resolved and verified. All work is complete and the ticket is no longer active.

### Entry Validation
- Ticket is resolved.
- Verification is complete or auto-close policy conditions are met.
- Closure reason is recorded.

### Exit Validation
- None.

### Allowed Transitions
- Reopened

### Business Rules
- Closed tickets are available for audit, reporting, and reopening.
- Closure must preserve the full ticket history.
- Closed tickets must not be directly edited. Corrections require authorized exception handling.
- Customer satisfaction prompts may be sent after closure.

### Generated Events
- Ticket Closed
- Closure Reason Recorded

### Timeline
- Ticket closed
- Closure reason recorded
- Final state preserved

### Notifications
- Customer closure notification.
- Optional customer satisfaction prompt.
- Internal closure confirmation.

### Customer Portal Visibility
- Customer sees ticket as closed with full history visible.

### Automation Level
- Semi Automatic

### Owner
- Supervisor

### Metrics
- Closure rate
- Average end-to-end resolution time
- Customer satisfaction score (future)

### SLA Impact
- Final SLA compliance outcome is recorded at closure.

### Rollback Policy
Closure reversal requires explicit reopening. Closed records must be preserved.

### Maximum Duration
No Maximum Duration.

---

## Cancelled

### Description
The ticket has been cancelled before resolution. Cancellation may be triggered by policy, duplicate detection, customer withdrawal, or authorized operational decision.

### Entry Validation
- Cancellation reason is identified.
- Authorization requirements are satisfied when manual cancellation is initiated.
- Cancellation is traceable to a rule, policy outcome, or actor decision.

### Exit Validation
- None.

### Allowed Transitions
- None.

### Business Rules
- Cancellation must preserve the reason.
- Duplicate-related cancellations must reference the primary ticket.
- Cancelled tickets must remain available for audit and reporting.
- Cancellation triggered by operator action must require authorization.

### Generated Events
- Ticket Cancelled
- Cancellation Reason Recorded

### Timeline
- Ticket cancelled
- Cancellation reason recorded
- Cancellation outcome preserved

### Notifications
- Customer cancellation notification.
- Internal cancellation confirmation.

### Customer Portal Visibility
- Customer sees ticket as cancelled with reason shown according to visibility policy.

### Automation Level
- Semi Automatic

### Owner
- Administrator

### Metrics
- Cancellation count
- Cancellation reason distribution
- Duplicate-driven cancellation rate

### SLA Impact
- SLA tracking stops at cancellation. Cancellation before response SLA may exclude the ticket from SLA metrics by policy.

### Rollback Policy
Cancellation is final. Historical cancellation records are preserved. A new ticket must be created if the issue recurs.

### Maximum Duration
No Maximum Duration.

---

## Reopened

### Description
A previously closed or resolved ticket has been reopened due to a recurring issue, failed verification, or customer dispute.

### Entry Validation
- Ticket was in Closed or Verification state.
- Reopen reason is recorded.
- Reopen actor is identified.

### Exit Validation
- Ticket re-enters triage or assignment for continued resolution.

### Allowed Transitions
- Triaged
- Assigned

### Business Rules
- Reopened tickets must preserve the full prior history.
- Reopen reason must be captured.
- Reopen count is tracked as a quality metric.
- Reopen SLA may be treated independently from the original SLA by policy.
- Assignment at reopen may follow the original assignee or be reassigned by policy.

### Generated Events
- Ticket Reopened
- Reopen Reason Recorded

### Timeline
- Ticket reopened
- Reopen reason recorded
- Prior history preserved

### Notifications
- Customer notification that the ticket has been reopened.
- Internal notification to triage queue or original assignee.

### Customer Portal Visibility
- Customer sees ticket as reopened with updated status.

### Automation Level
- Semi Automatic

### Owner
- Customer Service

### Metrics
- Reopen count
- Reopen reason distribution
- Reopen-to-close cycle time

### SLA Impact
- A new SLA window begins or is extended according to reopen SLA policy.

### Rollback Policy
Reopening is a forward action. The prior closed state is preserved in history. No rollback of the reopen decision.

### Maximum Duration
No Maximum Duration.

---

# State Transition Diagram

```mermaid
stateDiagram-v2
    [*] --> Draft
    Draft --> Open : submitted
    Draft --> Cancelled : discarded

    Open --> Triaged : triage completed
    Open --> Cancelled : invalid or duplicate

    Triaged --> Assigned : assignment made
    Triaged --> Cancelled : invalid after triage

    Assigned --> Accepted : technician accepts
    Assigned --> Triaged : reassignment required
    Assigned --> Cancelled : cancelled before acceptance

    Accepted --> "On Route" : technician departs
    Accepted --> "Waiting Material" : material gap identified
    Accepted --> "Waiting Third Party" : dependency identified
    Accepted --> Triaged : escalation or reassignment

    "On Route" --> "In Progress" : technician arrives
    "On Route" --> Accepted : route cancelled or rescheduled

    "In Progress" --> "Waiting Customer" : customer unavailable
    "In Progress" --> "Waiting Material" : material gap identified
    "In Progress" --> "Waiting Third Party" : dependency identified
    "In Progress" --> Resolved : work completed
    "In Progress" --> Accepted : escalation or reassignment

    "Waiting Customer" --> "In Progress" : customer available
    "Waiting Customer" --> Cancelled : timeout by policy

    "Waiting Material" --> "In Progress" : material available
    "Waiting Material" --> Assigned : reassignment for material

    "Waiting Third Party" --> "In Progress" : dependency resolved
    "Waiting Third Party" --> Assigned : reassignment required

    Resolved --> Verification : verification required
    Resolved --> Closed : auto-close by policy

    Verification --> Closed : verified
    Verification --> Reopened : verification failed or disputed

    Closed --> Reopened : customer dispute or recurrence

    Reopened --> Triaged : re-triage required
    Reopened --> Assigned : direct reassignment
```

---

# Ticket Categories

Ticket category determines SLA targets, evidence requirements, assignment rules, and resolution workflows.

## Incident
An unplanned event causing or threatening service degradation or loss.

## Problem
A recurring or root-cause issue linked to multiple incidents.

## Service Request
A customer-initiated request for a defined service action.

## Complaint
A customer expression of dissatisfaction requiring formal response.

## Installation
A new service installation task associated with a subscription onboarding.

## Relocation
A service relocation task for an existing customer subscription.

## Upgrade
A service upgrade task requiring field work or provisioning changes.

## Downgrade
A service downgrade task requiring field work or provisioning changes.

## Inspection
A planned or reactive site inspection for quality assurance.

## Preventive Maintenance
A scheduled maintenance task performed before failure occurs.

## Corrective Maintenance
A maintenance task performed in response to a detected or reported failure.

Category configuration and category-specific rules are managed through Settings Engine.

---

# Priority Model

Priority determines SLA targets and escalation behavior.

## Critical
Immediate response required. Highest SLA urgency. May bypass standard assignment queues.

## High
Expedited response and resolution. Elevated SLA targets.

## Medium
Standard response and resolution according to configured SLA targets.

## Low
Deferred response within configured SLA windows.

Priority assignment must occur at triage. Priority may be elevated by SLA breach or supervisor override.

Priority levels and associated SLA targets are configurable through Settings Engine.

---

# SLA

## Response SLA
The time allowed from ticket submission to assignment acknowledgment.

Response SLA starts when the ticket enters Open state.

Response SLA may be marked met when the ticket reaches Accepted state.

## Resolution SLA
The time allowed from ticket submission to resolution.

Resolution SLA starts when the ticket enters Open state.

Resolution SLA stops when the ticket enters Resolved state.

## Escalation
When SLA breach risk is detected, an escalation notification is triggered.

Escalation recipients and thresholds are configurable through Settings Engine.

## Breach
When SLA targets are exceeded, a breach event is recorded.

Breach records are immutable and available for reporting.

SLA breach does not automatically close or cancel a ticket.

## Pause Conditions
SLA clocks may be paused in the following states according to configured policy:

- Waiting Customer
- Waiting Material
- Waiting Third Party

## Resume Conditions
SLA clocks resume when the ticket transitions out of a pause condition state.

Pause and resume events must be fully traceable in the timeline.

All SLA values and pause behaviors are configurable through Settings Engine.

---

# Assignment Engine

The assignment engine supports configurable strategies for routing tickets to the appropriate technician or team.

## Manual Assignment
Supervisors or authorized users manually assign tickets based on operational judgment.

## Area Assignment
Tickets are assigned to technicians covering the relevant service area or geographic zone.

## Skill Assignment
Tickets are routed to technicians with the required skills or certifications for the ticket category.

## Workload Balancing
Assignment policy distributes tickets based on active workload and configurable fairness targets.

## Supervisor Override
Supervisors may override any assignment rule and reassign tickets at any time with reason capture.

Assignment strategy selection and configuration are managed through Settings Engine.

---

# Technician Workflow

The technician field workflow follows this sequence:

Accept Ticket

↓

Navigate to Location

↓

Verify Customer Identity

↓

Perform Work

↓

Capture Photos and Evidence

↓

Update Notes

↓

Attach Evidence

↓

Resolve

↓

Verification (when required)

↓

Close

Each step must be traceable with timestamp and actor attribution. Evidence requirements are configurable by ticket category.

---

# Attachments

The Ticket Workflow supports the following attachment types:

- Photos (field evidence, before and after)
- Documents (contracts, permits, technical references)
- GPS Coordinates (location verification at arrival and resolution)
- Speed Test Results (connectivity validation evidence)
- Signal Measurements (optical or RF signal evidence)
- Configuration Backup (device configuration snapshots)

Attachment requirements by ticket category are configurable through Settings Engine.

Attachment access inherits ticket-level permissions.

All attachments are versioned and must never be permanently deleted within the retention period.

---

# Exception Handling

## Customer Not Available
Handling:
- Record customer unavailability with timestamp.
- Transition ticket to Waiting Customer.
- Send customer notification requesting availability.
- Escalate if unavailability exceeds configured threshold.

## Material Unavailable
Handling:
- Record missing material details.
- Transition ticket to Waiting Material.
- Notify internal operations or procurement.
- Escalate to supervisor when waiting duration threshold is reached.

## Weather Delay
Handling:
- Record delay reason and conditions.
- Transition ticket to Waiting Third Party or return to Accepted.
- Notify customer of delay.
- Reschedule according to policy.

## Access Denied
Handling:
- Record access denial reason and location context.
- Transition ticket to Waiting Customer or Waiting Third Party as appropriate.
- Notify customer service for coordination.
- Escalate after configured timeout.

## Duplicate Ticket
Handling:
- Identify the primary ticket.
- Merge relevant context from the duplicate into the primary.
- Cancel the duplicate with reference to the primary ticket.
- Preserve the duplicate evidence for audit.

## Invalid Assignment
Handling:
- Record the invalid assignment reason.
- Return ticket to Triaged for reassignment.
- Preserve the invalid assignment in history.
- Notify the supervisor.

## Manual Override
Handling:
- Require authorized actor and reason.
- Preserve complete timeline and activity history.
- Record the override event with accountability reference.

## Ticket Escalation
Handling:
- Capture escalation trigger reason such as SLA breach risk or supervisor decision.
- Notify the escalation target according to policy.
- Record the escalation event with attribution.
- Escalation must not bypass the state machine.

## Ticket Merge
Handling:
- Identify the primary and secondary tickets.
- Transfer relevant context from secondary to primary.
- Cancel the secondary ticket with merge reference.
- Preserve all history from both tickets.

## Ticket Split
Handling:
- Identify distinct work items requiring separate tickets.
- Create child or sibling tickets for each distinct item.
- Link parent and child tickets with traceable references.
- Preserve all original ticket history.

---

# Integration Points

## Network Monitoring Workflow
Receives device warning and critical events as ticket candidates. Monitoring recommends tickets but the Ticket Workflow decides whether to create them automatically or require manual confirmation. Monitoring never directly modifies ticket state.

## Provisioning Workflow
Receives provisioning failure events as ticket candidates for operational follow-up. Provisioning and ticket workflows maintain separate ownership.

## Subscription Lifecycle
Receives subscription-related service issues and service restoration requests. Ticket Workflow does not directly modify subscription state.

## Billing Workflow
Receives billing-related complaints or service request escalations. Ticket Workflow does not modify invoices.

## Payment Workflow
Receives payment dispute or investigation requests. Ticket Workflow does not modify payment records.

## Collector Workflow
Receives field exception escalations requiring technical resolution beyond collector scope. Collector and ticket workflows maintain separate ownership.

## Notification Workflow
Publishes ticket lifecycle events for notification delivery. Ticket Workflow does not communicate directly with delivery channels.

## Timeline
Generates timeline events for every significant ticket state transition, assignment, resolution, and closure.

## Activity Log
Captures accountability and operational traceability for every ticket action, assignment change, evidence capture, and override.

## Customer Portal
Allows customers to create tickets, view status, add comments, upload attachments, and confirm resolution. Portal visibility is bounded by customer-facing rules.

## Attachment System
Stores and manages photos, documents, GPS captures, speed test results, signal measurements, and configuration backups attached to tickets.

## Settings Engine
Controls SLA targets, escalation thresholds, assignment policies, evidence requirements, category rules, pause conditions, auto-close behavior, and priority configuration.

## Reporting
Consumes ticket lifecycle outcomes, SLA compliance data, technician productivity, reopen rates, and category distributions for operational analytics.

---

# Customer Portal

Customers may:

- Create tickets
- View ticket status and history
- Add comments and notes
- Upload attachments
- Confirm resolution during verification

Customers cannot:

- Change ticket priority
- Change ticket assignment
- Close operational tickets that require verification
- Access internal notes or internal classification details
- View technician assignment details unless policy allows

Customer-created tickets must follow the same lifecycle as internally created tickets.

Customer portal ticket interactions must generate timeline and activity log entries.

---

# Reporting Outputs

Ticket Workflow exposes reporting including:

- Response Time
- Resolution Time
- SLA Compliance Rate
- SLA Breach Count
- Technician Productivity
- Reopen Rate
- Ticket Volume
- Category Distribution
- Area Distribution
- Priority Distribution
- Average Time in Each State
- Escalation Frequency
- First-Contact Resolution Rate

Reporting outputs must remain consistent with immutable ticket history and auditable transition records.

---

# Workflow Principles

- Every ticket has exactly one owner at any time.
- Every assignment is auditable and immutable as history.
- Every state transition generates timeline events.
- SLA is configurable and category-driven.
- Assignment history is preserved and never deleted.
- Ticket Workflow never owns business entities such as invoices, payments, or subscriptions.
- Attachments are versioned and preserved within retention policy.
- Resolution requires evidence when category policy mandates it.
- Duplicate detection must be applied at triage.
- Escalation must be traceable with trigger reason and attribution.
- Manual override requires authorization and produces an audit record.
- Reopening is a forward action that preserves full prior history.

---

# Future Extensions

The following ticket workflow extensions are reserved for future design:

## AI Ticket Classification
Support automated category and priority suggestion based on ticket content analysis.

## AI Technician Recommendation
Support intelligent technician matching based on skills, availability, and historical performance.

## AI Root Cause Analysis
Support automated root cause suggestion for recurring incidents linked to monitoring patterns.

## Route Optimization
Support optimized technician routing for multi-ticket field schedules.

## Knowledge Base Integration
Support access to resolution guides and known issue documentation from within the ticket workflow.

## Customer Satisfaction Survey
Support post-closure customer satisfaction measurement linked to ticket outcomes.

## Predictive Maintenance
Support proactive ticket creation from predictive monitoring signals before failure occurs.

## Field Inventory Integration
Support real-time material availability checks from field inventory management during the Accepted and In Progress states.
