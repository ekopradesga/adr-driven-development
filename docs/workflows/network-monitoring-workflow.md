# Purpose

This document defines the complete Network Monitoring Workflow responsible for observing network infrastructure, customer connectivity, and service health.

Monitoring is responsible for observation.

Monitoring is NOT responsible for:

- Repair
- Billing
- Provisioning
- Service suspension

Monitoring provides reliable operational data for other workflows.

---

# Scope

The workflow covers monitoring for:

- Core Router
- Distribution Router
- OLT
- ODF
- FAT
- Dropcore
- ONT
- Customer Connectivity

The workflow integrates with:

- Subscription Lifecycle
- Provisioning Workflow
- Notification Engine
- Timeline
- Activity Log
- Ticket Workflow
- Customer Portal
- Reporting
- Settings Engine

---

# Monitoring Principles

## Observation Before Diagnosis
Monitoring collects facts.

Diagnosis is produced by analysis rules.

---

## Vendor Agnostic
Monitoring never depends on a specific vendor.

---

## Hierarchical Monitoring
Support hierarchical topology.

Example:

Core Router

↓

Distribution

↓

OLT

↓

ODF

↓

FAT

↓

Dropcore

↓

ONT

---

## Parent Child Relationship
Health may propagate.

Example:

OLT Offline

↓

All child ONTs become Unknown

not

Offline

---

## Correlation Engine
Support fault correlation.

Example:

Many ONTs

↓

Same FAT

↓

Possible FAT Issue

---

## Warning Levels
Support:

- Healthy
- Warning
- Critical
- Unknown
- Maintenance

---

## Monitoring Never Changes Business Data
Monitoring never edits:

- Customers
- Invoices
- Payments
- Subscriptions

---

# Monitoring Lifecycle

## Unknown

### Description
The asset or connectivity context exists but monitoring confidence is not yet established.

### Entry Validation
- Asset or service context is present.
- Monitoring context lacks sufficient observations.

### Exit Validation
- Discovery confirms monitorable target.
- Monitoring context is initialized.

### Allowed Transitions
- Discovered
- Archived

### Business Rules
- Unknown is a valid non-failure state.
- Unknown does not imply outage root cause.

### Generated Events
- Monitoring Unknown Detected
- Unknown State Registered

### Timeline
- Monitoring state unknown
- Unknown state registered
- Awaiting discovery

### Notifications
- Optional internal unknown-state alert based on policy.

### Customer Portal Visibility
- Customer may see service status as unknown when policy allows.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Unknown state count
- Unknown duration
- Unknown-to-discovered conversion rate

### Rollback Policy
Return to last valid observed state when delayed telemetry arrives, preserving full history.

### Maximum Duration
Configurable through Settings Engine.

## Discovered

### Description
Monitoring target is identified, registered, and ready for active observation.

### Entry Validation
- Monitorable target identity is confirmed.
- Topology relationship is known.
- Monitoring rules are available.

### Exit Validation
- Monitoring collection starts.
- Discovery failure is handled.

### Allowed Transitions
- Monitoring
- Unknown
- Archived

### Business Rules
- Discovery must map parent-child relationships.
- Discovery must remain vendor-agnostic.

### Generated Events
- Target Discovered
- Discovery Registered

### Timeline
- Target discovered
- Discovery registered
- Monitoring onboarding started

### Notifications
- Internal discovery notification for operations.

### Customer Portal Visibility
- No customer-visible diagnosis details.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Discovery success rate
- Discovery duration
- Discovery failure count

### Rollback Policy
Revert to Unknown if discovery data is invalid, preserving discovery attempt records.

### Maximum Duration
Configurable through Settings Engine.

## Monitoring

### Description
The target is under active observation and receives periodic or event-driven monitoring updates.

### Entry Validation
- Target has been discovered.
- Monitoring sources are configured.
- Health scoring policy is active.

### Exit Validation
- Health state is classified.
- Monitoring is paused, archived, or moved to maintenance.

### Allowed Transitions
- Healthy
- Warning
- Critical
- Unknown
- Maintenance
- Archived

### Business Rules
- Observation data collection is separate from diagnosis inference.
- Monitoring must remain traceable and policy-driven.

### Generated Events
- Monitoring Started
- Monitoring Sample Captured

### Timeline
- Monitoring started
- Monitoring sample captured
- Health evaluation triggered

### Notifications
- Internal monitoring-start notification when configured.

### Customer Portal Visibility
- Customer may see high-level service monitoring availability status.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- System

### Metrics
- Sampling interval compliance
- Data completeness
- Monitoring continuity rate

### Rollback Policy
Return to Discovered when active monitoring configuration is invalidated, preserving all collected telemetry history.

### Maximum Duration
No Maximum Duration.

## Healthy

### Description
Observed metrics are within acceptable thresholds and no active risk condition is detected.

### Entry Validation
- Monitoring data is available.
- Health score meets healthy threshold.

