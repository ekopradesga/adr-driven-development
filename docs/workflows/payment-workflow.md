# Purpose

This document defines the complete payment lifecycle responsible for receiving, validating, allocating, and completing customer payments.

The Payment Workflow is responsible for:

- Receiving payments
- Validating payment information
- Recording immutable payment transactions
- Allocating payments to invoices
- Supporting partial payments
- Supporting batch payments
- Supporting multiple payment methods
- Triggering downstream business events

The Payment Workflow is NOT responsible for:

- Invoice generation
- Billing calculations
- Service provisioning
- Service suspension

---

# Scope

This workflow integrates with:

- Billing Workflow
- Collector Workflow
- Subscription Lifecycle
- Notification Engine
- Timeline
- Activity Log
- Customer Portal
- Reporting
- Settings Engine

---

# Payment Principles

## Immutable Payment
Once a payment is successfully recorded, it must never be edited or deleted.

Corrections must be handled through reversal, refund, or reallocation workflows.

---

## Mutable Allocation
Payment allocation may change when authorized by business policy.

Allocation history must remain fully auditable.

---

## One Payment, Many Allocations
One payment may be allocated to one or more invoices.

---

## Partial Payment
Invoices may be partially paid.

Outstanding balances remain collectible until fully settled.

---

## Batch Payment
One payment transaction may settle multiple invoices.

---

## Payment Intent
Payment Intent represents the customer's intention to pay before payment confirmation.

Examples:

- QRIS generated
- Virtual Account created
- Bank Transfer waiting
- Cash collection assigned

Payment Intent may expire without becoming a completed payment.

---

# Actor Matrix

The following matrix describes the primary actor responsibilities across the payment lifecycle.

| Actor | Responsible | Approver | Override | Read Only |
|---|---|---|---|---|
| Customer | Yes, for initiating payment intent and completing payment action | No | No | Yes |
| Collector | Yes, for collection-driven payment intake and follow-up | Yes, for collection outcomes | Limited, by policy | Yes |
| Customer Service | Yes, for payment support and customer communication | Yes, for service exceptions | Limited, by policy | Yes |
| Finance | Yes, for payment governance, reconciliation oversight, and controlled corrections | Yes | Limited, by policy | Yes |
| Billing Engine | Yes, for receiving payment allocation outcomes and invoice status synchronization | No | No | Yes |
| System | Yes, for workflow orchestration, validation, and event processing | No | Limited, by policy | Yes |
| Administrator | Yes, for controlled intervention and exception resolution | Yes | Yes | Yes |

The actor matrix describes workflow responsibility rather than record ownership.

---

# Payment Lifecycle States

## Payment Intent Created

### Description
A payment intent has been created to represent the customer's intention to pay.

### Entry Validation
- Customer and payable context exist.
- Payment method intent is recognized.
- Intent policy is active.

### Exit Validation
- Payment moves to waiting state.
- Intent expires or fails according to policy.

### Allowed Transitions
- Waiting Payment
- Payment Failed

### Business Rules
- Payment Intent is not a completed payment.
- Intent expiration is policy-driven.
- Intent must be traceable with unique reference context.

### Generated Events
- Payment Intent Created
- Payment Intent Registered

### Timeline Events
- Payment intent created
- Payment intent waiting
- Payment channel intent recorded

### Notifications
- Optional customer payment instruction notification.
- Internal tracking notification when configured.

### Customer Portal Visibility
- Customer can see intent status and reference details.

### Billing Impact
- No invoice status change.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Intent creation count
- Intent expiration rate
- Intent-to-payment conversion rate

### Rollback Policy
Invalidate intent and return to pre-intent payable context while preserving full intent history.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Waiting Payment

### Description
The workflow is waiting for payment confirmation after intent creation or payment request initiation.

### Entry Validation
- Payment intent exists or payable context is established.
- Payment channel reference is available.
- Waiting window policy is active.

### Exit Validation
- Payment has been received.
- Waiting window expires.

### Allowed Transitions
- Payment Received
- Payment Failed

### Business Rules
- Waiting state must preserve intent and channel context.
- Timeout and expiration behavior must be configurable.

