# ISP Management System — Project Scope

**Version:** 1.0

**Status:** Living Document

**Owner:** Architecture Team

**Last Review:** 2026-06-27

---

# Purpose

This document defines the official business boundaries of the ISP Management System.

It is the authoritative source for determining whether a feature, module, or business capability belongs to the project.

This document is not a software specification.

This document is not an implementation guide.

This document does not define technical architecture, database schemas, APIs, or source code.

When a question arises about whether a capability is in scope, this document provides the answer. When this document does not explicitly address a capability, the Architecture Team must make a formal scope decision before work begins.

---

# Vision

The ISP Management System is an integrated platform designed to manage the complete operational lifecycle of an Internet Service Provider from initial customer acquisition through service termination.

The platform consolidates all core ISP business operations into a single coherent system, replacing fragmented tools, manual processes, and disconnected workflows with a unified, event-driven, and configurable operational environment.

The long-term vision is a platform that:

- Serves as the single source of truth for all ISP operational data.
- Enables end-to-end automation of repeatable business processes.
- Provides real-time operational visibility across all business domains.
- Delivers a consistent experience for field staff, office staff, and customers.
- Supports intelligent decision-making through structured data, event history, and future AI-assisted capabilities.
- Scales from a single-branch ISP to a multi-area, high-volume operation.
- Remains adaptable to evolving business requirements through configuration rather than code changes.

---

# Objectives

## Centralized ISP Operations

Consolidate customer management, billing, payments, collections, provisioning, network monitoring, and support into a single integrated platform.

Eliminate data silos that arise from operating disconnected tools for each business domain.

## Standardized Business Workflows

Define, enforce, and automate the business workflows that govern how the ISP operates from lead generation through service termination.

Workflows must be consistent, auditable, and role-aware.

## Automation

Reduce manual intervention in repeatable, policy-driven processes such as billing cycle execution, invoice publication, overdue detection, suspension eligibility, and notification delivery.

Automation must be policy-controlled and override-capable by authorized actors.

## Operational Visibility

Provide real-time dashboards and reporting across all operational domains.

Supervisors, managers, and administrators must have immediate access to the current state of operations without manual data collection.

## Auditability

Every significant business action must produce a traceable record.

The platform must support full audit trails through the Timeline and Activity Log systems, enabling review of who did what, when, and why.

## Scalability

The platform must support business growth in customer volume, service areas, and operational complexity without requiring architectural changes.

## Maintainability

The platform must be built to evolve. New business rules, workflow changes, and module additions should be achievable without breaking existing functionality.

## AI-Ready Architecture

The architecture must be designed to support AI-assisted capabilities in future releases.

Event-driven design, structured business data, and immutable audit history lay the foundation for AI features such as fault prediction, ticket classification, and smart delivery scheduling.

## Event-Driven Design

Business workflows communicate through well-defined business events.

Event-driven design enables loose coupling between modules, supports reliable asynchronous processing, and provides a foundation for future integrations and automation.

---

# Target Users

## Administrator

Responsible for platform governance, exception handling, manual overrides, and policy configuration. Has the highest level of access across all modules.

## Customer Service

Responsible for customer communication, ticket management, service coordination, and operational exception handling. Primary day-to-day operational role.

## Sales

Responsible for lead management, prospect qualification, survey scheduling, and pre-installation workflow. Handles the early stages of the customer lifecycle.

## Finance

Responsible for billing governance, payment oversight, financial reconciliation, and reporting. Has read access to all financial records and approval authority over controlled financial exceptions.

## Billing Staff

Responsible for billing cycle execution, invoice management, and overdue tracking. Operates within the Billing Workflow and coordinates with Finance and Collectors.

## Collector

Responsible for field payment collection, outstanding invoice follow-up, and collection visit management. Primarily operates through mobile interfaces with offline capability.

## NOC

Network Operations Center staff responsible for monitoring network infrastructure, responding to device health events, and coordinating with Technicians on network incidents.

