# Purpose

This document defines the complete collector workflow responsible for managing field collections for outstanding invoices.

Collector Workflow manages collection activities but does not own invoices or payments.

Invoices remain owned by the Billing Workflow.

Payments remain owned by the Payment Workflow.

---

# Scope

Integrates with:

- Billing Workflow
- Payment Workflow
- Subscription Lifecycle
- Notification Engine
- Timeline
- Activity Log
- Customer Portal
- Settings Engine
- Reporting

---

# Collection Principles

## Assignment Based Collection
Every collection task belongs to one collector.

---

## Dynamic Reassignment
Assignments may be reassigned.

History must remain auditable.

---

## Mobile First
Collectors primarily use mobile devices.

---

## Offline Friendly
Collection activities should support temporary offline operation.

Synchronization occurs automatically when connectivity returns.

---

## Search First
Collectors should locate customers using:

- Name
- Customer Number
- Phone Number
- Address
- QR Code
- Invoice Number

---

## One Visit Many Invoices
One field visit may collect multiple invoices.

---

## Payment Ownership
Collectors never modify invoices.

Collectors never modify payments.

Collectors only initiate collection activities.

---

# Actor Matrix

The following matrix describes the primary actor responsibilities across the collector lifecycle.

| Actor | Responsible | Approver | Override | Read Only |
|---|---|---|---|---|
| Customer | Yes, for identity confirmation and collection interaction | No | No | Yes |
| Collector | Yes, for assignment execution, visit outcomes, and payment submission initiation | No | No | Yes |
| Customer Service | Yes, for scheduling support and reassignment coordination | Yes, for controlled customer-facing exceptions | Limited, by policy | Yes |
| Finance | Yes, for collection governance and escalation controls | Yes | Limited, by policy | Yes |
| Supervisor | Yes, for assignment approval, prioritization, and collector oversight | Yes | Yes, by policy | Yes |
| System | Yes, for workflow orchestration, notifications, offline synchronization, and status transitions | No | Limited, by policy | Yes |
| Administrator | Yes, for controlled intervention and exception resolution | Yes | Yes | Yes |

The actor matrix defines responsibility for workflow operations, not record ownership of invoices or payments.

---

# Collection Lifecycle

## Waiting Assignment

### Description
Outstanding collection work exists but has not yet been assigned to a collector.

### Entry Validation
- At least one collectible invoice exists.
- Collection eligibility criteria are met.
- Customer and contact context are available.

### Exit Validation
- A valid collector is assigned.
- Assignment policy has been applied.

### Allowed Transitions
- Assigned
- Cancelled

### Business Rules
- Collection must start from eligible unpaid or partially paid invoices.
- Collector workflow does not change invoice ownership.
- Assignment eligibility is controlled by policy.

### Generated Events
- Collection Task Created
- Collection Waiting Assignment

### Timeline
- Collection task created
- Waiting assignment
- Eligibility recorded

### Notifications
- Internal assignment queue notification.

### Customer Portal Visibility
- Customer sees outstanding billing status only, not internal assignment details.

### Billing Impact
- No invoice data mutation.
- Collection eligibility context is consumed from billing.

### Payment Impact
- No payment record is created.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Waiting assignment count
- Time to assignment
- Assignment backlog size

### Rollback Policy
Return to pre-assignment queue state while preserving assignment history.

### Maximum Duration
Configurable through Settings Engine.

## Assigned

### Description
A collector has been assigned to the collection task.

### Entry Validation
- Valid collector identity exists.
- Assignment policy checks are satisfied.
- Task ownership is unique.

### Exit Validation
- Collection is scheduled or reassigned.
- Assignment is cancelled if no longer valid.

### Allowed Transitions
- Scheduled
- Waiting Assignment
- Cancelled

### Business Rules
- Every collection task has exactly one active collector assignment.
- Reassignment must preserve full assignment history.
- Priority rules may override default assignment order.

### Generated Events
- Collector Assigned
- Assignment Confirmed

### Timeline
- Collector assigned
- Assignment confirmed
- Assignment priority recorded

### Notifications
- Collector assignment notification.
- Supervisor notification when policy requires.

