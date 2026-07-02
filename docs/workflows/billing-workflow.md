# Purpose

This document defines the complete billing lifecycle responsible for transforming active subscriptions into invoices that can be paid by customers.

Billing is responsible for:

- Determining billable items
- Calculating invoice amounts
- Creating immutable invoices
- Publishing invoices
- Triggering customer notifications
- Supporting payment allocation visibility
- Providing overdue information for collection and suspension workflows

Billing is not responsible for:

- Receiving payments
- Service provisioning
- Network monitoring
- Service suspension execution

---

# Scope

This workflow integrates with:

- Subscription Lifecycle
- Provisioning Workflow
- Payment Workflow
- Collector Workflow
- Notification Engine
- Customer Portal
- Activity Log
- Timeline
- Settings Engine
- Reporting

Billing begins only after a subscription reaches the Active state.

Billing ends when an invoice becomes:

- Paid
- Cancelled (before publication only)
- Written Off (future extension)

---

# Billing Principles

## Immutable Invoice
Published invoices must never be modified.

Corrections must be performed through future adjustment mechanisms.

---

## Price Snapshot
Invoice prices must be copied from billable items at billing time.

Future product price changes must never affect historical invoices.

---

## One Invoice Per Billing Period
Only one invoice may exist for one subscription within the same billing period.

---

## Billable Items
Billing is based on billable items rather than products.

Examples:

- Monthly Subscription
- Installation Fee
- Router Rental
- Static Public IP
- Additional ONU
- Additional Cable
- Penalty
- Manual Adjustment
- Third-Party Service

---

## Invoice-Level Tax and Discount
Taxes and discounts are applied at invoice level.

Invoice lines store:

- Quantity
- Unit Price Snapshot
- Line Total

---

## On-Demand PDF
Invoice records are stored permanently.

Invoice PDF files are generated only when requested.

PDF files are never stored permanently.

---

# Actor Matrix

The following matrix describes the primary actor responsibilities across the billing lifecycle.

| Actor | Responsible | Approver | Override | Read Only |
|---|---|---|---|---|
| Customer | No | No | No | Yes |
| Customer Service | Yes, for billing support and status communication | Yes, for customer-facing exceptions | Limited, by policy | Yes |
| Collector | Yes, for overdue follow-up and collection coordination | Yes, for collection outcomes | Limited, by policy | Yes |
| Finance | Yes, for billing governance and accounting review | Yes | Limited, by policy | Yes |
| Billing Engine | Yes, for automated cycle execution and invoice generation | No | No | Yes |
| System | Yes, for workflow orchestration, notifications, and status transitions | No | Limited, by policy | Yes |
| Administrator | Yes, for controlled exception handling | Yes | Yes | Yes |

The actor matrix describes workflow responsibility rather than record ownership.

---

# Billing Lifecycle States

## Waiting For Billing

### Description
The subscription is active and eligible for billing, but billing cycle execution has not started.

### Entry Validation
- Subscription is Active.
- Billing period is open.
- Billing policy is available through Settings Engine.

### Exit Validation
- Billing cycle trigger is reached.
- Eligible subscription is selected for billing run.

### Allowed Transitions
- Collect Billable Items
- Billing Closed

### Business Rules
- Billing starts only after activation.
- No invoice is created in this state.
- Eligibility checks must be deterministic.

### Generated Events
- Billing Eligibility Confirmed
- Billing Cycle Waiting

### Timeline Events
- Waiting for billing
- Billing eligibility confirmed
- Billing cycle pending

### Notifications
- Optional internal notification for cycle readiness.

### Customer Portal Visibility
- No new invoice yet.
- Existing billing history remains visible.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- No invoice impact.

### Automation Level
- Fully Automatic

### Owner
- Billing Engine

### Metrics
- Billing eligibility count
- Waiting duration
- Cycle readiness rate

### Rollback Policy
Return to prior active subscription billing context without changing historical billing records.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Collect Billable Items

### Description
The billing workflow collects all eligible billable items for the subscription and billing period.

### Entry Validation
- Subscription is Active.
- Billing period and cycle context are valid.
- Billable item definitions are available.

### Exit Validation
- Billable items have been collected.
- Missing or invalid items are flagged.

### Allowed Transitions
- Billing Validation
- Billing Closed

### Business Rules
- Billing is based on billable items, not product records.
- Item eligibility must be governed by policy.
- Item collection must preserve source traceability.