### Generated Events
- Waiting Payment Started
- Payment Pending Confirmation

### Timeline Events
- Waiting for payment
- Payment pending confirmation
- Waiting window tracked

### Notifications
- Reminder notifications according to policy.

### Customer Portal Visibility
- Customer can view pending payment status.

### Billing Impact
- Invoice remains unpaid until payment is confirmed.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Waiting duration
- Timeout count
- Confirmation latency

### Rollback Policy
Return to Payment Intent Created when payment confirmation is invalidated before receipt, preserving history.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Payment Received

### Description
A payment signal or receipt has been received and awaits validation.

### Entry Validation
- Payment confirmation signal is present.
- Received amount and method are captured.
- Customer context is identifiable.

### Exit Validation
- Payment proceeds to validation.
- Invalid receipt is routed to failure handling.

### Allowed Transitions
- Payment Validation
- Payment Failed

### Business Rules
- Receipt evidence must be traceable.
- Receiving payment does not automatically complete allocation.

### Generated Events
- Payment Received
- Payment Receipt Captured

### Timeline Events
- Payment received
- Receipt captured
- Validation queued

### Notifications
- Optional customer receipt acknowledgment.
- Internal intake notification.

### Customer Portal Visibility
- Shows payment received and pending validation status.

### Billing Impact
- Billing remains unchanged until validation and recording succeed.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Receipt intake rate
- Unvalidated receipt count
- Intake processing time

### Rollback Policy
Mark received signal invalid and return to Waiting Payment while preserving receipt evidence.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Payment Validation

### Description
The payment is validated for amount, customer, method, reference, and consistency.

### Entry Validation
- Payment receipt exists.
- Validation policy is available.
- Required reference data is accessible.

### Exit Validation
- Validation passes and payment can be recorded.
- Validation failure is classified and tracked.

### Allowed Transitions
- Payment Recorded
- Payment Failed

### Business Rules
- Duplicate payments must be prevented.
- Amount must be positive and currency valid.
- Invoice references and allocation consistency must be validated.

### Generated Events
- Payment Validation Started
- Payment Validation Completed

### Timeline Events
- Payment validation started
- Payment validation completed
- Validation outcome recorded

### Notifications
- Internal validation failure notification.
- Optional customer notification for validation issues.

### Customer Portal Visibility
- Shows validation status when relevant.

### Billing Impact
- No invoice update until payment is recorded and allocated.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Fully Automatic

### Owner
- Finance

### Metrics
- Validation success rate
- Validation failure count
- Duplicate prevention hit rate

### Rollback Policy
Return to Waiting Payment or Payment Received based on failure class while preserving validation trail.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Payment Recorded

### Description
A successful payment transaction is recorded as an immutable payment record.

### Entry Validation
- Payment validation passed.
- Unique payment reference is available.
- Immutable record policy is enforceable.

### Exit Validation
- Payment record is persisted and traceable.
- Allocation can begin.

### Allowed Transitions
- Payment Allocation
- Payment Reversed
- Payment Failed

### Business Rules
- Recorded payment is immutable.
- Payment records cannot be deleted by normal operations.
- Corrections must use reversal, refund, or reallocation workflows.

### Generated Events
- Payment Recorded
- Immutable Payment Created

### Timeline Events
- Payment recorded
- Immutable payment created
- Allocation queued

### Notifications
- Customer payment confirmation notification.
- Internal payment-recorded notification.

### Customer Portal Visibility
- Payment appears in history as recorded.

### Billing Impact
- Eligible invoices can now be updated through allocation outcomes.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Fully Automatic

### Owner
- Finance

### Metrics
- Recorded payment count
- Recording success rate
- Payment reference uniqueness incidents

### Rollback Policy
Payment record itself is not rolled back; use Payment Reversed when policy allows correction.

### Maximum Duration (configurable)
No Maximum Duration.

## Payment Allocation

### Description
The recorded payment is allocated to one or more invoices according to allocation policy.

### Entry Validation
- Immutable payment exists.
- Eligible invoices and balances are available.
- Allocation policy configuration is present.

### Exit Validation
- Allocation is completed partially or fully.
- Allocation failures are captured.