### Customer Portal Visibility
- No internal collector assignment details are exposed.

### Billing Impact
- No billing mutation.

### Payment Impact
- No payment mutation.

### Automation Level
- Semi Automatic

### Owner
- Supervisor

### Metrics
- Assignment acceptance rate
- Reassignment count
- Assignment turnaround time

### Rollback Policy
Revert to Waiting Assignment if assignment is invalid or revoked, preserving all assignment events.

### Maximum Duration
Configurable through Settings Engine.

## Scheduled

### Description
A visit schedule is set for the assigned collector and target customer.

### Entry Validation
- Collector assignment is active.
- Customer location and contact context are available.
- Scheduling rules are satisfied.

### Exit Validation
- Collector begins route execution.
- Schedule is updated, cancelled, or escalated.

### Allowed Transitions
- On Route
- Assigned
- Cancelled

### Business Rules
- Scheduling may include customer-preferred windows where policy allows.
- Schedule changes must remain auditable.
- Priority collections may preempt lower-priority schedules.

### Generated Events
- Collection Scheduled
- Schedule Updated

### Timeline
- Collection scheduled
- Schedule updated
- Visit plan recorded

### Notifications
- Collector schedule notification.
- Optional customer visit reminder.

### Customer Portal Visibility
- May display upcoming collection visit window when policy allows.

### Billing Impact
- No billing mutation.

### Payment Impact
- No payment mutation.

### Automation Level
- Semi Automatic

### Owner
- Customer Service

### Metrics
- Scheduling lead time
- Schedule change rate
- On-time schedule adherence

### Rollback Policy
Return to Assigned if schedule cannot be executed, preserving scheduling history.

### Maximum Duration
Configurable through Settings Engine.

## On Route

### Description
Collector is en route to perform the field collection visit.

### Entry Validation
- Visit is scheduled.
- Collector confirms route start.
- Required task details are available locally or offline.

### Exit Validation
- Collector arrives and visit outcome is recorded.
- Route is interrupted and rescheduled.

### Allowed Transitions
- Customer Visited
- Follow Up Required
- Scheduled

### Business Rules
- Mobile-first execution is expected.
- Offline mode may be used with later synchronization.
- Route progress should be traceable.

### Generated Events
- Route Started
- Route Progress Updated

### Timeline
- Collector on route
- Route progress updated
- Route interruption recorded

### Notifications
- Internal route status notification.

### Customer Portal Visibility
- Optional collector-arrival status where policy allows.

### Billing Impact
- No billing mutation.

### Payment Impact
- No payment mutation.

### Automation Level
- Semi Automatic

### Owner
- Collector

### Metrics
- Route start latency
- Route completion ratio
- Route interruption count

### Rollback Policy
Return to Scheduled if route cannot be completed and visit has not occurred; preserve route history.

### Maximum Duration
Configurable through Settings Engine.

## Customer Visited

### Description
Collector has visited the customer location and verified visit context.

### Entry Validation
- Collector arrival is recorded.
- Customer identity and account context are verified.
- Visit evidence is captured according to policy.

### Exit Validation
- Negotiation outcome is recorded.
- Payment is collected or follow-up is required.

### Allowed Transitions
- Negotiation
- Payment Collected
- Follow Up Required
- Cancelled

### Business Rules
- Customer verification is required before payment collection.
- Evidence capture may include GPS, notes, and photos.
- Visit outcome must be auditable.

### Generated Events
- Customer Visited
- Visit Verification Completed

### Timeline
- Customer visited
- Verification completed
- Visit outcome recorded

### Notifications
- Internal visit outcome notification.

### Customer Portal Visibility
- May show visit completed status and next step summary.

### Billing Impact
- No invoice mutation by collector workflow.

### Payment Impact
- No payment record yet unless collection proceeds.

### Automation Level
- Manual

### Owner
- Collector

### Metrics
- Visit completion rate
- Verification failure count
- Average visit duration

### Rollback Policy
Revert to On Route only if visit was recorded in error and preserve all captured evidence history.

### Maximum Duration
Configurable through Settings Engine.

## Negotiation

### Description
Collector and customer discuss outstanding balances, payment options, and feasible settlement outcomes.

