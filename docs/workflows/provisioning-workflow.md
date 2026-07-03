# Purpose

This document defines the complete provisioning lifecycle responsible for transforming an installed subscription into an operational internet service.

Provisioning is responsible for resource allocation, service activation, validation, rollback, monitoring handoff, and synchronization with external systems.

Provisioning is not responsible for billing.

Billing starts only after successful service activation.

---

# Scope

This workflow integrates with:

- Subscription Lifecycle
- Installation
- Network Inventory
- OLT
- Router
- Radius / Authentication
- Monitoring
- Notification Engine
- Timeline
- Activity Log
- Customer Portal
- Settings Engine

The provisioning workflow begins immediately after Installation Completed and ends only after Subscription Activated or Provisioning Failed.

---

# Actor Matrix

The following matrix describes the primary actor responsibilities across the provisioning lifecycle.

| Actor | Responsible | Approver | Override | Read Only |
|---|---|---|---|---|
| Customer | No | No | No | Yes |
| Customer Service | Yes, for customer coordination and status handling | Yes, for business approvals when required | Limited, by policy | Yes |
| Technician | Yes, for field-side provisioning support and equipment readiness | Yes, for operational confirmation | No | Yes |
| NOC | Yes, for network readiness and service validation | Yes, for network-side approvals | Limited, by policy | Yes |
| Billing | Yes, for activation dependency confirmation and policy coordination | Yes, for activation approval when required | No | Yes |
| System | Yes, for automatic execution, retry orchestration, and event processing | No | Limited, by policy | Yes |
| Administrator | Yes, for exception handling and controlled intervention | Yes | Yes | Yes |

The actor matrix describes workflow responsibility rather than record ownership.

---

# Lifecycle States

## Waiting For Provisioning

### Description
The provisioning request has been accepted, but execution has not started.

### Entry Validation
- Installation Completed has been recorded.
- The provisioning request has been accepted.
- Required subscription data is available.

### Exit Validation
- Resource allocation can begin.
- The request can be queued for execution.

### Allowed Transitions
- Resource Allocation
- Provisioning Failed
- Rollback

### Business Rules
- Provisioning does not begin before installation completion.
- Billing does not start in this state.
- The request must carry a traceable business reference.

### Generated Events
- Provisioning Requested
- Provisioning Accepted

### Timeline Events
- Provisioning request accepted
- Waiting for provisioning
- Provisioning handoff recorded

### Notifications
- Internal provisioning intake notification.
- Optional customer status update when configured.

### Monitoring Impact
- No monitoring registration occurs yet.

### Billing Impact
- No billing impact.

### Automation Level
- Semi Automatic

### Owner
- System

### Metrics
- Queue intake time
- Request acceptance rate
- Waiting duration

### Rollback Policy
Return to the prior installation-complete business state if the request is invalid.

### Maximum Duration
Configurable through the Settings Engine.

## Resource Allocation

### Description
The workflow allocates the logical and physical resources required for service delivery.

### Entry Validation
- The subscription is eligible for provisioning.
- Required service data is available.
- Resource allocation rules are configured.

### Exit Validation
- Required resources have been reserved or assigned.
- Allocation failures have been handled.

### Allowed Transitions
- Provisioning Queue
- Provisioning Failed
- Rollback

### Business Rules
- Resource allocation rules must be configurable.
- Allocation may include OLT, PON Port, ONU or ONT, Service Profile, VLAN, IP Pool, and Authentication Profile.
- Allocation must preserve the association between the subscription and the selected service resources.

### Generated Events
- Resource Allocation Started
- Resource Allocation Completed

### Timeline Events
- Resource allocation started
- Resources assigned
- Allocation completed

### Notifications
- Internal provisioning and network readiness notification.
- Optional customer update when configured.

### Monitoring Impact
- Monitoring registration planning may begin after resources are confirmed.

### Billing Impact
- No billing impact.

### Automation Level
- Semi Automatic

### Owner
- System

### Metrics
- Resource allocation duration
- Allocation retry count
- Allocation failure count

