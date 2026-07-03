# Purpose

This document defines the authoritative workflow for FAT Management covering `FAT` asset lifecycle governance and downstream reachability evaluation.

FAT Management owns Fiber Access Terminal inventory records and their operational assignment contract.

It does not own ONU provisioning, OLT lifecycle, or generic monitoring health-state taxonomy.

---

# Scope

Integrates with:

- OLT Management
- Network Monitoring Workflow
- Provisioning Workflow
- Service Area Management
- Timeline
- Activity Log
- Reporting

---

# Workflow Principles

## Asset Lifecycle vs Reachability
FAT lifecycle is administrative asset governance and is distinct from derived downstream reachability status.

---

## Downstream Scope Resolution
FAT operational reachability is derived from the set of downstream ONU endpoints mapped to that FAT.

---

## Parent Topology Integrity
A FAT must belong to one ODF and may optionally link to one Service Area for field and operational scoping.

---

# Actor Matrix

| Actor | Responsible | Approver | Override | Read Only |
|---|---|---|---|---|
| Administrator | Yes | Yes | Yes | Yes |
| Supervisor | Limited, by policy | Yes | Limited, by policy | Yes |
| NOC | Yes | No | No | Yes |
| Field Technician | Limited, by policy | No | No | Yes |
| System | Yes, for audit propagation and health summarization | No | No | Yes |

---

# FAT Lifecycle

## Planned

### Description
FAT asset record exists but is not yet available for operational assignment.

### Allowed Transitions
- Active
- Retired

### Business Rules
- Planned FATs must not receive new downstream ONU assignment.
- Location, capacity, and parent ODF linkage may be prepared while planned.

### Generated Events
- FatCreated

## Active

### Description
FAT is available for operational topology use and downstream assignment.

### Allowed Transitions
- Maintenance
- Retired

### Business Rules
- Active FATs may be referenced by downstream ONU distribution scope.
- Capacity saturation is a derived operational condition, not a lifecycle state.

### Generated Events
- FatActivated

## Maintenance

### Description
FAT is temporarily unavailable for new operational assignment due to maintenance context.

### Allowed Transitions
- Active
- Retired

### Business Rules
- Maintenance FATs must not receive new downstream ONU assignment.
- Monitoring may continue, but maintenance context suppresses operational reassignment.

### Generated Events
- FatMaintenanceStarted

## Retired

### Description
FAT is permanently removed from operational use while remaining historically queryable.

### Allowed Transitions
- None

### Business Rules
- Retired FATs are terminal and not assignable.
- Retirement requires no active ONT, Dropcore, or mapped operational ONU dependencies.

### Generated Events
- FatRetired

---

# Downstream Reachability Evaluation

1. Resolve the downstream ONU scope using the FAT-to-ONU distribution mapping.
2. Ping all mapped ONU endpoints according to Monitoring policy.
3. If no ONU is mapped, FAT reachability is `Unknown`.
4. If all mapped ONUs are reachable, FAT reachability is `Healthy`.
5. If some mapped ONUs are unreachable while others remain reachable, FAT reachability is `Warning`.
6. If all mapped ONUs are unreachable while the parent OLT remains reachable, FAT reachability is `Critical` and indicates a probable FAT-side issue.
7. If the parent OLT is unreachable, FAT reachability must become `Unknown` rather than independently `Critical`.

Reachability outcomes are monitoring facts and must not mutate the FAT lifecycle column.

---

# Cross-Module Effects

## Provisioning Workflow
- New downstream endpoint assignment must resolve to `active` FAT assets only.
- Provisioning or migration updates the FAT-to-ONU mapping used for downstream reachability evaluation.

## Network Monitoring Workflow
- FAT health is derived from downstream ONU ping results and parent OLT reachability context.
- Correlation rules may raise probable FAT issues when multiple downstream ONUs fail under one reachable parent path.

## Service Area Management
- FAT may optionally link to one Service Area for field routing, planning, and reporting.

---

# Audit Requirements

- FAT lifecycle transitions must generate Timeline and Activity Log records.
- FAT lifecycle transitions must record actor, prior state, new state, and timestamp.
- Downstream scope changes must preserve old mapping, new mapping, actor, and timestamp.

---

# References

- docs/architecture/decisions.md
- docs/architecture/business-events.md
- docs/database/entities.md
- docs/database/erd.md
- docs/project/project-scope.md