### Allowed Transitions
- Partially Allocated
- Fully Allocated
- Payment Failed

### Business Rules
- One payment may allocate to many invoices.
- Default allocation may follow oldest invoice first.
- Manual allocation may be permitted for authorized actors.

### Generated Events
- Payment Allocation Started
- Payment Allocation Applied

### Timeline Events
- Allocation started
- Allocation applied
- Allocation rule recorded

### Notifications
- Internal allocation result notification.
- Optional customer allocation update.

### Customer Portal Visibility
- Shows allocated invoices and remaining amount if any.

### Billing Impact
- Invoice balances are updated from allocation outcome.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Fully Automatic

### Owner
- Billing Engine

### Metrics
- Allocation duration
- Allocation failure count
- Allocation consistency rate

### Rollback Policy
Allocation changes may be reallocated under authorization while preserving immutable payment record and complete allocation history.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Partially Allocated

### Description
Payment has been allocated to invoices, but a remaining amount or outstanding invoice balance still exists.

### Entry Validation
- At least one allocation exists.
- Settlement is incomplete at payment or invoice level.

### Exit Validation
- Allocation becomes fully settled.
- Remaining amount is reallocated, reserved as credit, or resolved by policy.

### Allowed Transitions
- Fully Allocated
- Payment Completed
- Payment Failed

### Business Rules
- Partial allocation must keep outstanding balances collectible.
- Overpayment and underpayment handling must follow policy.
- Reallocation remains auditable.

### Generated Events
- Partially Allocated
- Outstanding Balance Updated

### Timeline Events
- Payment partially allocated
- Outstanding balance updated
- Reallocation eligibility recorded

### Notifications
- Customer partial settlement notification.
- Collector visibility update notification.

### Customer Portal Visibility
- Shows partially allocated status and outstanding balances.

### Billing Impact
- Some invoices may move to partial-paid status.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Semi Automatic

### Owner
- Collector

### Metrics
- Partial allocation rate
- Remaining balance amount
- Partial-to-full settlement time

### Rollback Policy
Allocation corrections are permitted by reallocation policy and require full audit and timeline retention.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Fully Allocated

### Description
The payment amount has been fully allocated according to invoice and policy rules.

### Entry Validation
- Payment amount has no unallocated remainder unless policy classifies surplus as credit.
- Allocation references are consistent.

### Exit Validation
- Settlement status can be finalized.
- Billing and collector downstream updates are completed.

### Allowed Transitions
- Payment Completed
- Payment Reversed

### Business Rules
- Fully allocated status must be traceable to invoice balances.
- Any residual overpayment handling must follow configured credit policy.

### Generated Events
- Fully Allocated
- Allocation Finalized

### Timeline Events
- Payment fully allocated
- Allocation finalized
- Invoice settlement synchronization recorded

### Notifications
- Customer full allocation notification.
- Internal settlement readiness notification.

### Customer Portal Visibility
- Shows invoices fully or proportionally settled by this payment.

### Billing Impact
- Invoice statuses are updated based on final allocation results.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Fully Automatic

### Owner
- Billing Engine

### Metrics
- Full allocation rate
- Allocation finalization time
- Residual handling count

### Rollback Policy
Reallocation may be applied only by authorized policy path while preserving complete allocation history.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Payment Completed

### Description
The payment lifecycle is completed after recording and allocation outcomes are finalized.

### Entry Validation
- Payment is recorded.
- Allocation outcome is finalized.
- Downstream updates have been triggered.

### Exit Validation
- None.

### Allowed Transitions
- Payment Reversed

### Business Rules
- Completed payments remain immutable.
- Completed state must remain fully auditable.

### Generated Events
- Payment Completed
- Payment Settlement Closed

### Timeline Events
- Payment completed
- Settlement closed
- Downstream workflows notified

### Notifications
- Customer completion notification.
- Internal completion notification.

### Customer Portal Visibility
- Shows payment as completed in payment history.

### Billing Impact
- Billing reflects final paid or partial-paid statuses according to allocation results.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Completion rate
- End-to-end payment cycle time
- Settlement success rate