### Rollback Policy
Release allocated resources when the allocation step fails and preserve the failure reason.

### Maximum Duration
Configurable through the Settings Engine.

## Provisioning Queue

### Description
The provisioning request is waiting for execution.

### Entry Validation
- Resources have been allocated or the request is ready for queue placement.
- The workflow is eligible for execution.

### Exit Validation
- The request has been dequeued for execution.
- The queue decision has been approved or retried.

### Allowed Transitions
- Provisioning In Progress
- Provisioning Failed
- Rollback

### Business Rules
- The queue must support retry, prioritization, and manual intervention.
- Queue ordering must be policy-driven.
- Queue state must preserve request correlation.
- Manual intervention may enqueue provisioning intent through the `provision:onu` command when policy allows.

### Generated Events
- Provisioning Queued
- Provisioning Prioritized
- Provisioning Requeued

### Timeline Events
- Provisioning queued
- Provisioning prioritized
- Queue position updated

### Notifications
- Internal queue status notification.
- Exception notification if the request remains queued beyond policy limits.

### Monitoring Impact
- No monitoring registration occurs yet.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Queue wait time
- Queue depth
- Retry count

### Rollback Policy
Return to Waiting For Provisioning or Resource Allocation depending on the last valid business step.

### Maximum Duration
Configurable through the Settings Engine.

## Provisioning In Progress

### Description
Provisioning commands are being executed against the required systems.

### Entry Validation
- The request has left the queue.
- Required resources are reserved.
- The execution path is available.

### Exit Validation
- Execution steps have completed successfully.
- Any execution failure has been recorded.

### Allowed Transitions
- Validation
- Provisioning Failed
- Rollback

### Business Rules
- Execution may involve multiple external systems.
- Provisioning must remain traceable across each external interaction.
- Execution should be idempotent whenever possible.

### Generated Events
- Provisioning Started
- Provisioning Step Completed

### Timeline Events
- Provisioning started
- Provisioning step completed
- External system interaction recorded

### Notifications
- Internal progress notification.
- Failure notification if any command fails.

### Monitoring Impact
- Monitoring registration may be prepared after command execution succeeds.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Provisioning execution time
- External call success rate
- Partial success count

### Rollback Policy
Compensate previously completed provisioning steps without removing historical records.

### Maximum Duration
Configurable through the Settings Engine.

## Validation

### Description
The workflow validates that provisioning completed successfully.

### Entry Validation
- Provisioning commands have been executed.
- Validation inputs are available.
- Required verification targets are reachable when applicable.

### Exit Validation
- Service has been verified operational.
- Validation has either succeeded or failed.

### Allowed Transitions
- Monitoring Registration
- Provisioning Failed
- Rollback

### Business Rules
- Validation must confirm service readiness before activation.
- Validation includes operational checks, not billing checks.
- Validation must preserve diagnostic evidence.

### Generated Events
- Provisioning Validation Started
- Provisioning Validation Completed

### Timeline Events
- Provisioning validation started
- Provisioning validation completed
- Validation findings recorded

### Notifications
- Internal validation result notification.
- Failure notification when validation does not pass.

### Monitoring Impact
- Monitoring readiness is confirmed or blocked by validation outcome.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- NOC

### Metrics
- Validation success rate
- Validation duration
- Validation failure count

### Rollback Policy
Return to Provisioning In Progress if the validation failure can be corrected without changing business intent.

### Maximum Duration
Configurable through the Settings Engine.

## Monitoring Registration

### Description
The service is registered into the Monitoring Engine.

### Entry Validation
- Validation has succeeded.
- The service is eligible for monitoring onboarding.
- Monitoring registration configuration is available.

### Exit Validation
- Monitoring registration has completed successfully.
- Registration failure has been handled.

### Allowed Transitions
- Ready For Activation
- Provisioning Failed
- Rollback

### Business Rules
- Monitoring registration must occur before service activation.
- Monitoring registration creates operational visibility for the new service.
- Monitoring registration must be traceable.