## Field Technician

Responsible for physical installation, service provisioning support, maintenance, and field resolution of support tickets. Primarily operates through mobile interfaces.

## Supervisor

Responsible for team oversight, assignment management, SLA monitoring, escalation handling, and workload balancing across Collectors and Technicians.

## Management

High-level oversight of operational performance, financial health, and business KPIs through dashboards and reporting. Generally read-only with approval authority over policy-level decisions.

## Customer (Portal)

The end customer of ISP services. Interacts with the platform through a dedicated Customer Portal to view service status, invoices, payment history, and support tickets.

---

# Core Modules — Version 1

The following modules are officially included in Version 1 of the ISP Management System.

## Authentication and Authorization

Manages user identity, authentication, session lifecycle, and role-based access control.

Supports user impersonation for authorized support scenarios.

## User Management

Manages internal user accounts, role assignments, area-based visibility, and account lifecycle.

## Customer Management

Manages the complete customer record including identity, contact profile, service location, geographic assignment, and lifecycle status.

Supports the Customer 360 workspace pattern providing a unified view of all customer-related operational context.

## Subscription Management

Manages the service contract lifecycle from prospect through activation, suspension, reactivation, and termination.

Governs the relationship between a customer and their service package and enforces the one-active-primary-subscription rule.

## Service Package Management

Manages commercial internet service plans including pricing profiles, speed configurations, and availability status.

Supports package lifecycle from draft through active, deprecation, and retirement.

## OLT Management

Manages Optical Line Terminal records as the core access network head-end entities in the physical and logical topology.

## ODF Management

Manages Optical Distribution Frame records as physical fiber distribution points in the network topology.

## FAT Management

Manages Fiber Access Terminal records as distribution terminals connecting the feeder network to the last-mile drop.

## Dropcore Management

Manages Dropcore records representing physical last-mile drop segments from FAT to customer endpoint.

## ONT Management

Manages Optical Network Terminal records as the customer-side optical endpoint for service delivery.

## Area and Assignment Management

Manages geographic service areas, operational clusters, and employee-to-area assignment for workload scoping and visibility control.

Supports hierarchical area structures such as Region, Branch, and Area.

## GIS Map

Provides geographic visualization of customers, network infrastructure, service areas, and operational assets.

Supports location-aware operational planning and field navigation.

## Billing

Manages the complete billing lifecycle from eligible subscription identification through invoice generation, publication, overdue tracking, and collection handoff.

Enforces immutable financial snapshots, one-invoice-per-period rules, and configurable billing calendar settings.

## Payment

Manages the payment lifecycle from payment intent creation through validation, recording, allocation, and completion.

Supports cash, bank transfer, QRIS, and virtual account payment channels. Enforces immutable payment records and auditable allocation history.

## Collector

Manages the field collection workflow for outstanding invoice follow-up including task assignment, visit scheduling, route execution, and payment submission.

Supports mobile-first and offline-capable field operation.

## Provisioning

Manages the technical service activation lifecycle from resource allocation through external system integration, monitoring registration, and service activation handoff.

Coordinates with OLT, router, and authentication systems in a vendor-neutral manner.

## Network Monitoring

Manages the observation and health classification of network infrastructure including Core Router, Distribution Router, OLT, ODF, FAT, Dropcore, and ONT.

Supports hierarchical topology, parent-child fault propagation, correlation rules, configurable health thresholds, and maintenance window management.

## Ticket Management

Manages the complete operational ticket lifecycle from issue creation through triage, assignment, field execution, resolution, verification, and closure.

Supports incident, problem, service request, complaint, installation, maintenance, and other configurable ticket categories with SLA tracking.

## Notification Engine

Manages event-driven notification delivery across WhatsApp, Email, SMS, Push Notification, and Portal Inbox channels.

Supports configurable notification rules, recipient resolution, template rendering, scheduling, retry, and fallback channel policies.

