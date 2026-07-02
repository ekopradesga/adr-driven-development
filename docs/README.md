# ISP Management System

Enterprise-grade ISP Management Platform designed using an Architecture-First approach.

The project documentation is the authoritative source for architecture, business rules, workflows, and implementation guidance.

---

# Architecture Status

| Field | Value |
|---|---|
| **Architecture Freeze** | v1.0 |
| **Effective Date** | 2026-07-02 |
| **Current Phase** | Architecture v1.1 Improvements |
| **Next Milestone** | Sprint 2 – Customer Module |

Architecture v1.0 is frozen. All platform standards, database foundations, and architecture decisions established through Sprint 1.4 are the stable baseline for all business module development.

Future architecture changes require a formal Architecture Decision recorded in `docs/architecture/decisions.md` before implementation. See `docs/architecture/architecture-freeze-v1.md` for the full governance policy.

---

# Documentation Structure

## Architecture

Contains architecture principles, business decisions, terminology, and business events.

```
docs/architecture/
```

Start here:

* decisions.md
* glossary.md
* business-events.md
* architecture-backlog.md

---

## Business Workflows

Defines complete business processes and lifecycle management.

```
docs/workflows/
```

Includes:

* Subscription Lifecycle
* Provisioning
* Billing
* Payment
* Collector
* Network Monitoring
* Ticket
* Notification

---

## Database

Contains the conceptual entity model and ERD.

```
docs/database/
```

---

## Project

Project scope and planning documents.

```
docs/project/
```

---

## Development

Development standards and process documents.

```
docs/development/
```

Includes:

* Definition of Done

---

# Documentation Reading Order

New contributors should review documentation in the following order:

1. decisions.md
2. glossary.md
3. business-events.md
4. workflow-standard.md
5. Business workflows
6. ERD / Entities
7. Project Scope

---

# Architecture Philosophy

This project follows several core principles:

* Architecture First
* Business Driven Design
* Event Driven Communication
* Metadata Driven CRUD
* Universal Workspace
* Configuration over Hardcoding
* Shared Components
* Single Source of Truth

Detailed explanations are documented in:

```
docs/architecture/decisions.md
```

---

# AI Collaboration

GitHub Copilot and AI assistants must treat the documentation as the authoritative knowledge base.

Before generating code, database changes, APIs, or workflows, always review the relevant documentation.

Implementation must follow documented architecture decisions.

If implementation conflicts with documentation, update the documentation first or propose an architecture decision before changing behavior.

---

# Project Status

**Current Phase:** Pre-Release — Architecture v1.1 Improvements

Architecture Freeze v1.0 is declared. Business module implementation begins with Sprint 2.

**Database Schema Status:** Pre-release. The schema is still being finalized as business module architectures are reviewed and resolved (see Architecture Backlog). Migration files for modules not yet in production may be replaced rather than incrementally altered. Migration history is intentionally kept clean until the first production release.

See the Migration Strategy Before First Release decision in `docs/architecture/decisions.md` for the governing policy.

---

# License

Private Project.