### Generated Events
- Monitoring Registration Started
- Monitoring Registration Completed

### Timeline Events
- Monitoring registration started
- Monitoring registration completed
- Monitoring handoff recorded

### Notifications
- Internal monitoring readiness notification.
- Failure notification if registration cannot be completed.

### Monitoring Impact
- Monitoring ownership is transferred to the Monitoring workflow.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- NOC

### Metrics
- Monitoring registration time
- Registration success rate
- Monitoring readiness delay

### Rollback Policy
Deregister monitoring references where possible and preserve the registration history.

### Maximum Duration
Configurable through the Settings Engine.

## Ready For Activation

### Description
Provisioning has completed successfully and the workflow is waiting for activation approval if required by business policy.

### Entry Validation
- Provisioning has been completed.
- Monitoring registration has been completed.
- The service is ready for activation.

### Exit Validation
- Activation approval has been granted, or activation may proceed automatically.

### Allowed Transitions
- Activated
- Provisioning Failed
- Rollback

### Business Rules
- This state is the final checkpoint before service activation.
- Billing still does not start in this state.
- Activation policy may require human approval.

### Generated Events
- Provisioning Ready For Activation

### Timeline Events
- Ready for activation
- Activation approval pending
- Activation readiness confirmed

### Notifications
- Internal activation readiness notification.
- Optional customer update when configured.

### Monitoring Impact
- Monitoring remains registered and ready.

### Billing Impact
- No billing impact until activation.

### Automation Level
- Semi Automatic

### Owner
- Billing

### Metrics
- Ready-for-activation aging
- Approval wait time
- Activation readiness rate

### Rollback Policy
Return to Monitoring Registration if activation cannot proceed and no service has been activated.

### Maximum Duration
Configurable through the Settings Engine.

## Activated

### Description
Provisioning workflow completed successfully and service activation has been confirmed.

### Entry Validation
- Monitoring registration has completed.
- Activation approval has been granted or not required.
- Service activation can proceed.

### Exit Validation
- Subscription Lifecycle has been notified.
- Activation has been recorded.

### Allowed Transitions
- Provisioning Failed
- Rollback

### Business Rules
- Provisioning success is required before Subscription Activation.
- Monitoring registration must complete before activation.
- Billing starts only after service activation.
- The workflow must notify Subscription Lifecycle on completion.

### Generated Events
- Service Activated
- Subscription Lifecycle Notified

### Timeline Events
- Service activated
- Subscription lifecycle notified
- Activation completion recorded

### Notifications
- Subscription lifecycle activation notice.
- Optional customer activation notice.

### Monitoring Impact
- Monitoring continues under active service supervision.

### Billing Impact
- Billing may begin after Subscription Lifecycle accepts the activation handoff.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Time to activate
- Activation success rate
- End-to-end provisioning completion time

### Rollback Policy
If activation is reversed before service use is established, return to Ready For Activation or the last valid pre-activation state while preserving audit history.

### Maximum Duration
No Maximum Duration

## Provisioning Failed

### Description
Provisioning could not be completed successfully.

### Entry Validation
- A provisioning step has failed or a fatal exception has occurred.
- Failure reason has been recorded.

### Exit Validation
- Failure handling has been completed.
- Retry or rollback actions have been evaluated.

### Allowed Transitions
- Retry to Queue
- Rollback

### Business Rules
- The workflow must preserve the failure reason.
- Retry policy must be configurable.
- A failed provisioning workflow does not activate service.

### Generated Events
- Provisioning Failed
- Failure Reason Recorded

### Timeline Events
- Provisioning failed
- Failure reason recorded
- Failure handling started

### Notifications
- Internal failure notification.
- Optional customer notification when the failure affects service delivery timing.

### Monitoring Impact
- Monitoring registration remains incomplete or is reverted according to policy.

### Billing Impact
- No billing impact.

### Automation Level
- Semi Automatic

### Owner
- Administrator

### Metrics
- Failure count
- Retry success rate
- Mean time to recovery