## Timeline

Provides business-readable chronological event records attached to core entities.

Every significant business state transition generates a Timeline entry accessible to authorized users on the relevant entity workspace.

## Activity Log

Provides a detailed operational accountability ledger capturing actor identity, action, entity reference, and before-and-after state for every significant platform action.

Supports security review, compliance audit, and forensic investigation.

## Attachment System

Manages file attachments across business entities including customers, invoices, payments, tickets, network devices, and subscriptions.

Supports photos, documents, GPS coordinates, speed test results, signal measurements, and configuration backups. Attachment access inherits entity-level permissions.

## Settings Engine

Provides centralized configuration management for business policies, workflow rules, SLA targets, notification rules, billing calendar settings, and operational thresholds.

Supports configuration-over-hardcoding for all policy-driven business behavior.

## Dashboard

Provides role-appropriate operational dashboards with real-time metrics and summary views for each major business domain.

Dashboards are data-driven and support the operational oversight needs of each user role.

## Reporting

Provides business reporting across all operational domains including billing, payments, collections, provisioning, monitoring, tickets, and notifications.

Reports must be consistent with immutable business history and auditable records.

## Customer Portal

Provides customers with a self-service interface to view service status, invoices, payment history, ticket status, and notifications.

Supports ticket creation, payment initiation, and resolution confirmation.

## Global Search

Provides a unified search interface allowing operational users to locate any business entity by name, identifier, phone number, address, or other configured attributes.

Serves as the primary navigation mechanism for experienced operational users.

---

# Shared Components

The following components are reusable across all modules and are not owned by any single business domain.

## Universal Workspace

A standardized tabbed profile layout for core business entities. Consolidates related operational context including billing, monitoring, timeline, activity log, attachments, tickets, and notes in a single workspace.

Defined by the Entity 360 Workspace Pattern in the architecture decisions.

## Metadata Driven CRUD

A configurable, schema-aware data management system that generates list, form, and detail views from field definitions.

Reduces duplication and enables consistent data entry and display across all entity types.

## Timeline

A shared event display and recording system providing business-readable, severity-classified event entries attached to any supported entity.

Consumed by the Universal Workspace and visible to authorized actors on each entity.

## Activity Log

A shared accountability ledger recording actor, action, target entity, before-and-after snapshot, and network context for every significant platform action.

Used for security, compliance, and operational audit across all modules.

## Attachment System

A shared file attachment service managing metadata, access control, versioning, and retention for files associated with any business entity.

## Notification Engine

A shared event-driven notification service that delivers business notifications through configurable multi-channel delivery pipelines without requiring source workflows to know the delivery mechanism.

## Search Engine

A shared search service providing entity discovery, cross-domain lookup, and global search navigation for all operational users.

## Settings Engine

A shared configuration service providing centralized, policy-driven configuration management for all modules.

Enables business behavior to be adjusted through configuration without implementation changes.

---

# Architecture Principles

The architecture principles governing this platform are formally documented in:

[docs/architecture/decisions.md](../architecture/decisions.md)

That document is the authoritative source for all architectural decisions. Key principles include:

- Simplicity over Complexity
- Business Rules First
- Architecture Decisions as Source of Truth
- AI-Assisted Development with documentation as knowledge base
- Documentation Hierarchy
- Mobile-First Experience
- Reusable Components
- Entity 360 Workspace
- Global Search as Primary Navigation
- Asynchronous External Integrations
- Configuration over Hardcoding
- Consistent Business Terminology
- Security by Default

This document does not duplicate architectural decision content. Refer to decisions.md for full definitions.

---

# Business Coverage

The platform covers the complete ISP operational lifecycle.

Lead

↓

Survey

↓

Installation

↓

Provisioning

↓

Activation

↓

Billing

↓

Payment

↓

Monitoring

↓

Support

↓

Suspension

↓

Reactivation

↓

Termination