### Generated Events
- Billable Item Collection Started
- Billable Item Collection Completed

### Timeline Events
- Billable item collection started
- Billable items collected
- Collection result recorded

### Notifications
- Internal exception notification when required items are missing.

### Customer Portal Visibility
- No draft items are customer-editable.
- Customer sees only published billing artifacts.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Draft billing data prepared.

### Automation Level
- Fully Automatic

### Owner
- Billing Engine

### Metrics
- Item collection duration
- Missing item count
- Item eligibility error rate

### Rollback Policy
Discard current draft collection context and return to Waiting For Billing while preserving event history.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Billing Validation

### Description
The workflow validates billing prerequisites and collected item quality before calculation.

### Entry Validation
- Billable items have been collected.
- Billing period is valid and open.
- Currency and pricing policy context is available.

### Exit Validation
- Validation has passed.
- Validation failures are recorded and handled.

### Allowed Transitions
- Invoice Calculation
- Billing Closed

### Business Rules
- Validation must prevent duplicate invoices.
- Validation must enforce one invoice per period.
- Validation must block invalid or incomplete billing records.

### Generated Events
- Billing Validation Started
- Billing Validation Completed

### Timeline Events
- Billing validation started
- Billing validation completed
- Validation outcome recorded

### Notifications
- Internal validation failure notification when rules fail.

### Customer Portal Visibility
- No visible change until invoice publication.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Billing run continues only when validation succeeds.

### Automation Level
- Fully Automatic

### Owner
- Finance

### Metrics
- Validation success rate
- Validation failure count
- Duplicate prevention hit rate

### Rollback Policy
Return to Collect Billable Items for corrected data input while preserving validation history.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Invoice Calculation

### Description
The workflow calculates invoice totals using validated billable items and pricing snapshots.

### Entry Validation
- Billing validation has passed.
- Item quantity and unit price snapshot are available.
- Tax and discount policy values are available.

### Exit Validation
- Subtotal, tax, discount, and total are calculated.
- Total amount is positive and valid.

### Allowed Transitions
- Invoice Generated
- Billing Closed

### Business Rules
- Calculation must be deterministic and repeatable.
- Tax and discount apply at invoice level.
- Historical prices are snapshots from billing time.

### Generated Events
- Invoice Calculation Started
- Invoice Calculation Completed

### Timeline Events
- Invoice calculation started
- Invoice calculation completed
- Calculation summary recorded

### Notifications
- Optional internal alert for calculation failures.

### Customer Portal Visibility
- No published invoice yet.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Financial totals prepared for invoice generation.

### Automation Level
- Fully Automatic

### Owner
- Billing Engine

### Metrics
- Calculation duration
- Calculation failure count
- Average invoice amount

### Rollback Policy
Return to Billing Validation for recalculation after corrected inputs, preserving prior calculation records.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Invoice Generated

### Description
A draft invoice record has been generated with full billing data and immutable snapshots prepared.

### Entry Validation
- Invoice calculation has completed.
- Unique invoice number can be reserved.
- Mandatory invoice fields are complete.

### Exit Validation
- Freeze checks are complete.
- Invoice is ready for publication or cancellation before publication.

### Allowed Transitions
- Invoice Published
- Billing Closed

### Business Rules
- Draft invoice may be cancelled only before publication.
- Snapshot data must be complete prior to publication.
- Duplicate invoice prevention remains enforced.

### Generated Events
- Invoice Generated
- Invoice Draft Prepared

### Timeline Events
- Invoice generated
- Draft invoice prepared
- Invoice freeze check pending

### Notifications
- Optional internal notification for invoice generation completion.

### Customer Portal Visibility
- Draft invoices are not visible to customers.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Draft invoice exists and awaits publication decision.

### Automation Level
- Fully Automatic

### Owner
- Billing Engine

### Metrics
- Draft generation count
- Draft-to-publish ratio
- Draft cancellation rate

### Rollback Policy
Cancel draft invoice before publication and return to Billing Validation or Waiting For Billing based on policy.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Invoice Published

### Description
The invoice has been published and becomes authoritative and immutable.

### Entry Validation
- Freeze policy checks have passed.
- Publication policy conditions are satisfied.
- Invoice metadata is complete.

### Exit Validation
- Invoice is available for payment workflow handoff.
- Publication notifications and logs are generated.

### Allowed Transitions
- Waiting Payment
- Overdue

### Business Rules
- Published invoice must never be modified.
- Published invoice is payable and visible to customer.
- Invoice PDF is generated on demand.