### Rollback Policy
Compensate prior successful provisioning steps and preserve all timeline and audit history.

### Maximum Duration
Configurable through the Settings Engine.

## Rollback

### Description
Rollback reverses previously executed provisioning actions when required.

### Entry Validation
- A rollback condition has been approved or triggered by policy.
- Steps to be compensated are known.

### Exit Validation
- Compensating actions have been executed.
- Historical records remain intact.

### Allowed Transitions
- Waiting For Provisioning
- Resource Allocation
- Provisioning Failed

### Business Rules
- Rollback must preserve audit history.
- Rollback must never remove timeline history.
- Rollback should compensate business effects rather than erase evidence of the prior attempt.

### Generated Events
- Rollback Started
- Rollback Completed

### Timeline Events
- Rollback started
- Rollback completed
- Compensating action recorded

### Notifications
- Internal rollback notification.
- Failure notification if rollback cannot be completed.

### Monitoring Impact
- Monitoring registration is reversed or withheld according to the rollback point.

### Billing Impact
- No billing impact.

### Automation Level
- Semi Automatic

### Owner
- Administrator

### Metrics
- Rollback count
- Rollback success rate
- Compensating action count

### Rollback Policy
Rollback is the compensating mechanism and therefore does not roll back itself unless the rollback attempt fails and must be retried from the last safe state.

### Maximum Duration
Configurable through the Settings Engine.

---

# State Transition Diagram

```mermaid
stateDiagram-v2
    [*] --> "Waiting For Provisioning"
    "Waiting For Provisioning" --> "Resource Allocation" : normal flow
    "Resource Allocation" --> "Provisioning Queue" : normal flow
    "Provisioning Queue" --> "Provisioning In Progress" : retry flow / normal flow
    "Provisioning In Progress" --> Validation : normal flow
    Validation --> "Monitoring Registration" : normal flow
    "Monitoring Registration" --> "Ready For Activation" : normal flow
    "Ready For Activation" --> Activated : normal flow
    Activated --> [*] : completion

    "Waiting For Provisioning" --> "Provisioning Failed" : failure flow
    "Resource Allocation" --> "Provisioning Failed" : failure flow
    "Provisioning Queue" --> "Provisioning Failed" : queue timeout / failure flow
    "Provisioning In Progress" --> "Provisioning Failed" : failure flow
    Validation --> "Provisioning Failed" : failure flow
    "Monitoring Registration" --> "Provisioning Failed" : failure flow
    "Ready For Activation" --> "Provisioning Failed" : policy or approval failure

    "Provisioning Failed" --> "Provisioning Queue" : retry flow
    "Provisioning Failed" --> Rollback : manual override / rollback flow
    Rollback --> "Waiting For Provisioning" : rollback flow
    Rollback --> "Resource Allocation" : rollback flow
    Rollback --> "Provisioning Failed" : rollback failed

    "Ready For Activation" --> Activated : manual override
    "Provisioning In Progress" --> Rollback : manual override
    Validation --> Rollback : manual override
    "Monitoring Registration" --> Rollback : manual override
```

---

# Retry Policy

Provisioning retry behavior is configurable through the Settings Engine.

## Retry Interval
Retry intervals must be configurable and may vary by failure type, queue priority, and business policy.

## Maximum Retries
The maximum number of retries must be configurable and may differ between automatic and manual retry paths.

## Exponential Backoff Support
Exponential backoff may be enabled for retryable external failures when business policy allows it.

## Manual Retry
Administrators and authorized operators may request a manual retry when automation cannot continue.

## Automatic Retry
The System may automatically retry eligible provisioning steps according to configured retry policy.

Retry behavior must preserve the original failure record and must not remove timeline or audit history.

---

# Rollback Strategy

Rollback restores business consistency without deleting historical records.

Rollback actions should compensate previously successful provisioning steps rather than erase history.

The rollback strategy must preserve all traceable evidence of the original attempt, including timing, failure reason, and operator involvement.

Rollback should stop short of any action that would hide the fact that provisioning had partially completed.

---

# External Integrations