Each stage of this lifecycle is governed by a documented business workflow. The workflows collectively define the authoritative operational behavior of the platform.

| Lifecycle Stage | Governing Workflow |
|---|---|
| Lead, Survey, Installation | Subscription Lifecycle |
| Provisioning, Activation | Provisioning Workflow |
| Billing, Invoice Management | Billing Workflow |
| Payment, Allocation | Payment Workflow |
| Field Collection | Collector Workflow |
| Network Observation | Network Monitoring Workflow |
| Issue Resolution | Ticket Workflow |
| Notifications | Notification Workflow |
| Suspension, Reactivation | Subscription Lifecycle, Billing Workflow |
| Termination | Subscription Lifecycle, Provisioning Workflow |

---

# Out of Scope — Version 1

The following capabilities are intentionally excluded from Version 1.

Excluded features may be added in future releases. Exclusion from Version 1 does not imply permanent exclusion from the platform.

## Accounting

Full double-entry accounting, journal entries, general ledger, accounts receivable reconciliation, and financial statement generation are out of scope. The platform tracks billing and payment records but does not provide a complete accounting system.

## Payroll

Employee compensation management, payroll calculations, and salary disbursement are out of scope.

## HR Management

Human resources functions including recruitment, performance management, leave management, and employee records beyond what is required for operational assignment are out of scope.

## Advanced Warehouse Management

Full warehouse management including bin locations, stock movements, and warehouse operations beyond basic inventory awareness are out of scope.

## Advanced Inventory Management

Detailed inventory tracking, stock level management, purchase orders, and supplier management are out of scope. Field inventory references in the Ticket Workflow are reserved for future integration.

## CRM Marketing

Marketing campaign management, mass email or SMS marketing, customer segmentation for promotional purposes, and marketing analytics are out of scope.

## Point of Sale

In-person sales terminal functionality for retail or walk-in payment processing beyond supported payment channels is out of scope.

## E-Commerce

Online product catalog, shopping cart, and e-commerce transaction management are out of scope.

## Multi-Company

Managing multiple legally separate companies with isolated financial books and separate operational boundaries in a single platform instance is out of scope for Version 1.

## Franchise Management

Franchise relationship management, franchise billing models, and multi-franchisee operational governance are out of scope.

## AI Decision Making

Automated decision-making driven by machine learning models that make or execute business decisions without human oversight is out of scope. AI-assisted recommendations are reserved for future extensions.

## Machine Learning Automation

Self-learning models that autonomously adjust business rules, thresholds, or configurations without explicit human authorization are out of scope.

---

# Future Expansion

The following capabilities are planned for future releases and are architecturally reserved.

## Inventory Management

Structured tracking of field equipment, materials, and spare parts linked to work orders and provisioning workflows.

## Warehouse Management

Receiving, dispatch, and location-based inventory management for equipment and materials.

## Accounting Integration

Synchronization with external or internal accounting systems for automated financial posting and reconciliation.

## Route Optimization

Intelligent routing for field collectors and technicians based on area, workload, and geographic proximity.

## AI Fault Diagnosis

AI-assisted analysis of monitoring events to suggest probable root causes and recommend corrective actions.

## AI Ticket Classification

Automated ticket categorization and priority assignment based on ticket content and historical patterns.

## Capacity Planning

Network capacity trend analysis and threshold forecasting to support infrastructure planning decisions.

## GIS Analytics

Geographic analysis overlays for service coverage, outage heat maps, and network density visualization.

## Predictive Maintenance

Proactive maintenance recommendation based on predictive signals from monitoring history and device health trends.

## Multi-ISP Support

Support for operating multiple distinct ISP entities within a single platform instance with isolated operational boundaries.

## Multi-Company

Support for multiple legally separate companies sharing platform infrastructure with appropriate data isolation.

## External Event Bus

Integration with external event bus infrastructure for cross-system event publishing and subscription.

## Mobile Applications

Dedicated native mobile applications for Collector and Technician workflows beyond the mobile-responsive web interface.