### Entry Validation
- Customer has been visited.
- Outstanding invoice and balance context is available.
- Negotiation policy guidance is available.

### Exit Validation
- Payment agreement is reached.
- Follow-up requirement is recorded.
- Collection attempt is cancelled by policy.

### Allowed Transitions
- Payment Collected
- Follow Up Required
- Cancelled

### Business Rules
- Negotiation does not alter invoice ownership or payment ownership.
- Any promised payment arrangement must be recorded.
- Negotiation outcomes must remain auditable.

### Generated Events
- Negotiation Started
- Negotiation Outcome Recorded

### Timeline
- Negotiation started
- Negotiation outcome recorded
- Settlement intent captured

### Notifications
- Internal negotiation outcome notification.
- Optional customer confirmation notification.

### Customer Portal Visibility
- May show agreed follow-up or promised payment note when policy allows.

### Billing Impact
- No direct billing mutation.

### Payment Impact
- No immutable payment record until payment workflow confirms recording.

### Automation Level
- Manual

### Owner
- Collector

### Metrics
- Negotiation success rate
- Promise-to-pay rate
- Follow-up conversion rate

### Rollback Policy
Return to Customer Visited if negotiation record is corrected, preserving full negotiation history.

### Maximum Duration
Configurable through Settings Engine.

## Payment Collected

### Description
Collector has received payment from customer and captured collection evidence.

### Entry Validation
- Customer payment intent is confirmed.
- Collection amount and method are captured.
- Required evidence or reference data is recorded.

### Exit Validation
- Payment submission package is complete.
- Payment can be submitted to payment workflow.

### Allowed Transitions
- Payment Submitted
- Follow Up Required
- Cancelled

### Business Rules
- Collector initiates payment handoff but does not create immutable payment records directly.
- One visit may include multiple invoice settlement intents.
- Single collection may map to batch payment processing.

### Generated Events
- Payment Collected
- Collection Evidence Captured

### Timeline
- Payment collected
- Collection evidence captured
- Payment submission prepared

### Notifications
- Internal payment-collected notification.
- Optional customer provisional receipt notice.

### Customer Portal Visibility
- May show payment collection pending confirmation.

### Billing Impact
- Billing remains unchanged until payment workflow confirmation.

### Payment Impact
- Payment handoff initiated, not finalized.

### Automation Level
- Manual

### Owner
- Collector

### Metrics
- Collection amount per visit
- Payment collection rate
- Evidence completeness rate

### Rollback Policy
Return to Negotiation or Customer Visited if collected entry is invalid before submission; preserve collection evidence trail.

### Maximum Duration
Configurable through Settings Engine.

## Payment Submitted

### Description
Collected payment context has been submitted to Payment Workflow for validation and recording.

### Entry Validation
- Payment collection package is complete.
- Required references for payment workflow are valid.
- Submission channel is available.

### Exit Validation
- Payment workflow acknowledges receipt.
- Submission failure is recorded for retry or follow-up.

### Allowed Transitions
- Completed
- Follow Up Required
- Cancelled

### Business Rules
- Submission must be idempotent.
- Submission must never create duplicate payment intents.
- Payment ownership remains with Payment Workflow.

### Generated Events
- Payment Submitted
- Payment Workflow Acknowledged

### Timeline
- Payment submitted
- Payment handoff acknowledged
- Submission result recorded

### Notifications
- Internal submission confirmation or failure notification.

### Customer Portal Visibility
- Shows payment submitted and pending final confirmation when applicable.

### Billing Impact
- Billing updates occur only after Payment Workflow settlement outputs.

### Payment Impact
- Payment workflow validation and recording begins.

### Automation Level
- Semi Automatic

### Owner
- System

### Metrics
- Submission success rate
- Submission retry count
- Submission latency

### Rollback Policy
Resubmit from last valid submission state on failure while preserving all prior submission attempts.

### Maximum Duration
Configurable through Settings Engine.

## Completed

### Description
Collection workflow for the assignment has completed with confirmed downstream handoff outcome.

### Entry Validation
- Payment submission outcome is confirmed or collection task is finalized by policy.
- Completion reason is recorded.

### Exit Validation
- None.