### Exit Validation
- Warning, critical, maintenance, unknown, or archival condition is detected.

### Allowed Transitions
- Warning
- Critical
- Maintenance
- Unknown
- Archived

### Business Rules
- Healthy status is derived from configurable thresholds.
- Healthy parent does not guarantee healthy children.

### Generated Events
- Health Status Healthy
- Healthy State Confirmed

### Timeline
- Health changed to healthy
- Healthy state confirmed
- Stability trend recorded

### Notifications
- Optional recovery confirmation notification by policy.

### Customer Portal Visibility
- Service status shown as healthy where policy allows.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- NOC

### Metrics
- Healthy duration
- Stability score
- Healthy transition frequency

### Rollback Policy
Restore previous health classification only when classification input is corrected, preserving timeline integrity.

### Maximum Duration
No Maximum Duration.

## Warning

### Description
Observed conditions indicate elevated risk but not critical failure.

### Entry Validation
- Monitoring data indicates threshold warning condition.
- Warning rule correlation is satisfied.

### Exit Validation
- Condition recovers, worsens, or moves to maintenance.

### Allowed Transitions
- Healthy
- Critical
- Maintenance
- Unknown
- Archived

### Business Rules
- Warning thresholds are configurable.
- Warning should not imply automatic repair action.
- Correlation may generate probable upstream issue signals.

### Generated Events
- Warning Raised
- Warning Correlation Evaluated

### Timeline
- Warning raised
- Warning correlation evaluated
- Warning status updated

### Notifications
- Internal warning notification and escalation by policy.

### Customer Portal Visibility
- Customer may see warning-level service degradation notice if allowed.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- NOC

### Metrics
- Warning count
- Warning-to-critical conversion rate
- Warning resolution time

### Rollback Policy
Return to Healthy when warning indicators normalize; retain full warning event history.

### Maximum Duration
Configurable through Settings Engine.

## Critical

### Description
Observed conditions indicate high-impact service or infrastructure risk requiring urgent operational response.

### Entry Validation
- Critical threshold conditions are met.
- Severity policy classifies impact as critical.

### Exit Validation
- Condition recovers to warning or healthy.
- Maintenance takeover is initiated.

### Allowed Transitions
- Warning
- Healthy
- Maintenance
- Unknown
- Archived

### Business Rules
- Critical status must be traceable and severity-classified.
- Monitoring may recommend ticket candidates but does not execute repair.

### Generated Events
- Critical Alarm Raised
- Critical Correlation Evaluated

### Timeline
- Critical alarm raised
- Correlation evaluated
- Critical state updated

### Notifications
- Internal critical alert and escalation notification.

### Customer Portal Visibility
- Customer may see outage or critical service status as configured.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- NOC

### Metrics
- Critical incident count
- Mean time to detect
- Mean time to recover indicator

### Rollback Policy
Return to Warning or Healthy when critical inputs clear, preserving all critical event history.

### Maximum Duration
Configurable through Settings Engine.

## Maintenance

### Description
Monitoring target is under planned or approved maintenance and health signals are interpreted within maintenance context.

### Entry Validation
- Maintenance window or approved maintenance context exists.
- Maintenance scope is defined.

### Exit Validation
- Maintenance ends and normal monitoring resumes.

### Allowed Transitions
- Monitoring
- Recovered
- Unknown
- Archived

### Business Rules
- Maintenance suppresses non-actionable alert noise according to policy.
- Maintenance does not modify business records.

### Generated Events
- Maintenance Started
- Maintenance Context Applied

### Timeline
- Maintenance started
- Maintenance context applied
- Maintenance scope recorded

### Notifications
- Internal maintenance notification.
- Optional customer maintenance advisory.

### Customer Portal Visibility
- Customer may see maintenance banner or status according to policy.

### Billing Impact
- No billing impact.

### Automation Level
- Semi Automatic

### Owner
- NOC

### Metrics
- Maintenance count
- Maintenance duration
- Maintenance overrun rate

### Rollback Policy
Revert to prior monitorable state when maintenance context was applied incorrectly, retaining full maintenance history.

### Maximum Duration
Configurable through Settings Engine.

## Recovered

### Description
A previously warning or critical condition has returned to stable monitorable conditions.

### Entry Validation
- Prior degraded condition exists.
- Recovery indicators satisfy configured thresholds.

### Exit Validation
- Target stabilizes as healthy monitoring context.
- Degradation recurs and re-enters warning or critical.

### Allowed Transitions
- Healthy
- Warning
- Critical
- Monitoring
- Archived

### Business Rules
- Recovery must reference prior degraded event chain.
- Recovery does not imply root cause closure unless analysis confirms.

### Generated Events
- Device Recovered
- Recovery Confirmed

### Timeline
- Device recovered
- Recovery confirmed
- Recovery trend recorded

### Notifications
- Internal recovery notification.
- Optional customer recovery notice where policy allows.