### Rollback Policy
Completed status may only change through authorized reversal workflows with full historical traceability.

### Maximum Duration (configurable)
No Maximum Duration.

## Payment Reversed

### Description
A recorded payment has been reversed through authorized correction workflow.

### Entry Validation
- Payment record exists and is eligible for reversal by policy.
- Authorization requirements are satisfied.
- Reversal reason is captured.

### Exit Validation
- Reversal outcomes are synchronized with billing and collector context.

### Allowed Transitions
- Payment Completed
- Payment Failed

### Business Rules
- Reversal must not delete original payment history.
- Reversal and reallocation history must be auditable.
- Reversal policy governs who can initiate and approve.

### Generated Events
- Payment Reversal Started
- Payment Reversed

### Timeline Events
- Payment reversal started
- Payment reversed
- Reversal reason recorded

### Notifications
- Customer reversal notification when required.
- Internal reversal and control notification.

### Customer Portal Visibility
- Shows payment reversal status and retained history.

### Billing Impact
- Billing balances are recalculated through reversal outcomes.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Semi Automatic

### Owner
- Administrator

### Metrics
- Reversal count
- Reversal approval time
- Reversal accuracy rate

### Rollback Policy
Reversal of reversal follows explicit policy path and must preserve all historical records.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Payment Failed

### Description
The payment lifecycle could not complete due to validation, allocation, confirmation, or policy failures.

### Entry Validation
- A failure reason is captured.
- Failure context is traceable.

### Exit Validation
- Retry, correction, or manual override path is selected.

### Allowed Transitions
- Waiting Payment
- Payment Validation
- Payment Allocation
- Payment Intent Created

### Business Rules
- Failures must preserve full payment attempt history.
- Retry behavior must be configurable.
- Failure handling must not create duplicate payment records.

### Generated Events
- Payment Failed
- Payment Failure Reason Recorded

### Timeline Events
- Payment failed
- Failure reason recorded
- Retry or override path selected

### Notifications
- Internal failure notification.
- Customer failure notification where policy requires.

### Customer Portal Visibility
- Shows failed payment status and next action guidance.

### Billing Impact
- No successful settlement impact until payment is corrected and completed.

### Monitoring Impact
- No monitoring impact.

### Automation Level
- Semi Automatic

### Owner
- Administrator

### Metrics
- Failure count
- Retry success rate
- Mean time to recover

### Rollback Policy
Failed state correction returns to the last valid workflow step while preserving all failed-attempt records.

### Maximum Duration (configurable)
Configurable through Settings Engine.

---

# Supported Payment Methods

Business support includes:

- Cash
- Bank Transfer
- QRIS
- Virtual Account
- Payment Gateway (Future)
- Marketplace (Future)

Payment method eligibility, limits, and channel behavior are policy-driven and configurable through Settings Engine.

---

# Payment Processing Flow

Payment Intent

↓

Receive Payment

↓

Validate Payment

↓

Create Immutable Payment Record

↓

Allocate Payment

↓

Update Invoice Status

↓

Update Outstanding Balance

↓

Generate Timeline

↓

Generate Activity Log

↓

Trigger Notification

↓

Notify Billing Workflow

↓

Notify Collector Workflow

---

# Payment Validation

Payment validation includes:

- Duplicate Payment Prevention
- Positive Amount
- Valid Currency
- Valid Payment Method
- Valid Customer
- Valid Invoice Reference
- Allocation Consistency

Validation failures must be traceable and must not create immutable payment records.

---

# Allocation Rules

Allocation rules are policy-driven and configurable through Settings Engine.

Included rules:

- Oldest invoice first (default policy)
- Manual allocation (authorized users)
- Automatic allocation
- Remaining balance
- Overpayment
- Underpayment

Allocation outcomes must remain fully auditable and reproducible.

---

# Reallocation Policy

Payment allocations may be changed when business policy permits and authorization requirements are met.

Every reallocation must:

- Preserve audit history
- Preserve timeline history
- Require authorization when configured

Payment records themselves remain immutable.

---

# Retry Policy

Retry behavior is configurable through Settings Engine.

## Validation failure
Retry is allowed after corrected input or data synchronization.