## Public API

A documented public API for authorized third-party integrations and customer-facing API access.

---

# Non-Functional Goals

## High Performance

The platform must remain responsive under expected operational load. Critical workflows such as billing execution, payment processing, and notification delivery must meet defined performance expectations.

## Scalability

The architecture must support growth in customer volume, active subscriptions, monitoring targets, and concurrent users without requiring structural redesign.

## Security

The platform must apply least-privilege access control, role-based authorization, area-based data visibility, and secure session management from the outset. Security is a baseline architectural constraint, not a post-launch enhancement.

## Reliability

Core business workflows must be resilient to transient failures. Retry behavior, idempotent processing, and graceful degradation must be designed into the system.

## Auditability

Every significant business action must produce a traceable, immutable record through the Timeline and Activity Log systems. Financial records must be logically immutable once confirmed.

## Configurability

Business behavior must be adjustable through the Settings Engine without implementation changes. Policy-driven configuration is a primary architectural principle.

## Extensibility

The platform must support new modules, workflow extensions, and third-party integrations without breaking existing functionality. Shared components must have clear, stable contracts.

## Observability

The platform must expose sufficient operational data through dashboards, reporting, and system health indicators to allow operators and administrators to understand current platform state and identify issues quickly.

## Maintainability

The codebase and documentation must be kept aligned. Architecture decisions are the authoritative source of truth. Technical debt must be managed intentionally and not allowed to accumulate silently.

---

# Success Criteria

The following measurable outcomes define project success for Version 1.

## Standardized Workflows

All core ISP business workflows — from lead acquisition to service termination — are formally documented, implemented, and enforced by the platform.

## Centralized Operational Data

All operational data including customer records, subscriptions, invoices, payments, monitoring events, and support tickets is managed in a single platform with no authoritative data held externally.

## Reduced Manual Processes

Repeatable, policy-driven processes such as billing generation, overdue detection, suspension evaluation, notification delivery, and SLA escalation are automated and require human intervention only for exceptions.

## Full Audit Trail

Every significant business action is traceable through the Timeline and Activity Log systems. Financial records, assignment history, and state transitions are immutable and fully auditable.

## Event-Driven Architecture

All inter-workflow communication is through formally defined business events. Modules are loosely coupled and can evolve independently. The business events catalog reflects the actual events used in production.

## Consistent User Experience

All modules share a consistent workspace pattern, navigation model, and interaction design. Users can operate across modules without relearning interface conventions.

## Future-Ready Architecture

The platform is designed to accommodate planned future extensions including AI-assisted features, inventory management, accounting integration, and external event bus without requiring architectural redesign.

---

# Related Documents

| Document | Description |
|---|---|
| [decisions.md](../architecture/decisions.md) | Authoritative architecture decisions and principles |
| [glossary.md](../architecture/glossary.md) | Canonical business and technical terminology |
| [business-events.md](../architecture/business-events.md) | Authoritative catalog of all platform business events |
| [entities.md](../database/entities.md) | Complete business entity inventory |
| [erd.md](../database/erd.md) | Conceptual entity relationship diagram |
| [subscription-lifecycle.md](../workflows/subscription-lifecycle.md) | Subscription management workflow |
| [provisioning-workflow.md](../workflows/provisioning-workflow.md) | Service provisioning workflow |
| [billing-workflow.md](../workflows/billing-workflow.md) | Invoice billing lifecycle workflow |
| [payment-workflow.md](../workflows/payment-workflow.md) | Payment processing workflow |
| [collector-workflow.md](../workflows/collector-workflow.md) | Field collection workflow |
| [network-monitoring-workflow.md](../workflows/network-monitoring-workflow.md) | Network monitoring workflow |
| [ticket-workflow.md](../workflows/ticket-workflow.md) | Ticket management workflow |
| [notification-workflow.md](../workflows/notification-workflow.md) | Notification delivery workflow |
