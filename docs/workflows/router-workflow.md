# Purpose

This document defines the authoritative workflow for Router Management covering Router asset lifecycle governance and topology hierarchy.

Router Management owns network routing asset records and their operational assignment contract.

It does not own monitoring health-state calculation, ONU provisioning execution, or repair workflows.

---

# Scope

Integrates with:

- Provisioning Workflow
- Network Monitoring Workflow
- Timeline
- Activity Log
- Reporting

---

# Workflow Principles

## Asset Lifecycle vs Health State
Router lifecycle is administrative asset governance and is distinct from runtime monitoring health.

---

## Topology Hierarchy
Core routers may parent distribution routers for hierarchy and propagation.

---

## Controlled Activation
Only active Router assets may be used for new topology attachment and provisioning assignment.

---

## Dependency-Safe Retirement
Retirement requires active child topology references to be cleared first.

---

# Actor Matrix

| Actor | Responsible | Approver | Override | Read Only |
|---|---|---|---|---|
| Administrator | Yes | Yes | Yes | Yes |
| Supervisor | Limited, by policy | Yes | Limited, by policy | Yes |
| NOC | Yes | No | No | Yes |
| Field Technician | No | No | No | Yes |
| System | Yes, for audit propagation | No | No | Yes |

---

# Router Lifecycle

## Planned

### Description
Router asset record exists but is not yet approved for operational topology or provisioning use.

### Allowed Transitions
- Active
- Retired

### Business Rules
- Planned Routers must not receive new operational topology assignments.
- Reference identity, location, and vendor metadata may be prepared while planned.

### Generated Events
- RouterCreated

## Active

### Description
Router is operationally available for topology and provisioning attachment.

### Allowed Transitions
- Maintenance
- Retired

### Business Rules
- Active Routers may own downstream hierarchy relationships.
- Monitoring health remains a separate concern and does not mutate Router lifecycle state.

### Generated Events
- RouterActivated

## Maintenance

### Description
Router is temporarily removed from new operational assignment due to planned maintenance context.

### Allowed Transitions
- Active
- Retired

### Business Rules
- Maintenance Routers must not receive new provisioning assignments.
- Existing monitoring and audit history remain intact.

### Generated Events
- RouterMaintenanceStarted

## Retired

### Description
Router is permanently removed from operational use while remaining historically queryable.

### Allowed Transitions
- None

### Business Rules
- Retired Routers are terminal and not assignable.
- Retirement requires no active child Router dependencies.

### Generated Events
- RouterRetired

---

# Cross-Module Effects

## Provisioning Workflow
- New topology assignment must resolve to `active` Router assets only.
- Router operational metadata may be consumed by service enablement and connectivity checks.

## Network Monitoring Workflow
- Router health is derived from monitoring observations and must not replace the Router lifecycle contract.
- Core and distribution router topology should be represented for correlation and fault propagation.

---

# Audit Requirements

- Router lifecycle transitions must generate Timeline and Activity Log records.
- Router lifecycle transitions must record actor, prior state, new state, and timestamp.

---

# References

- docs/architecture/decisions.md
- docs/architecture/business-events.md
- docs/database/entities.md
- docs/database/erd.md
- docs/project/project-scope.md