Provisioning interacts with external systems in a vendor-neutral manner.

## Router
Used for service profile application, connectivity checks, and service enablement.

## OLT
Used for access-port association, optical service assignment, and service availability checks.

## Authentication Service
Used for authentication profile application and service access validation.

## Monitoring Engine
Used to register service monitoring before activation is confirmed.

## Notification Engine
Used to publish provisioning progress, success, and failure events.

## Timeline
Used to record business progress, exceptions, retries, and rollback history.

## Activity Log
Used to capture operational accountability and traceable changes.

## Settings Engine
Used to control allocation rules, retry behavior, activation approval, and rollback policy.

All external interactions should remain asynchronous whenever possible and must be traceable.

---

# Monitoring Handoff

Successful provisioning transfers responsibility to the Monitoring Workflow after monitoring registration is complete and the service is ready for activation.

The provisioning workflow must not mark the service activated until monitoring registration has succeeded according to policy.

Monitoring handoff must preserve the provisioning history and create an explicit transition record for operational traceability.

---

# Exception Handling

## Resource Allocation Failed
Resource allocation could not reserve the required logical or physical resources.

Handling:
- Preserve the allocation failure reason.
- Notify responsible internal teams.
- Return to a safe prior state or queue the request for retry.

## Queue Timeout
The provisioning request remained in queue beyond the configured threshold.

Handling:
- Record the timeout event.
- Notify internal operations.
- Retry, reprioritize, or escalate according to policy.

## External System Offline
A required external system was not available during execution.

Handling:
- Record the dependency outage.
- Notify the responsible operator group.
- Retry when the dependency becomes available or trigger rollback if necessary.

## Device Unreachable
A target device could not be reached during provisioning.

Handling:
- Record the connectivity failure.
- Notify operations and NOC.
- Retry or rollback according to policy.

## Authentication Failed
Service authentication could not be validated successfully.

Handling:
- Record the authentication failure.
- Notify internal teams.
- Prevent activation until the issue is resolved.

## Validation Failed
Provisioning validation did not confirm service readiness.

Handling:
- Record the validation failure.
- Keep the service from activation.
- Retry or rollback according to policy.

## Monitoring Registration Failed
Monitoring registration could not be completed before activation.

Handling:
- Record the failure.
- Prevent activation until monitoring readiness is resolved.
- Escalate to internal operations.

## Partial Success
Some provisioning actions succeeded while others failed.

Handling:
- Preserve all successful step records.
- Execute compensating rollback where required.
- Notify internal teams of the partial completion state.

## Rollback Failed
Compensating actions did not complete successfully.

Handling:
- Preserve the failure trail.
- Escalate to an administrator.
- Require manual review before another attempt.

## Manual Override
An authorized operator intervened outside the normal automated path.

Handling:
- Record the override reason.
- Preserve the previous valid state.
- Require audit and timeline entry.

---

# Workflow Principles

- Idempotent execution.
- Every provisioning request has a unique correlation ID.
- External systems should be accessed asynchronously.
- Provisioning must be retryable.
- Rollback must preserve business history.
- Every transition generates timeline events.
- Every transition generates audit logs.
- Every external interaction is traceable.
- Provisioning success is required before Subscription Activation.
- Monitoring registration must complete before activation.

---

# Future Extensions

The following provisioning extensions are reserved for future design:

## Package Upgrade
Support upgrading a service package through controlled provisioning changes.

## Package Downgrade
Support downgrading a service package through controlled provisioning changes.

## ONU Replacement
Support replacing an ONU while preserving service history and traceability.

## OLT Migration
Support moving a service between OLTs under controlled operational policy.

## FAT Migration
Support relocating the field access termination reference when network design changes.

## Address Relocation
Support changing the service location while preserving business history.

## Multi-Service Provisioning
Support provisioning more than one service type under a customer account.

## Zero-Touch Provisioning (ZTP)
Support policy-driven automated provisioning onboarding.

## Vendor-Specific Adapters
Reserve space for future vendor adapters without changing the business workflow.