### Generated Events
- Invoice Published
- Invoice Frozen

### Timeline Events
- Invoice published
- Invoice frozen
- Invoice exposed for payment

### Notifications
- Customer invoice publication notification.
- Internal publication success notification.

### Customer Portal Visibility
- Invoice becomes visible.
- PDF download available on demand.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Receivable officially created.

### Automation Level
- Fully Automatic

### Owner
- Billing Engine

### Metrics
- Publication success rate
- Publish failure count
- Invoice visibility latency

### Rollback Policy
No direct rollback of published invoice content. Corrections must use future adjustment mechanisms.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Waiting Payment

### Description
The published invoice is waiting for payment confirmation from the payment workflow.

### Entry Validation
- Invoice is published.
- Due date and payable status are available.
- Payment workflow handoff has completed.

### Exit Validation
- Invoice receives full payment.
- Invoice receives partial payment.
- Invoice exceeds due and grace policy thresholds.

### Allowed Transitions
- Partially Paid
- Fully Paid
- Overdue

### Business Rules
- Billing workflow exposes payable invoice only.
- Payment allocation is outside billing workflow.
- Due and grace logic is policy-driven.

### Generated Events
- Invoice Waiting Payment
- Payment Handoff Completed

### Timeline Events
- Waiting for payment
- Payment handoff completed
- Payment status monitoring started

### Notifications
- Payment reminder notifications according to schedule.

### Customer Portal Visibility
- Invoice status shown as unpaid.
- Outstanding balance displayed.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Receivable remains open.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Days sales outstanding
- Payment wait duration
- Reminder delivery success rate

### Rollback Policy
Status rollbacks are not allowed unless payment status input is corrected by authoritative payment events.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Partially Paid

### Description
The invoice has received partial payment and remains open for remaining balance.

### Entry Validation
- At least one payment allocation exists.
- Remaining balance is greater than zero.

### Exit Validation
- Remaining balance reaches zero.
- Invoice becomes overdue by policy.

### Allowed Transitions
- Fully Paid
- Overdue
- Collection

### Business Rules
- Partial payment updates open balance only.
- Billing does not perform payment allocation itself.
- Outstanding balance must remain non-negative.

### Generated Events
- Partial Payment Recorded
- Invoice Balance Updated

### Timeline Events
- Partial payment recorded
- Outstanding balance updated
- Payment progress tracked

### Notifications
- Customer partial payment confirmation.
- Optional reminder for remaining balance.

### Customer Portal Visibility
- Shows partial paid status.
- Displays paid amount and remaining balance.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Receivable reduced but still open.

### Automation Level
- Fully Automatic

### Owner
- Finance

### Metrics
- Partial payment rate
- Average remaining balance
- Partial-to-full conversion time

### Rollback Policy
Balance corrections must come from authoritative payment reversal or adjustment events while preserving history.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Fully Paid

### Description
The invoice has been fully settled and no outstanding balance remains.

### Entry Validation
- Remaining balance is zero.
- Payment status is confirmed by payment workflow.

### Exit Validation
- Invoice can be closed in billing workflow.

### Allowed Transitions
- Billing Closed

### Business Rules
- Fully paid status is final for receivable closure.
- Published invoice content remains immutable.

### Generated Events
- Invoice Fully Paid
- Invoice Settlement Confirmed

### Timeline Events
- Invoice fully paid
- Settlement confirmed
- Invoice ready for closure

### Notifications
- Customer payment completion notification.
- Internal settlement confirmation.

### Customer Portal Visibility
- Shows paid status.
- Displays full payment history.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Receivable closed for this invoice.

### Automation Level
- Fully Automatic

### Owner
- Finance

### Metrics
- Full payment rate
- Settlement cycle time
- Collection efficiency

### Rollback Policy
Settlement status correction requires authoritative reversal events and must preserve historical payment trail.

### Maximum Duration (configurable)
No Maximum Duration.

## Overdue

### Description
The invoice has exceeded due date and grace period without full settlement.

### Entry Validation
- Invoice is unpaid or partially paid.
- Due date and grace policy threshold have passed.

### Exit Validation
- Invoice is paid.
- Invoice becomes eligible for collection workflow.

### Allowed Transitions
- Collection
- Fully Paid
- Partially Paid

### Business Rules
- Overdue is evaluated by policy and billing calendar.
- Overdue status feeds suspension and collection eligibility.
- Overdue tracking remains financial and does not directly execute service suspension.