### Customer Portal Visibility
- Customer may see service restored state.

### Billing Impact
- No billing impact.

### Automation Level
- Fully Automatic

### Owner
- NOC

### Metrics
- Recovery count
- Recovery time
- Reoccurrence rate

### Rollback Policy
Return to previous degraded state if recovery classification is invalidated, preserving full recovery history.

### Maximum Duration
Configurable through Settings Engine.

## Archived

### Description
Monitoring context is no longer active and is retained for historical and reporting needs.

### Entry Validation
- Target is retired, detached, or otherwise no longer monitored.
- Archive policy conditions are met.

### Exit Validation
- None.

### Allowed Transitions
- None.

### Business Rules
- Archived records remain available for audit and reporting.
- Archiving does not delete historical monitoring events.

### Generated Events
- Monitoring Archived
- Archive Completed

### Timeline
- Monitoring archived
- Archive completed
- Historical retention confirmed

### Notifications
- Internal archive completion notification when configured.

### Customer Portal Visibility
- Historical incident visibility depends on portal policy.

### Billing Impact
- No billing impact.

### Automation Level
- Semi Automatic

### Owner
- Administrator

### Metrics
- Archive count
- Archive processing time
- Retention compliance rate

### Rollback Policy
Archival reversal is allowed only through authorized restoration workflow while preserving archive and restore history.

### Maximum Duration
No Maximum Duration.

---

# Topology Model

Monitoring topology follows hierarchical relationships:

Core Router

↓

Distribution Router

↓

OLT

↓

ODF

↓

FAT

↓

Dropcore

↓

ONT

↓

Customer

Each node supports:

- GPS
- Parent
- Children
- Status
- Last Seen
- Health Score

Topology relationships are used for propagation and correlation, not for direct business data mutation.

---

# Monitoring Sources

Monitoring sources remain generic and implementation-agnostic.

Possible sources:

- Ping
- SNMP
- API
- MQTT
- Syslog
- Event Push
- Polling

Source selection, source weighting, and source trust policy are configurable through Settings Engine.

---

# Correlation Rules

Correlation rules identify probable upstream issues from observed event patterns.

Example:

Three ONTs

↓

Offline

↓

Same FAT

↓

Generate

Possible FAT Warning

Example:

Entire OLT unreachable

↓

Mark child devices Unknown

Do not automatically diagnose child failures.

Correlation must remain explainable and auditable.

---

# Health Calculation

Health scoring is configurable through Settings Engine.

Support:

- Availability
- Packet Loss
- Latency
- Device Reachability
- Alarm Count
- Historical Stability

Health score thresholds for Healthy, Warning, Critical, Unknown, and Maintenance are policy-controlled.

---

# Fault Propagation

Fault propagation follows parent-child topology context.

Healthy parent

does not imply

healthy child.

Failed parent

may produce

Unknown child state.

Propagation behavior must preserve original observed facts and correlated inference separately.

---

# Notification Integration

Monitoring generates events.

Notification Engine decides:

Who receives notifications

How

When

Escalation

Notification policy must be configurable and severity-aware.

---

# Timeline

Monitoring generates timeline events.

Examples:

- Device Recovered
- Health Changed
- Warning Raised
- Critical Alarm
- Maintenance Started
- Maintenance Ended

Timeline entries must remain business-readable and traceable.

---

# Ticket Integration

Monitoring never creates repair actions.

Monitoring may recommend:

Ticket Candidate

The Ticket Workflow decides whether a ticket is opened automatically or manually.

---

# Customer Portal

Customers may see:

- Service Status
- ONT Status
- Current Outage
- Maintenance
- Incident History (configurable)

Internal diagnosis is never exposed.

---

# Reporting

Support reporting for:

- Availability
- Device Health
- Alarm Frequency
- Recovery Time
- Outage Duration
- Network Stability
- Customer Availability

Reporting outputs must remain consistent with immutable event history and topology-aware context.

---

# Workflow Principles

- Monitoring observes.
- Diagnosis is inferred.
- Business data remains immutable.
- Topology is hierarchical.
- Health is configurable.
- Correlation rules are configurable.
- Monitoring is vendor agnostic.
- Timeline records every significant health event.

---

# Future Extensions

The following monitoring extensions are reserved for future design:

## AI Fault Prediction
Support predictive signal analysis for probable future faults.

## Capacity Forecasting
Support capacity trend analysis and threshold forecasting.

## Fiber Route Visualization
Support route-level monitoring overlays.

## Heat Map
Support geographic health density and outage heat visualization.

## Automatic Root Cause Analysis
Support explainable rule-based or model-assisted root cause suggestions.

## Predictive Maintenance
Support maintenance recommendation before failure conditions.

## Multi-Site Monitoring
Support centralized monitoring across distributed sites.

## Topology Visualization
Support advanced topology mapping and dependency visualization.

## Digital Twin
Support digital twin representation of network assets and health simulations.
