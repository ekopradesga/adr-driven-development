# Purpose

This document defines the authoritative workflow for OLT Management covering `OLT` asset lifecycle governance.

OLT Management owns the administrative lifecycle and inventory contract for Optical Line Terminal records.

It does not own monitoring health state calculation, ONU provisioning execution, or repair workflows.

---

# Scope

Integrates with:

- Network Monitoring Workflow
- Provisioning Workflow
- Timeline
- Activity Log
- Reporting

---

# Workflow Principles

## Asset Lifecycle vs Health State
OLT lifecycle is administrative asset governance and is distinct from runtime monitoring health.

---

## Controlled Activation
Only active OLT assets may be used for new topology attachment and provisioning assignment.

---

## Dependency-Safe Retirement
Retirement requires active dependent topology or endpoint references to be cleared first.

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

# OLT Lifecycle

## Planned

### Description
OLT asset record exists but is not yet approved for operational topology or provisioning use.

### Allowed Transitions
- Active
- Retired

### Business Rules
- Planned OLTs must not receive new operational ONU provisioning assignments.
- Reference identity, location, and vendor metadata may be prepared while planned.

### Generated Events
- OltCreated

## Active

### Description
OLT is operationally available for topology, provisioning, and monitoring attachment.

### Allowed Transitions
- Maintenance
- Retired

### Business Rules
- Active OLTs may own ONU and ODF operational relationships.
- Monitoring health remains a separate concern and does not mutate OLT lifecycle state.

### Generated Events
- OltActivated

## Maintenance

### Description
OLT is temporarily removed from new operational assignment due to planned maintenance context.

### Allowed Transitions
- Active
- Retired

### Business Rules
- Maintenance OLTs must not receive new provisioning assignments.
- Existing monitoring and audit history remain intact.

### Generated Events
- OltMaintenanceStarted

## Retired

### Description
OLT is permanently removed from operational use while remaining historically queryable.

### Allowed Transitions
- None

### Business Rules
- Retired OLTs are terminal and not assignable.
- Retirement requires no active ONU or ODF dependencies.

### Generated Events
- OltRetired

---

# Cross-Module Effects

## Provisioning Workflow
- ONU allocation and provisioning must resolve to `active` OLT assets only.
- Maintenance and retired OLTs must be excluded from new provisioning assignment.

## Network Monitoring Workflow
- Health states such as healthy, warning, critical, unknown, and maintenance remain monitoring concerns and must not be persisted as OLT lifecycle values.

---

# Audit Requirements

- OLT lifecycle transitions must generate Timeline and Activity Log records.
- Lifecycle transitions must record actor, prior state, new state, and timestamp.

---

# References

- docs/architecture/decisions.md
- docs/architecture/business-events.md
- docs/database/entities.md
- docs/database/erd.md
- docs/project/project-scope.md