### Allowed Transitions
- None.

### Business Rules
- Completed tasks remain historical and auditable.
- Completion does not transfer invoice or payment ownership.

### Generated Events
- Collection Completed
- Collection Closure Recorded

### Timeline
- Collection completed
- Closure reason recorded
- Assignment closed

### Notifications
- Internal completion notification.
- Optional customer completion notification.

### Customer Portal Visibility
- Displays resulting payment or outstanding status as synchronized from billing and payment workflows.

### Billing Impact
- Billing reflects outcomes from payment and collection integration.

### Payment Impact
- Payment outcomes are consumed from payment workflow.

### Automation Level
- Semi Automatic

### Owner
- Supervisor

### Metrics
- Completion rate
- Average completion time
- Completed collection value

### Rollback Policy
Completion correction requires explicit authorized workflow and must preserve full history.

### Maximum Duration
No Maximum Duration.

## Follow Up Required

### Description
Collection attempt was not fully resolved and requires additional action.

### Entry Validation
- Attempt outcome indicates unresolved balance or unresolved verification.
- Follow-up reason is recorded.

### Exit Validation
- New schedule is created.
- Task is reassigned, cancelled, or completed by policy path.

### Allowed Transitions
- Scheduled
- Assigned
- Cancelled
- Completed

### Business Rules
- Follow-up reasons must be categorized.
- Follow-up planning must preserve prior attempt history.
- Outstanding balances remain collectible.

### Generated Events
- Follow Up Required
- Follow Up Plan Created

### Timeline
- Follow-up required
- Follow-up reason recorded
- Follow-up plan created

### Notifications
- Internal follow-up assignment notification.
- Optional customer follow-up notification.

### Customer Portal Visibility
- May display pending follow-up status where policy allows.

### Billing Impact
- No direct billing mutation.

### Payment Impact
- No new payment record unless a new payment collection occurs.

### Automation Level
- Semi Automatic

### Owner
- Customer Service

### Metrics
- Follow-up rate
- Follow-up completion rate
- Repeat-visit count

### Rollback Policy
Return to last valid pre-follow-up state only by authorized correction while preserving all follow-up history.

### Maximum Duration
Configurable through Settings Engine.

## Cancelled

### Description
Collection task is cancelled by policy, exception, or authorized operational decision.

### Entry Validation
- Cancellation reason is provided.
- Authorization requirements are satisfied.

### Exit Validation
- None.

### Allowed Transitions
- None.

### Business Rules
- Cancellation must be auditable.
- Cancellation does not alter invoice or payment ownership.
- Cancellation may trigger reassignment or new task creation through separate workflow.

### Generated Events
- Collection Cancelled
- Cancellation Reason Recorded

### Timeline
- Collection cancelled
- Cancellation reason recorded
- Task closure recorded

### Notifications
- Internal cancellation notification.
- Optional customer cancellation notice.

### Customer Portal Visibility
- Customer may see cancelled visit status where policy allows.

### Billing Impact
- No direct billing mutation.

### Payment Impact
- No direct payment mutation.

### Automation Level
- Manual

### Owner
- Administrator

### Metrics
- Cancellation rate
- Cancellation reason distribution
- Reassignment-after-cancel rate

### Rollback Policy
Cancellation rollback requires authorized correction workflow and full history retention.

### Maximum Duration
No Maximum Duration.

---

# Assignment Policy

Assignment policy is configurable through Settings Engine.

## Automatic Assignment
System may assign tasks automatically based on configured rules.

## Manual Assignment
Supervisors or authorized users may assign tasks manually.

## Area Based Assignment
Assignments may prioritize collector-to-area affinity and operational territory coverage.

## Collector Workload Balancing
Assignment policy may distribute tasks based on workload thresholds and fairness targets.

## Priority Assignment
High-risk or high-value overdue cases may be prioritized.

## Reassignment
Reassignment is allowed with full audit history and reason capture.

---

# Visit Workflow

Assignment

↓

Navigate

↓

Customer Verification

↓

Discuss Outstanding Balance

↓

Collect Payment

↓

Generate Receipt Reference

↓

Submit Payment

↓

Sync Payment Workflow

---

