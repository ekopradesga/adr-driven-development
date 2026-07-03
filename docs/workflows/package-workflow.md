# Purpose

This document defines the authoritative workflow for Service Package Management covering `Package` lifecycle governance.

Service Package Management owns commercial package definitions and lifecycle availability.

It does not own subscriptions, invoices, or billing periods.

---

# Scope

Integrates with:

- Subscription Lifecycle
- Billing Workflow
- Timeline
- Activity Log
- Reporting

---

# Workflow Principles

## Catalog Stability
Package changes must preserve historical financial and service records.

---

## Controlled Availability
Only active packages may be selected for new subscriptions or package changes.

---

## Lifecycle Integrity
Package lifecycle transitions are forward-controlled and validated against active dependencies.

---

# Actor Matrix

| Actor | Responsible | Approver | Override | Read Only |
|---|---|---|---|---|
| Administrator | Yes | Yes | Yes | Yes |
| Supervisor | Limited, by policy | Yes | Limited, by policy | Yes |
| Customer Service | No | No | No | Yes |
| Billing Staff | No | No | No | Yes |
| System | Yes, for audit propagation | No | No | Yes |

---

# Package Lifecycle

## Draft

### Description
Package exists as a prepared commercial profile and is not yet available for operational assignment.

### Allowed Transitions
- Active
- Retired

### Business Rules
- Draft packages must not be assigned to subscriptions.
- Pricing and speed profile fields may be edited freely while in draft.

### Generated Events
- PackageCreated

## Active

### Description
Package is available for subscription assignment and operational use.

### Allowed Transitions
- Deprecated
- Retired

### Business Rules
- Active packages may be assigned to new subscriptions.
- Existing subscriptions continue to reference package snapshots through billing linkage.

### Generated Events
- PackageActivated

## Deprecated

### Description
Package remains valid for historical and existing subscription references but is closed for new assignment.

### Allowed Transitions
- Active
- Retired

### Business Rules
- Deprecated packages must not be assigned to new subscriptions.
- Existing subscriptions may continue temporarily until migration plans are executed.

### Generated Events
- PackageDeprecated

## Retired

### Description
Package is permanently removed from operational assignment while remaining historically queryable.

### Allowed Transitions
- None

### Business Rules
- Retired packages are terminal and not assignable.
- Retirement requires zero active subscriptions referencing the package.

### Generated Events
- PackageRetired

---

# Cross-Module Effects

## Subscription Lifecycle
- New subscription creation and package changes must resolve to `active` packages only.
- Retirement is blocked if active subscriptions still reference the package.

## Billing Workflow
- Invoice item snapshots remain immutable; later package changes do not alter historical invoices.

---

# Audit Requirements

- Package lifecycle transitions must generate Timeline and Activity Log records.
- Lifecycle transitions must record actor, prior state, new state, and timestamp.

---

# References

- docs/architecture/decisions.md
- docs/architecture/business-events.md
- docs/database/entities.md
- docs/database/erd.md
- docs/project/project-scope.md