## Notification failure
Notification retries may occur independently of payment completion.

## Allocation failure
Allocation retries may run automatically or manually based on policy.

## External confirmation timeout
Timeout retries may run within configured windows and escalation rules.

Retry must never remove historical payment, timeline, or audit records.

---

# Exception Handling

## Duplicate Payment
Handling:
- Block duplicate payment recording.
- Preserve duplicate attempt evidence.
- Escalate for review when required.

## Invalid Amount
Handling:
- Reject non-positive or inconsistent amount.
- Record validation failure.
- Route to correction path.

## Allocation Failed
Handling:
- Preserve allocation failure context.
- Trigger retry or manual intervention.
- Keep payment record immutable.

## Customer Not Found
Handling:
- Block payment completion.
- Record identity mismatch.
- Route to customer data correction process.

## Invoice Not Found
Handling:
- Block allocation.
- Record unresolved invoice reference.
- Route to billing correction workflow.

## Overpayment
Handling:
- Allocate according to policy.
- Route remainder to configured credit behavior.
- Preserve overpayment traceability.

## Underpayment
Handling:
- Mark invoice as partial paid.
- Preserve outstanding balance.
- Keep invoice collectible.

## External Confirmation Failed
Handling:
- Record external confirmation failure.
- Retry or escalate per policy.
- Preserve attempt history.

## Payment Gateway Timeout (Future)
Handling:
- Record timeout event.
- Retry confirmation based on policy.
- Escalate unresolved cases.

## Manual Override
Handling:
- Require authorized actor and reason.
- Preserve full timeline and activity history.
- Prevent duplicate or conflicting payment outcomes.

---

# Integration Points

## Billing Workflow
Receives payment allocation outcomes and updates invoice statuses.

## Collector Workflow
Receives unpaid and partially paid visibility context, and closes active collection when settlement conditions are met.

## Notification Engine
Sends payment confirmations, failures, reminders, and status-change notifications.

## Timeline
Captures business-readable payment lifecycle events.

## Activity Log
Captures accountability and operational traceability for payment transitions.

## Customer Portal
Displays payment history, payment status, allocated invoices, and outstanding balances.

## Reporting
Consumes payment outcomes, allocation trends, and aging recovery signals.

## Settings Engine
Controls method policies, retries, allocation rules, reallocation authorization, and timeout behavior.

---

# Collector Handoff

Unpaid and partially paid invoices remain visible to Collector Workflow through payment status and outstanding balance context.

Successful payments that satisfy invoice balances must remove invoices from active collection queues according to policy.

Collector visibility and removal criteria are configurable through Settings Engine.

---

# Customer Portal

Customer can:

- View payment history
- View payment status
- View outstanding balance
- Download payment receipt (future)
- View allocated invoices

Customers must never modify payment records.

---

# Reporting Outputs

Payment Workflow exposes reporting including:

- Payment Volume
- Collection Rate
- Outstanding Balance
- Payment Success Rate
- Partial Payment Statistics
- Average Collection Time
- Aging Recovery

Reporting outputs must remain consistent with immutable payment history and auditable allocation history.

---

# Workflow Principles

- Payments are immutable.
- Allocations are auditable.
- Allocation may change, payment history may not.
- Every payment generates timeline events.
- Every payment generates activity logs.
- Duplicate payments must be prevented.
- Payment processing must be deterministic.
- Payment allocation must always be traceable.
- Every payment has a unique payment reference.

---

# Future Extensions

The following payment extensions are reserved for future design:

## Refund Workflow
Support controlled refund lifecycle linked to immutable payment history.

## Chargeback Workflow
Support dispute-driven payment reversal controls.

## Credit Balance
Support policy-driven customer credit management.

## Customer Wallet
Support wallet-style balance for future settlement models.

## Auto Reconciliation
Support automated settlement matching and discrepancy handling.

## Accounting Integration
Support synchronized accounting and financial posting workflows.

## Multi-Currency
Support controlled multi-currency payment operations.

## Installment Payment
Support split payment schedules under policy governance.

## Scheduled Payment
Support pre-scheduled payment execution and confirmation workflows.