# Search Workflow

Collector should be able to search using:

- Customer Number
- Invoice Number
- QR Code
- Customer Name
- Phone Number
- Address

Search results should prioritize exact matches, then prefix matches, then partial matches according to configured search policy.

---

# QR Code

QR code should identify customer or invoice.

QR code must never contain sensitive payment information.

QR code is used for fast field lookup, visit verification support, and collection workflow acceleration.

---

# Batch Collection

One visit

↓

Multiple invoices

↓

Single payment

↓

Payment Allocation Workflow

Batch collection supports one visit resolving several outstanding invoices while payment allocation remains owned by Payment Workflow.

---

# Exception Handling

## Customer Not Home
Handling:
- Record unsuccessful visit reason.
- Create follow-up action.
- Notify assignment owner.

## Address Changed
Handling:
- Record updated location note.
- Escalate address verification.
- Reschedule or reassign task.

## Customer Refused Payment
Handling:
- Record refusal reason.
- Keep invoice collectible.
- Escalate based on policy.

## Partial Payment
Handling:
- Record collected amount and remaining balance context.
- Submit payment to Payment Workflow.
- Schedule follow-up if required.

## Unable To Verify Customer
Handling:
- Block payment collection.
- Record verification failure.
- Trigger follow-up verification workflow.

## QR Code Invalid
Handling:
- Record invalid QR event.
- Use fallback search method.
- Notify internal support if repeated.

## Offline Device
Handling:
- Store visit and collection data locally.
- Mark records pending synchronization.
- Auto-sync when connectivity returns.

## Synchronization Failed
Handling:
- Preserve unsynced data.
- Retry synchronization according to policy.
- Escalate if retry threshold is exceeded.

## Assignment Cancelled
Handling:
- Record cancellation reason.
- Notify collector and supervisor.
- Requeue task when policy requires.

## Manual Override
Handling:
- Require authorized actor and reason.
- Preserve full timeline and activity history.
- Prevent conflicting collection outcomes.

---

# Integration Points

## Billing Workflow
Provides outstanding invoice eligibility and receives collection outcome context.

## Payment Workflow
Receives submitted payment collection context and returns payment confirmation outcomes.

## Notification Engine
Handles visit reminders, outcome notifications, and follow-up messaging.

## Timeline
Captures visit lifecycle and collection progress as business-readable events.

## Activity Log
Captures accountability, field actions, and exception handling traceability.

## Customer Portal
Displays customer-visible collection outcomes and synchronized payment status.

## Settings Engine
Controls assignment rules, priorities, retry policies, and offline synchronization policy.

## Reporting
Consumes collection operations and outcome metrics.

---

# Mobile Workflow

Expected mobile experience includes:

- Offline cache
- Camera QR scanner
- GPS capture
- Photo attachment
- Digital notes
- Digital signature (future)

Mobile workflow must preserve consistency between offline actions and synchronized server-side outcomes.

---

# Reporting Outputs

Collector Workflow exposes:

- Collection Success Rate
- Visit Success Rate
- Outstanding By Collector
- Outstanding By Area
- Collector Productivity
- Average Collection Time
- Visit Count
- Payment Collection Amount

Reporting outputs must remain consistent with billing and payment authoritative sources.

---

# Workflow Principles

- Collector never edits invoices.
- Collector never edits payments.
- Every visit generates timeline events.
- Every collection generates activity logs.
- Offline operation must preserve data consistency.
- Synchronization must be idempotent.
- GPS and photo evidence should be supported.
- Collection history must never be deleted.

---

# Future Extensions

The following collector workflow extensions are reserved for future design:

## Route Optimization
Support optimized collector routing based on area and outstanding priorities.

## AI Visit Recommendation
Support intelligence-assisted visit sequencing and timing recommendations.

## Automatic Assignment
Support fully policy-driven task assignment automation.

## Collector Leaderboard
Support performance benchmarking and incentive visibility.

## Customer Appointment Booking
Support customer-scheduled collection appointment windows.

## Digital Receipt
Support standardized digital receipt distribution.

## Electronic Signature
Support customer electronic signature capture.

## Geo Fence Validation
Support geofence-based visit authenticity checks.