### Generated Events
- Invoice Overdue
- Overdue Eligibility Recorded

### Timeline Events
- Invoice marked overdue
- Overdue age tracking started
- Collection eligibility evaluated

### Notifications
- Customer overdue notification.
- Internal overdue escalation notification.

### Customer Portal Visibility
- Shows overdue status and overdue age.
- Highlights outstanding balance.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Receivable remains open and aging increases.

### Automation Level
- Fully Automatic

### Owner
- Collector

### Metrics
- Overdue rate
- Aging bucket distribution
- Overdue recovery rate

### Rollback Policy
Overdue rollback is allowed only when due date, grace period, or payment status is corrected by authoritative source data.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Collection

### Description
The overdue invoice is actively handled by collection workflows.

### Entry Validation
- Invoice is overdue.
- Collection eligibility threshold has been reached.
- Collection policy conditions are satisfied.

### Exit Validation
- Payment is received.
- Collection effort is closed or escalated.

### Allowed Transitions
- Partially Paid
- Fully Paid
- Billing Closed

### Business Rules
- Collection eligibility must be configurable.
- Collection activity is traceable by timeline and activity logs.
- Collection does not modify immutable invoice content.

### Generated Events
- Collection Started
- Collection Outcome Recorded

### Timeline Events
- Collection started
- Collection attempt logged
- Collection outcome recorded

### Notifications
- Customer collection reminders.
- Internal collector task and escalation notifications.

### Customer Portal Visibility
- Shows collection-related status and outstanding amount.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Receivable remains open until settlement or closure policy outcome.

### Automation Level
- Semi Automatic

### Owner
- Collector

### Metrics
- Collection attempt count
- Collection success rate
- Average collection resolution time

### Rollback Policy
Collection state rollback is allowed only when collection entry criteria were evaluated incorrectly and must preserve all collection activity history.

### Maximum Duration (configurable)
Configurable through Settings Engine.

## Billing Closed

### Description
Billing lifecycle for the invoice is closed by final outcome.

### Entry Validation
- Invoice is fully paid, cancelled before publication, or written off by approved future extension.
- Closure reason is recorded.

### Exit Validation
- None.

### Allowed Transitions
- None.

### Business Rules
- Closed invoices remain available for audit and reporting.
- Published invoice content remains immutable after closure.
- Closure must preserve full financial and operational history.

### Generated Events
- Billing Closed
- Billing Closure Reason Recorded

### Timeline Events
- Billing closed
- Closure reason recorded
- Final billing status preserved

### Notifications
- Internal closure notification.
- Customer closure notification where applicable.

### Customer Portal Visibility
- Shows final status and historical record.

### Monitoring Impact
- No monitoring impact.

### Billing Impact
- Invoice receivable is closed by final business outcome.

### Automation Level
- Semi Automatic

### Owner
- Finance

### Metrics
- Closure rate
- Closure reason distribution
- Time to close

### Rollback Policy
Closure rollback is permitted only through explicit corrective financial workflows and must preserve full historical records.

### Maximum Duration (configurable)
No Maximum Duration.

---

# Billing Calendar

Billing scheduling is controlled through configurable settings.

## Billing Date
The date when billing cycle processing starts for eligible subscriptions.

## Invoice Date
The date assigned to generated invoices for the billing period.

## Due Date
The payment deadline for each published invoice.

## Grace Period
Additional configurable window after due date before overdue and suspension-candidate evaluations apply.

## Suspend Candidate Date
Policy-driven date when unpaid invoices become eligible to feed suspension-candidate workflows.

All billing calendar values are configurable through the Settings Engine.

---

# Invoice Generation

Invoice generation follows a controlled, repeatable sequence.

Determine Eligible Subscriptions

↓

Collect Billable Items

↓

Price Snapshot

↓

Validate Data

↓

Calculate Invoice

↓

Apply Tax

↓

Apply Discount

↓

Generate Invoice

↓

Freeze Invoice

↓

Publish Invoice

↓

Create Timeline

↓

Create Activity Log

↓

Send Notification

---

# Invoice Validation

Billing validation includes at minimum:

- Subscription Active
- One Invoice Per Period
- Valid Billing Period
- Valid Billable Items
- Valid Currency
- Positive Total Amount
- Duplicate Prevention

Validation failures must block publication and generate traceable workflow events.

---

# Invoice Freeze Policy

