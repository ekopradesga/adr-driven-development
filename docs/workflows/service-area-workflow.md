# Purpose

This document defines the authoritative workflow for Area and Assignment Management covering `Cluster`, `ServiceArea`, and employee-to-area assignment governance.

Area and Assignment Management owns territory structure and visibility context.

It does not own customers, subscriptions, or employees themselves.

---

# Scope

Integrates with:

- Customer Management
- Collector Workflow
- Ticket Workflow
- Identity & Access
- Timeline
- Activity Log
- Reporting
- GIS Map

---

# Workflow Principles

## Territory First
Operational assignments and visibility must resolve from active cluster and service area structures.

---

## Hierarchy Aware
Service areas may be organized in parent-child hierarchies such as Region -> Branch -> Area.

---

## Merge Preserves History
Service area consolidation must preserve historical references and record the merge destination.

---

## Visibility Follows Assignment
Users without global scope only see operational data for the service areas assigned to their linked employee profile.

---

# Actor Matrix

| Actor | Responsible | Approver | Override | Read Only |
|---|---|---|---|---|
| Administrator | Yes | Yes | Yes | Yes |
| Supervisor | Yes | Yes | Limited, by policy | Yes |
| Customer Service | Limited, by policy | No | No | Yes |
| Collector | No | No | No | Yes |
| System | Yes, for audit propagation and visibility resolution | No | No | Yes |

---

# Cluster Lifecycle

## Planned

### Description
Cluster exists as a prepared operational grouping but is not yet active for production assignment.

### Allowed Transitions
- Active
- Inactive

### Business Rules
- Planned clusters must not receive new service areas or customer assignments unless policy explicitly permits staged setup.
- Reference data may be prepared while the cluster remains non-operational.

### Generated Events
- Cluster Created

## Active

### Description
Cluster is active for workload planning, reporting, and territory assignment.

### Allowed Transitions
- Inactive

### Business Rules
- Active clusters may own active service areas.
- Customer assignment and service area creation must resolve to an active cluster.

### Generated Events
- Cluster Activated

## Inactive

### Description
Cluster is retired from new operational use while preserving historical references.

### Allowed Transitions
- Active

### Business Rules
- Inactive clusters must not receive new customers or service areas.
- Existing records remain queryable for reporting and audit purposes.

### Generated Events
- Cluster Inactivated

---

# Service Area Lifecycle

## Draft

### Description
Service area structure is defined but not yet approved for operational assignment.

### Entry Validation
- Parent cluster exists.
- Area name and code are unique within the cluster.

### Exit Validation
- Hierarchy and visibility boundaries are complete enough for activation.

### Allowed Transitions
- Active
- Archived

### Business Rules
- Draft service areas must not receive new customer assignments.
- Employee assignment is allowed only for staged preparation when policy permits.

### Generated Events
- Service Area Created

## Active

### Description
Service area is operational and available for assignment, visibility, and routing.

### Entry Validation
- Parent cluster is active.
- Hierarchy references are valid.

### Exit Validation
- Merge or archive preconditions are satisfied.

### Allowed Transitions
- Merged
- Archived

### Business Rules
- Active service areas may be assigned to customers and employees.
- At most one primary service area assignment may exist per employee.
- Parent-child hierarchies must remain acyclic.

### Generated Events
- Service Area Activated

## Merged

### Description
Source service area has been consolidated into another active destination service area.

### Entry Validation
- Destination service area exists and is active.
- Source and destination service areas are distinct.
- Customer reassignment plan is recorded.

### Exit Validation
- None.

### Allowed Transitions
- None.

### Business Rules
- Merge is terminal for the source service area.
- Historical references remain on the source record with `merged_into_service_area_id` and `merged_at`.
- Active customers and employee assignments must be reassigned before merge completion.

### Generated Events
- Service Area Merged

## Archived

### Description
Service area is retired without merge and remains historical only.

### Entry Validation
- No active customers remain assigned.
- No active employee assignments remain linked.

### Exit Validation
- None.

### Allowed Transitions
- None.

### Business Rules
- Archived service areas are terminal and not assignable.
- Archive must preserve hierarchy and assignment history for reporting.

### Generated Events
- Service Area Archived

---

# Assignment Rules

1. Employee-to-service-area assignment is managed through `employee_service_area`.
2. One employee may be assigned to multiple service areas.
3. Each employee may have at most one `is_primary = true` assignment.
4. Assigned service areas drive area-based visibility unless an active role grants global scope.
5. Removing the final service area assignment from an area-scoped employee must be treated as a controlled administrative action.

---

# Cross-Module Effects

## Customer Management
- Customer `cluster_id` and `service_area_id` must resolve to active reference data.
- Service area merge or archive operations must explicitly handle customer reassignment before completion.

## Collector Workflow
- Collector workload queries and route planning use assigned service area scope.

## Ticket Workflow
- Technician and supervisor assignment may use service area coverage context.

## Identity & Access
- Area-based visibility resolves through active employee-to-service-area assignment.

---

# Audit Requirements

- Cluster and service area lifecycle transitions must generate Timeline and Activity Log records.
- Employee assignment changes must record actor, timestamp, old value, and new value.
- Merge operations must preserve source, destination, and reassignment metadata.

---

# References

- `docs/architecture/decisions.md`
- `docs/architecture/business-events.md`
- `docs/database/entities.md`
- `docs/database/erd.md`
- `docs/project/project-scope.md`