After publication, the following fields must become immutable:

- Invoice Number
- Invoice Date
- Invoice Lines
- Price Snapshot
- Tax
- Discount
- Total

Future corrections must not modify the published invoice.

---

# Retry Policy

Retry behavior is configurable through the Settings Engine.

## Invoice generation failure
The workflow may retry generation after transient failures, while preserving prior failure history.

## Notification failure
Notification delivery retries may run independently from invoice publication success.

## PDF generation failure
On-demand PDF retries may run without altering invoice data.

## Publish failure
Publish retries may be attempted before publication succeeds, with duplicate prevention still enforced.

Retry policy controls should include configurable intervals, maximum retries, and manual override capability.

---

# Exception Handling

## Missing Billable Item
Handling:
- Record missing item details.
- Block invoice publication.
- Trigger correction workflow.

## Duplicate Invoice
Handling:
- Block duplicate generation.
- Preserve duplicate attempt evidence.
- Escalate for review when needed.

## Invalid Price
Handling:
- Block calculation and publication.
- Record invalid price source.
- Route for financial correction.

## Missing Customer
Handling:
- Block invoice generation.
- Record customer data inconsistency.
- Route to customer data correction workflow.

## Billing Period Closed
Handling:
- Reject new invoice generation in closed period.
- Record closure conflict event.
- Require authorized exception handling.

## Notification Failed
Handling:
- Preserve invoice publication status.
- Retry notification per policy.
- Record delivery failure for audit.

## Publish Failed
Handling:
- Keep invoice in pre-publish state.
- Retry publish process.
- Preserve all failure events.

## Manual Override
Handling:
- Require authorized actor and reason.
- Preserve complete timeline and activity history.
- Ensure duplicate prevention still applies.

---

# Integration Points

## Subscription Lifecycle
Provides activation status and receives billing-driven overdue context.

## Payment Workflow
Receives published payable invoices and returns payment status updates.

## Collector Workflow
Receives overdue and collection-eligible invoices.

## Notification Engine
Handles billing reminders, publication notices, and overdue notices.

## Timeline
Captures business-readable invoice lifecycle events.

## Activity Log
Captures accountability and operational traceability for billing transitions.

## Customer Portal
Displays published invoices, payment status, billing history, and outstanding balances.

## Reporting
Consumes billing outcomes for financial and operational analytics.

## Settings Engine
Controls billing calendar, retry policies, overdue rules, and collection eligibility.

---

# Payment Handoff

Published invoices become available to Payment Workflow as payable financial records.

Payment allocation is outside Billing Workflow.

Billing exposes payable invoices and receives payment state outcomes from Payment Workflow.

---

# Collection Handoff

Overdue invoices become eligible for collection when configurable collection criteria are met.

Collection eligibility and escalation timing must be configurable through Settings Engine.

Billing provides overdue and aging context to collector workflows without transferring invoice ownership semantics.

---

# Customer Portal

Customer portal visibility includes:

- View invoices
- Download PDF (generated on demand)
- View payment status
- View billing history
- View outstanding balance

Customer cannot modify invoices.

---

# Reporting Outputs

Billing exposes data for reporting including:

- Revenue
- Outstanding Balance
- Overdue Aging
- Invoice Count
- Collection Performance
- Billing Success Rate

Reporting outputs must remain consistent with immutable invoice history.

---

# Workflow Principles

- Billing is deterministic.
- Billing must be repeatable.
- Every invoice is immutable after publication.
- Every invoice generates timeline events.
- Every invoice generates activity logs.
- Every invoice has a unique invoice number.
- Historical invoices never change.
- Billing never depends on current product prices.
- Billing never creates duplicate invoices.
- Billing must be fully auditable.

---

# Future Extensions

The following billing extensions are reserved for future design:

## Credit Note
Support corrective financial reduction without modifying published invoices.

## Debit Note
Support corrective financial addition without modifying published invoices.

## Billing Adjustment
Support controlled post-publication adjustment workflows.

## Promotional Billing
Support policy-driven promotional charges and discounts.

## Usage-Based Billing
Support usage-derived charging models.

## Bundle Billing
Support grouped multi-item billing packages.

## Multi-Currency
Support controlled currency expansion beyond base currency policy.

## Electronic Tax Invoice
Support tax-document lifecycle integration.

## Accounting Integration
Support synchronized financial posting to accounting workflows.

## Revenue Recognition
Support policy-based revenue recognition outputs and controls.
