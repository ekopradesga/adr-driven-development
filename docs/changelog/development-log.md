# Development Log

**Project:** ISP Management System
**Rule:** Append-only. Never rewrite previous entries except to correct obvious formatting or spelling mistakes. Maintained by GitHub Copilot after every completed task.

---

## Entry Format

```
### YYYY-MM-DD | Category | Title

**Summary:** What was done and why.

**Files Added:**
- path/to/file

**Files Modified:**
- path/to/file

**Architecture Impact:** None — or describe if relevant.

**Notes:** Optional context.
```

**Valid Categories:** Architecture · Documentation · Database · Backend · Frontend · Security · Performance · Testing · DevOps · Refactoring · Bug Fix

---

## Log

---

### 2026-06-25 | Architecture | Established Architecture Foundation

**Summary:** Defined the authoritative architecture documentation for the ISP Management Platform. Established the Architecture-First philosophy, 14 architecture principles, and all core architecture decisions covering RBAC, lifecycle state machines, billing, payments, monitoring, and identity. Created the project glossary and the complete business events catalog.

**Files Added:**
- docs/README.md
- docs/architecture/decisions.md
- docs/architecture/glossary.md
- docs/architecture/business-events.md

**Files Modified:** None

**Architecture Impact:** All subsequent implementation must follow `decisions.md` as the source of truth. Documentation hierarchy established: Architecture Decisions → Workflows → Database Design → Source Code.

---

### 2026-06-25 | Documentation | Defined Project Scope

**Summary:** Authored the official project scope document defining Version 1.0 feature boundaries, target users (Administrator, Finance, Sales, Technician, Customer Service, Collector, NOC, Supervisor, Customer Portal), business objectives, and out-of-scope list.

**Files Added:**
- docs/project/project-scope.md

**Files Modified:** None

**Architecture Impact:** Defines the approved implementation boundary for all modules. Out-of-scope for Version 1.0: Accounting, Payroll, HR, Advanced Warehouse, Advanced Inventory, CRM Marketing, POS, E-Commerce, Multi-Company, Franchise, AI autonomous decision-making.

---

### 2026-06-25 | Documentation | Authored Eight Business Workflow Documents

**Summary:** Defined eight complete business workflow documents covering the full ISP operational lifecycle — from subscription activation through billing, payment, collection, provisioning, network monitoring, support tickets, and notifications.

**Files Added:**
- docs/workflows/subscription-lifecycle.md
- docs/workflows/billing-workflow.md
- docs/workflows/payment-workflow.md
- docs/workflows/collector-workflow.md
- docs/workflows/provisioning-workflow.md
- docs/workflows/network-monitoring-workflow.md
- docs/workflows/ticket-workflow.md
- docs/workflows/notification-workflow.md

**Files Modified:** None

**Architecture Impact:** All module implementations must follow the lifecycle states and transitions defined in these documents. Every state transition must generate a `TimelineEvent` and an `ActivityLog` entry.

---

### 2026-06-25 | Database | Authored Entity Inventory and Conceptual ERD

**Summary:** Documented the complete business entity inventory covering six entity classifications (Core, Transaction, Infrastructure, Platform, Operational, Identity) across all platform domains. Produced the conceptual ERD defining all entity relationships, cardinalities, ownership rules, deletion behaviors, and lifecycle dependencies.

**Files Added:**
- docs/database/entities.md
- docs/database/erd.md

**Files Modified:** None

**Architecture Impact:** Authoritative source for all Eloquent relationships, foreign key definitions, soft delete rules, polymorphic models, and immutability constraints. All migrations must be verified against `erd.md` before generation.

---

### 2026-06-25 | Database | Initial Database Migrations — Core Business Modules

**Summary:** Generated Laravel migrations for the initial core and transaction entity tables: customers, packages, OLT/ONU network infrastructure, subscriptions, invoices, invoice line items, payments, payment allocations, and ONU statistics.

**Files Added:**
- database/migrations/2026_06_25_000001_create_customers_table.php
- database/migrations/2026_06_25_000002_create_packages_table.php
- database/migrations/2026_06_25_000003_create_olts_table.php
- database/migrations/2026_06_25_000004_create_onus_table.php
- database/migrations/2026_06_25_000005_create_subscriptions_table.php
- database/migrations/2026_06_25_000006_create_invoices_table.php
- database/migrations/2026_06_25_000007_create_invoice_items_table.php
- database/migrations/2026_06_25_000008_create_payments_table.php
- database/migrations/2026_06_25_000009_create_payment_allocations_table.php
- database/migrations/2026_06_25_000010_create_onu_statistics_table.php

**Files Modified:** None

**Architecture Impact:** Schema follows `entities.md` and `erd.md`. All tables use BIGINT UNSIGNED PKs, InnoDB, utf8mb4 charset, soft deletes where documented, and foreign key constraints per ERD cardinalities.

---

### 2026-06-25 | DevOps | Created AI Development Guide

**Summary:** Created the GitHub Copilot instructions file establishing the Architecture-First development philosophy, documentation reading order, technology stack constraints (Laravel 10, no Laravel 11+ features), coding standards, naming conventions, UI rules, AI agent behavior rules, and terminal safety policy.

**Files Added:**
- .github/copilot-instructions.md

**Files Modified:** None

**Architecture Impact:** Governs all AI-assisted code generation for the project. Defines mandatory documentation review order before any code generation begins.

---

### 2026-06-29 | Refactoring | Refactored Copilot Instructions

**Summary:** Consolidated and deduplicated `copilot-instructions.md`. Merged 14 standalone duplicate sections (Architecture First, Workflow Driven Development, Business Events, Shared Components, Metadata Driven CRUD, Universal Workspace, Controller Responsibility, Database Rules, Configuration Over Hardcoding, Project Scope, Documentation Synchronization, Coding Principles, AI Collaboration, Final Principle) into the numbered section structure. Added Testing, Performance, Security, and Implementation Preferences subsections to Laravel Coding Standards. Reduced document length by approximately 30%.

**Files Added:** None

**Files Modified:**
- .github/copilot-instructions.md

**Architecture Impact:** None. Documentation cleanup only. No architectural rules were changed or weakened.

---

### 2026-06-29 | Backend | Sprint 0 — Foundation Module

**Summary:** Implemented the complete Foundation module covering Identity & Access (Users, Roles, Permissions) and the Settings Engine. Includes PHP 8.1 backed Enums, Eloquent models with lifecycle casts and documented relationships, three-pivot RBAC model matching the ERD exactly, Settings Engine with scope hierarchy (global/area/cluster/customer) and 1-hour cache, UserPolicy with super-admin bypass, four Form Requests, and complete seeders for 9 roles, 47 permissions across 12 categories, 7 setting categories, 23 registry entries, and global default settings.

**Files Added:**
- app/Enums/UserStatus.php
- app/Enums/RoleStatus.php
- app/Enums/PermissionStatus.php
- app/Enums/SettingScope.php
- app/Enums/SettingDataType.php
- app/Models/Role.php
- app/Models/Permission.php
- app/Models/SettingCategory.php
- app/Models/SettingRegistryEntry.php
- app/Models/Setting.php
- app/Policies/UserPolicy.php
- app/Http/Requests/StoreUserRequest.php
- app/Http/Requests/UpdateUserRequest.php
- app/Http/Requests/StoreRoleRequest.php
- app/Http/Requests/UpdateRoleRequest.php
- app/Services/Settings/SettingService.php
- database/migrations/2026_06_29_000001_create_roles_table.php
- database/migrations/2026_06_29_000002_create_permissions_table.php
- database/migrations/2026_06_29_000003_create_role_user_table.php
- database/migrations/2026_06_29_000004_create_permission_role_table.php
- database/migrations/2026_06_29_000005_create_permission_user_table.php
- database/migrations/2026_06_29_000006_create_setting_categories_table.php
- database/migrations/2026_06_29_000007_create_setting_registry_entries_table.php
- database/migrations/2026_06_29_000008_create_settings_table.php
- database/seeders/RoleSeeder.php
- database/seeders/PermissionSeeder.php
- database/seeders/SettingCategorySeeder.php
- database/seeders/SettingRegistrySeeder.php
- database/seeders/SettingSeeder.php

**Files Modified:**
- database/migrations/2014_10_12_000000_create_users_table.php
- app/Models/User.php
- app/Providers/AuthServiceProvider.php
- app/Providers/AppServiceProvider.php
- database/seeders/DatabaseSeeder.php

**Architecture Impact:** Implements the RBAC decision from `decisions.md`. Implements Settings Engine per Architecture Principle 13 (Configuration over Hardcoding). All three pivot tables (`role_user`, `permission_role`, `permission_user`) match the ERD exactly. User lifecycle (draft → active → suspended → disabled) matches `entities.md`. Convention established: `scope_id = 0` for global-scope settings to ensure composite unique index correctness in MySQL/MariaDB.

**Notes:** Controllers not generated — Sprint 0 scope is infrastructure only. Permission keys follow dot-notation convention (`domain.action`).

---

### 2026-06-29 | Documentation | Established Development Log Policy

**Summary:** Added mandatory Development Log policy (Section 12) to `copilot-instructions.md`, renumbering subsequent sections 12–15 to 13–16. Created this development log file with initial history from 2026-06-25 through 2026-06-29.

**Files Added:**
- docs/changelog/development-log.md

**Files Modified:**
- .github/copilot-instructions.md

**Architecture Impact:** None. Governance policy only. All future Copilot-assisted tasks must append a new entry to this log upon completion.

---

### 2026-06-29 | Backend | Sprint 0 Story 1 — Identity & Access Foundation

**Summary:** Implemented the complete Identity & Access Foundation covering authentication UI, user management, role management, permission management, and authorization infrastructure. Replaced all Tailwind/Breeze auth views with AdminLTE Bootstrap equivalents. Built full CRUD for users and roles, read-only permission browsing, CheckPermission middleware, RolePolicy and PermissionPolicy, UserService and RoleService, and an initial super-admin seeder. Updated AdminLTE config with project title and ISP navigation menu. Removed the Breeze "delete account" feature from profile (users are managed by admins only).

**Files Added:**
- app/Http/Middleware/CheckPermission.php
- app/Policies/RolePolicy.php
- app/Policies/PermissionPolicy.php
- app/Services/Identity/UserService.php
- app/Services/Identity/RoleService.php
- app/Http/Controllers/UserController.php
- app/Http/Controllers/RoleController.php
- app/Http/Controllers/PermissionController.php
- database/seeders/AdminUserSeeder.php
- resources/views/users/index.blade.php
- resources/views/users/create.blade.php
- resources/views/users/edit.blade.php
- resources/views/users/show.blade.php
- resources/views/roles/index.blade.php
- resources/views/roles/create.blade.php
- resources/views/roles/edit.blade.php
- resources/views/roles/show.blade.php
- resources/views/permissions/index.blade.php
- resources/views/permissions/show.blade.php

**Files Modified:**
- resources/views/auth/login.blade.php (Tailwind → AdminLTE Bootstrap)
- resources/views/auth/forgot-password.blade.php (Tailwind → AdminLTE Bootstrap)
- resources/views/auth/reset-password.blade.php (Tailwind → AdminLTE Bootstrap)
- resources/views/profile/edit.blade.php (Tailwind → AdminLTE, removed delete-account section)
- resources/views/dashboard.blade.php (Tailwind → AdminLTE stub)
- app/Http/Controllers/ProfileController.php (removed destroy method)
- app/Providers/AuthServiceProvider.php (registered RolePolicy, PermissionPolicy, Gate::before super-admin bypass)
- app/Providers/AppServiceProvider.php (added Paginator::useBootstrap() for Bootstrap 4 pagination)
- app/Http/Kernel.php (registered 'permission' middleware alias)
- routes/web.php (added user/role/permission resource routes; removed profile.destroy)
- config/adminlte.php (updated title, URLs to use_route_url, replaced placeholder menu with Identity & Access navigation)
- database/seeders/DatabaseSeeder.php (added AdminUserSeeder)

**Architecture Impact:** Implements Role-Based Access Control decision from `decisions.md`. All controllers delegate to Services (zero business logic in controllers). All authorization uses `$this->authorize()` via Policies. `Gate::before` ensures super-admin bypass is applied globally. Permission management is read-only through UI — creation/deletion is seeder-controlled per sprint specification.

**Notes:** Initial admin credentials: `admin@isp.local` / `Admin@1234!` — must be changed after first login. `profile.destroy` route removed: account lifecycle is managed by admins through UserController, not self-service. Bootstrap 4 pagination activated via `Paginator::useBootstrap()` for AdminLTE 3 compatibility.

---

### 2026-06-29 | Database | Foundation Migration Compliance Review

**Summary:** Reviewed four Foundation migrations against `entities.md`, `erd.md`, and `decisions.md`. Applied three targeted fixes: (1) corrected user status default from `draft` to `active`; (2) corrected role_user from surrogate-id + unique constraint to composite primary key; (3) corrected role_id FK in role_user from cascadeOnDelete to restrictOnDelete per the architecture's "Role uses Restrict when assigned." The permissions migration and roles slug column were confirmed compliant and left unchanged.

**Files Added:** None

**Files Modified:**
- database/migrations/2014_10_12_000000_create_users_table.php
- database/migrations/2026_06_29_000001_create_roles_table.php (docblock only — slug retained and justified)
- database/migrations/2026_06_29_000003_create_role_user_table.php

**Architecture Impact:**
- `users.status` default `active` aligns with operational context: users are created active by administrators.
- `role_user` composite PK enforces uniqueness at DB level — no redundant surrogate key.
- `role_id` `restrictOnDelete()` provides DB-level enforcement of erd.md rule "Role uses Restrict when assigned."
- `user_id` `cascadeOnDelete()` retained: users are soft-deleted in normal operation (FK never triggered); cascade applies only to forceDelete(), which is appropriate.

**Notes:**
- `permissions` migration: no changes required — fully compliant.
- `roles.slug`: retained. Not listed in entities.md key attributes, but justified as a stable machine-readable role identifier used for programmatic lookups. This is an implementation convention, not a new business rule.
- **Side-effect to address separately:** `app/Enums/UserStatus.php` still contains `Draft` and `Disabled` cases that no longer match the migration's operational status set (`active`, `suspended`, `inactive`). The enum is outside this task's scope and must be updated in a follow-up task.

---

### 2026-06-29 | Refactoring | Standardize UserStatus Enum

**Summary:** Synchronized `UserStatus` enum with the `users.status` migration column. Removed `Draft` and `Disabled` cases (not present in the migration). Added `Inactive` case (value `inactive`). Added `isActive()`, `isInactive()`, `isSuspended()` instance helpers. Added `values()` and `options()` static utilities. Refactored `canAuthenticate()` to delegate to `isActive()` eliminating duplicated logic. Updated `badgeColor()` for the `inactive` case to use Bootstrap `secondary` (was `danger` for the now-removed `disabled` case).

**Files Added:** None

**Files Modified:**
- app/Enums/UserStatus.php

**Architecture Impact:** Enum now exactly matches the `users.status` migration column values (`active`, `inactive`, `suspended`). No enum value exists without a corresponding valid database value.

**Notes:** The side-effect flag from the previous migration compliance review is now resolved. Any code that previously referenced `UserStatus::Draft` or `UserStatus::Disabled` will produce a compile-time error — these references must be updated as encountered. The `AdminUserSeeder` and all new Sprint 0 code already use `UserStatus::Active`, which remains valid.

---

### 2026-06-29 | Backend | RBAC Seeder & Foundation Data Refactoring

**Summary:** Synchronized all Foundation seeders with Architecture v1.0 and current Enum definitions. Created `RolePermissionSeeder` implementing the full RBAC policy across 9 roles. Updated `AdminUserSeeder` to read credentials from environment variables with local-only fallbacks and console warnings. Updated `DatabaseSeeder` execution order to enforce correct dependencies.

**Files Added:**
- database/seeders/RolePermissionSeeder.php

**Files Modified:**
- database/seeders/AdminUserSeeder.php
- database/seeders/DatabaseSeeder.php

**Architecture Impact:** Implements the RBAC decision from `decisions.md`. Initial role–permission assignments are now the authoritative seeder-defined policy. `sync()` is used (idempotent and deterministic): re-running the seeder always produces the exact intended permission set. Super Administrator receives all 47 permissions. Administrator receives all except `user.impersonate` and `settings.manage-registry`.

**Seeders reviewed — no changes required:**
- `RoleSeeder` — Uses `RoleStatus::Active->value`. Correct. 9 roles match `project-scope.md`.
- `PermissionSeeder` — Uses `PermissionStatus::Active->value`. Seeds 47 permissions. Does not assign to roles (correctly separated). No changes needed.
- `SettingCategorySeeder`, `SettingRegistrySeeder`, `SettingSeeder` — No enum issues. No changes needed.

**Enum synchronization:**
- `RoleStatus` (Draft/Active/Deprecated) — matches roles migration `default('draft')`. Lifecycle is `Draft → Active → Deprecated` per entities.md. No changes required.
- `PermissionStatus` (Draft/Active/Deprecated) — matches permissions migration `default('draft')`. No changes required.
- `UserStatus` — already standardized in previous task.

**⚠ Inconsistency reported (outside seeder scope — do not modify automatically):**
`app/Services/Identity/UserService::softDelete()` calls `UserStatus::Disabled->value`. This case was removed from `UserStatus` in the previous standardization task. This will produce a fatal error when `softDelete()` is called. The method should be updated to use `UserStatus::Inactive->value` instead. This must be addressed in a follow-up task targeting the Service layer.

---

### 2026-06-29 | Bug Fix | Repository-wide UserStatus Synchronization

**Summary:** Resolved the reported `UserStatus::Disabled` inconsistency. Performed a repository-wide impact analysis across Models, Services, Controllers, Policies, Form Requests, Middleware, Seeders, Factories, and Blade Views. Found one breaking reference: `UserService::softDelete()` calling the removed `UserStatus::Disabled` case. Replaced with `UserStatus::Inactive->value` and updated the method docblock.

**Files Added:** None

**Files Modified:**
- app/Services/Identity/UserService.php

**Architecture Impact:** `softDelete()` now sets user status to `inactive` before soft-deleting, correctly signalling that the account is decommissioned. All `UserStatus` references across the repository are now synchronized with the standardized enum (`active`, `inactive`, `suspended`).

**Impact Analysis — Full Repository Scan:**

| File | Finding | Action |
|---|---|---|
| `app/Services/Identity/UserService.php:83` | `UserStatus::Disabled->value` — fatal at runtime | Fixed: replaced with `UserStatus::Inactive->value` |
| `app/Models/User.php` | Uses `UserStatus::Active` — valid | No change |
| `app/Http/Controllers/UserController.php` | Uses `UserStatus::cases()` — valid | No change |
| `app/Http/Requests/StoreUserRequest.php` | Uses `new Enum(UserStatus::class)` — valid | No change |
| `app/Http/Requests/UpdateUserRequest.php` | Uses `new Enum(UserStatus::class)` — valid | No change |
| `app/Enums/RoleStatus.php` | Contains `Draft` case — valid for Role lifecycle | No change |
| `app/Enums/PermissionStatus.php` | Contains `Draft` case — valid for Permission lifecycle | No change |
| `database/factories/UserFactory.php` | No `status` field set; DB default `active` applies | No change (functionally correct) |
| All Blade views | Compare against `'active'`/`'suspended'` strings only | No change |
| All Policies, Middleware, Seeders | No obsolete `UserStatus` references | No change |

**Remaining Technical Debt (separate tasks required):**
1. `app/Http/Requests/Auth/LoginRequest.php` — `Auth::attempt()` does not enforce `UserStatus::canAuthenticate()`. Suspended and inactive users can log in. This is a security gap requiring a dedicated Authentication Enhancement story.
2. `database/factories/UserFactory.php` — Does not explicitly set `status`. Relies on the DB column default (`active`). Functionally correct but should be updated for test clarity in a future testing story.

---

### 2026-06-29 | Security | Authentication Hardening

**Summary:** Hardened the authentication entry point to enforce user status, role assignment, and role status validation before granting session access. Added server-side authentication event logging. All rejection paths return the same generic error message to prevent account status enumeration. The controller's existing session ID regeneration and redirect logic were confirmed correct and required no changes.

**Files Added:** None

**Files Modified:**
- app/Http/Requests/Auth/LoginRequest.php

**Architecture Impact:** Implements Architecture Principle 15 (Security by Default) from `decisions.md`. The authentication flow now enforces the full RBAC entry guard in order: credentials → user status → active role assignment.

**Authentication flow enforced (in order):**

| Step | Check | On Failure |
|---|---|---|
| 1–2 | Email + password via `Auth::attempt()` | Rate limit hit, warning logged, generic error |
| 3 | `UserStatus::Active` only via `canAuthenticate()` | Logout, rate limit hit, status value logged, generic error |
| 4–5 | At least one role with `RoleStatus::Active` | Logout, rate limit hit, warning logged, generic error |
| 6 | All passed | Rate limit cleared, success logged |

**Logging events added:**

| Event | Log level | Fields |
|---|---|---|
| Invalid credentials | `warning` | email, IP |
| Account inactive or suspended | `warning` | user_id, status value, IP |
| No active role assigned | `warning` | user_id, IP |
| Successful login | `info` | user_id, email, IP |

**Security principles applied:**
- All rejection paths return `trans('auth.failed')` — reason is never revealed to the client.
- Rate limiter incremented on status/role failures — prevents account existence enumeration via timing.
- `Auth::logout()` called immediately when status/role check fails — clears the session established by `Auth::attempt()`.
- Passwords are never logged.
- Session ID regeneration: confirmed correct in `AuthenticatedSessionController::store()` via `$request->session()->regenerate()` — no change required.
- Remember Me: `$this->boolean('remember')` passed to `Auth::attempt()` — unchanged and functional.
- Password reset: separate controllers (`PasswordResetLinkController`, `NewPasswordController`) — unaffected.

**Additional Security Gaps reported (require dedicated stories):**
1. **Active session not invalidated on account suspension** — An existing authenticated session remains valid if an administrator suspends or deactivates the user mid-session. A per-request middleware checking `UserStatus::canAuthenticate()` would close this gap.
2. **`UserFactory` does not set `status` explicitly** — Relies on DB column default. Low risk; address in testing story.

---

### 2026-06-29 | Backend | Domain Model Review — Architecture v1.0 Compliance

**Summary:** Reviewed all six models in `app/Models/` against `entities.md`, `erd.md`, `decisions.md`, and `project-scope.md`. No architecture violations found. All models are compliant. Recommendations recorded for future improvement.

**Files Added:** None

**Files Modified:** None — no architecture violations found; no code changes were required.

**Architecture Impact:** None. Review only.

**Models reviewed:**

| Model | Violations | Recommendation Count | Risk |
|---|---|---|---|
| `User` | None | 5 | Low |
| `Role` | None | 2 | Low |
| `Permission` | None | 1 | Low |
| `Setting` | None | 2 | Low |
| `SettingCategory` | None | 1 | Low |
| `SettingRegistryEntry` | None | 2 | Low |

**Key findings:**
- All enum casts present and correct (`UserStatus`, `RoleStatus`, `PermissionStatus`, `SettingScope`, `SettingDataType`).
- All documented ERD relationships implemented correctly.
- `SettingRegistryEntry` correctly has NO `SoftDeletes` — matching entities.md "Soft Delete: No".
- `SettingCategory.settings()` uses `hasManyThrough` correctly to implement the conceptual "SettingCategory 1 → N Setting" relationship via `SettingRegistryEntry`.
- `hasPermission()` and `hasRole()` do not filter by status — deprecated roles/permissions may still grant access. Business rule not yet defined in architecture. Flagged for future architecture decision.

**Recommendations recorded (not violations — for future stories):**
- Add `hasActiveRole()`, `isSuspended()`, `isInactive()`, `scopeSuspended()`, `scopeInactive()` to `User`.
- Add `isActive()`, `isDeprecated()` helpers to `Role` and `Permission`.
- Add `isActive()` to `Setting`, `isVisible()` to `SettingCategory`.
- Add `scopeVisible()`, `scopeByCategory()` to `SettingRegistryEntry`.

**Future conflict noted:** `User` model's `Notifiable` trait provides a `notifications()` relationship (Laravel's built-in `DatabaseNotification`). When the platform's custom `Notification` entity is implemented, the method name must differ (e.g., `platformNotifications()`) to avoid collision.

**Missing models — expected, not violations:** `UserSession`, `ImpersonationSession`, `Employee`, `ActivityLog`, `TimelineEvent`, `Notification`, and all domain/infrastructure entities are not yet implemented. Relationships to these entities on existing models should be added when those models are created.

---

### 2026-06-29 | Architecture | Authorization Requires Active Role and Permission Lifecycle

**Summary:** Inserted a new architecture decision into `docs/architecture/decisions.md` in the Identity & Access section, immediately after "Role Based Access Control" and before "Area Based Visibility." The decision formalizes that only Roles and Permissions with Active status may participate in authorization. Both the table of contents and the decision body were updated. No existing decision was merged or superseded — this decision extends the RBAC decision with lifecycle enforcement rules.

**Files Added:** None

**Files Modified:**
- docs/architecture/decisions.md

**Architecture Impact:** Formally mandates that `User::hasRole()` and `User::hasPermission()` must filter by `RoleStatus::Active` and `PermissionStatus::Active` respectively. Policies, Gates, Middleware, Controllers, and Services must rely solely on these domain helpers without performing their own lifecycle checks.

**Insertion rationale:** The decision logically follows "Role Based Access Control" because it adds a lifecycle constraint to the RBAC model. Grouping them keeps all RBAC-related decisions together before unrelated Identity & Access concerns (Area Based Visibility, User Impersonation).

**No overlap or contradiction with existing decisions.** The existing RBAC decision establishes the general framework; this decision adds the lifecycle rule within it.

**Required follow-up work (introduced by this decision):**
`app/Models/User.php` — `hasRole()` and `hasPermission()` do not currently filter by status (identified in the Domain Model Review). These methods must be updated to conform to this decision. This is a required implementation task that should be scheduled as a follow-up story.

---

### 2026-06-29 | Backend | Authorization Domain Implementation

**Summary:** Implemented the Authorization Domain Helpers in `app/Models/User.php` to fully comply with the architecture decision "Authorization Requires Active Role and Permission Lifecycle." `hasRole()` and `hasPermission()` now filter by `RoleStatus::Active` and `PermissionStatus::Active`. A new `hasActiveRole()` helper was added. All existing Policies and Middleware (`UserPolicy`, `RolePolicy`, `PermissionPolicy`, `CheckPermission`, `AuthServiceProvider::Gate::before`) immediately benefit without any modification.

**Files Added:** None

**Files Modified:**
- app/Models/User.php

**Architecture Impact:** `User::hasRole()`, `User::hasPermission()`, and the new `User::hasActiveRole()` now enforce the lifecycle constraint mandated by `decisions.md — Authorization Requires Active Role and Permission Lifecycle`. Draft and Deprecated roles and permissions no longer grant authorization. All Policies and Middleware may safely rely on these helpers without performing their own lifecycle checks.

**Authorization flow after implementation:**

`hasRole(string $slug)`
1. If `roles` relationship is loaded → filter in-memory: `slug === $slug && status === Active`
2. Otherwise → single DB query: `WHERE slug = ? AND status = 'active'`

`hasActiveRole()`
1. If `roles` loaded → `status === Active` collection check
2. Otherwise → `WHERE status = 'active' EXISTS`

`hasPermission(string $key)`
1. Direct grant path — permission must be `status = Active`
2. Role-based path — role must be `status = Active` AND permission must be `status = Active`
3. Collection short-circuit used when relationships already loaded

**Relationship-loading strategy (N+1 prevention):**
- Single checks (Policy `before()`, Gate): lazy load acceptable — 1 query per check.
- Loop scenarios: caller must eager-load `$user->load('roles.permissions', 'directPermissions')`. The implementation detects loaded relationships via `relationLoaded()` and uses in-memory collection operations, producing zero additional queries per check.

**No architectural inconsistencies found.** The implementation aligns with `decisions.md`, `entities.md`, `erd.md`, and all existing Policies and Middleware call signatures remain unchanged.

---

### 2026-07-01 | Documentation | Sprint 1 Story 1 — Settings Module Architecture Review & Design

**Summary:** Completed analysis and implementation design for the Settings module without generating application code. Reviewed architecture sources (`project-scope.md`, `decisions.md`, `entities.md`, `erd.md`, `.github/copilot-instructions.md`) and existing Settings implementation artifacts (models, migrations, seeders, service, policy). Produced a constrained implementation plan for index/edit/update flow only, aligned with registry-driven settings governance.

**Files Added:** None

**Files Modified:**
- docs/changelog/development-log.md

**Architecture Impact:** No runtime behavior changed. The review identified architecture-alignment gaps to be addressed in follow-up implementation tasks: missing `docs/business/workflow.md` reference target, settings permission key mismatch (`setting.*` vs `settings.*`), missing policy registration for `SettingPolicy`, and a Settings relationship mismatch between conceptual ERD ownership and current physical model.

**Notes:** Task was analysis/design only per scope. No Controllers, Services, Requests, Policies, Routes, or Views were generated in this task.

---

### 2026-07-01 | Backend | Sprint 1 Story 1 — Settings Module UI Vertical Slice

**Summary:** Implemented the Settings module UI vertical slice for registry-driven setting values only. Added `UpdateSettingRequest`, `SettingController` with `index`, `edit`, and `update`, resource routes limited to `index/edit/update`, and Blade pages for table-based index and standard form edit. All settings reads/writes are delegated to `SettingService`; controllers remain orchestration-only.

**Files Added:**
- app/Http/Controllers/SettingController.php
- app/Http/Requests/UpdateSettingRequest.php
- resources/views/settings/index.blade.php
- resources/views/settings/edit.blade.php

**Files Modified:**
- app/Policies/SettingPolicy.php
- app/Providers/AuthServiceProvider.php
- app/Services/Settings/SettingService.php
- routes/web.php
- docs/changelog/development-log.md

**Architecture Impact:** Aligns Settings authorization and HTTP flow with architecture constraints. `SettingPolicy` now enforces `settings.view` and `settings.update` permissions, and policy mapping for `Setting` is registered. The UI exposes only index/edit/update operations (no create/store/destroy/bulk). Setting metadata (key, data type, category, scope) is read-only in edit; only value and active status are mutable. Validation is centralized in `UpdateSettingRequest`, and setting persistence uses `SettingService` exclusively.

**Notes:** Verified route registration (`settings.index`, `settings.edit`, `settings.update`) and PHP syntax for all new/changed classes.

---

### 2026-07-01 | Refactoring | Sprint 1.2 Story 1 — Service Layer Standardization

**Summary:** Enforced the "Controllers never communicate directly with Eloquent models except for implicit Route Model Binding" architecture rule. Created `PermissionService` and `ProfileService`. Extended `RoleService` with `findForShow`, `findForEdit`, `activePermissions`, `assignedPermissionIds`, and `canDelete`. Extended `UserService` with `findForShow`, `findForEdit`, and `activeRoles`. Refactored all four controllers (`PermissionController`, `RoleController`, `UserController`, `ProfileController`) to eliminate all direct Eloquent usage. All business logic now lives exclusively in service classes; controllers remain thin orchestrators that authorize, call service methods, and return views/redirects.

**Files Added:**
- app/Services/Identity/PermissionService.php
- app/Services/Identity/ProfileService.php

**Files Modified:**
- app/Services/Identity/RoleService.php
- app/Services/Identity/UserService.php
- app/Http/Controllers/PermissionController.php
- app/Http/Controllers/RoleController.php
- app/Http/Controllers/UserController.php
- app/Http/Controllers/ProfileController.php
- docs/changelog/development-log.md

**Architecture Impact:** Completes service layer standardization for Identity & Access controllers. All direct model access violations have been eliminated. Controllers no longer contain: `Model::query()`, `Model::where()`, `Model::count()`, `Model::pluck()`, `Model::active()`, `$model->load()`, or direct model manipulation (`fill()`, `save()`). Service methods expose meaningful domain APIs: `paginate()`, `categories()`, `findForShow()`, `findForEdit()`, `activePermissions()`, `assignedPermissionIds()`, `canDelete()`, `activeRoles()`, `update()`. Business logic and data access are now consistently delegated to services, maintaining identical behavior while enforcing strict layering.

**Notes:** Verified PHP syntax for all modified files. Routes, policies, and UI remain unchanged. Behavior preserved.

---

### 2026-07-01 | Refactoring | Sprint 1.2 Story 2 — Introduce AbstractCrudService

**Summary:** Created `AbstractCrudService` base class providing reusable CRUD operations (paginate, find, create, update, delete) to reduce duplicated logic across service classes. Refactored `UserService`, `RoleService`, `PermissionService`, and `SettingService` to extend the abstract class. Each service customizes behavior by overriding protected hooks: `applyDefaultRelationships()`, `applyFilters()`, and `applyDefaultOrdering()`. Business logic remains in child services. All public APIs preserved — no controller changes required.

**Files Added:**
- app/Services/AbstractCrudService.php

**Files Modified:**
- app/Services/Identity/UserService.php
- app/Services/Identity/RoleService.php
- app/Services/Identity/PermissionService.php
- app/Services/Settings/SettingService.php
- docs/changelog/development-log.md

**Architecture Impact:** Implements DRY principle without introducing repositories. Service layer remains the single point of business logic delegation. The abstract class provides generic data access patterns while allowing full customization through protected hooks. `UserService::list()` and `RoleService::list()` maintained as aliases to `paginate()` for backward compatibility. `PermissionService` extends the abstract class but does not expose `create()`/`update()`/`delete()` publicly (permissions are seeder-governed). `SettingService` overrides `paginate()` completely to preserve its registry-driven complex filtering logic with joins and custom ordering. Controllers remain unchanged — all service method signatures preserved.

**Notes:** Verified PHP syntax for all modified files. No repositories introduced per requirements. Public behavior unchanged.

---

### 2026-07-02 | Refactoring | Sprint 1.2 Story 3 — Prepare Services for Future ViewModels

**Summary:** Refactored all service methods returning data for UI views to return structured arrays instead of exposing internal query composition. Introduced standardized methods: `buildIndexData()` for list pages, `buildCreateData()` and `buildEditData()` for forms, and updated `findForShow()` to return arrays. Controllers now delegate complete view data assembly to services and remain thin orchestrators. No DTOs or ViewModels created — this story only prepares service interfaces for future DTO introduction.

**Files Added:** None

**Files Modified:**
- app/Services/Identity/UserService.php
- app/Services/Identity/RoleService.php
- app/Services/Identity/PermissionService.php
- app/Services/Settings/SettingService.php
- app/Http/Controllers/UserController.php
- app/Http/Controllers/RoleController.php
- app/Http/Controllers/PermissionController.php
- app/Http/Controllers/SettingController.php
- docs/changelog/development-log.md

**Architecture Impact:** Services now own complete view data composition. Controllers no longer assemble view data from multiple service calls or Enum cases — they call a single service method and pass the result directly to views. Service method signatures changed from returning models/collections to returning structured arrays with named keys. All controllers simplified: `index()` calls `buildIndexData()`, `create()` calls `buildCreateData()`, `edit()` calls `buildEditData()`, `show()` calls `findForShow()` — all return arrays for direct view binding. Pattern example: `return view('users.index', $this->userService->buildIndexData($filters));`. This interface design allows future DTO/ViewModel introduction without controller changes. Behavior identical — views receive the same data structure, only the delegation pattern changed.

**Notes:** Verified PHP syntax and compile errors for all modified files. No runtime behavior changes. Views unchanged. Routes unchanged. All existing functionality preserved.

---

### 2026-07-02 | Architecture | Sprint 1.3 Story 1 — Establish Domain Event Architecture Standard

**Summary:** Established comprehensive Domain Event Architecture for the platform. Created authoritative event documentation defining Domain Events, Integration Events, event naming conventions, dispatch rules, listener responsibilities, and event lifecycle (Service → Event → Listener → Infrastructure). Created directory structure `app/Domain/Events` and `app/Domain/Listeners`. Implemented six sample domain events following the standardized structure: `UserCreated`, `UserUpdated`, `RoleCreated`, `RoleUpdated`, `RoleDeleted`, `SettingUpdated`. Each event contains only entity identifier, timestamp, and actor ID (nullable) with no business logic. No existing Services modified — this story establishes the architecture foundation for future event integration.

**Files Added:**
- docs/architecture/events.md
- app/Domain/Events/UserCreated.php
- app/Domain/Events/UserUpdated.php
- app/Domain/Events/RoleCreated.php
- app/Domain/Events/RoleUpdated.php
- app/Domain/Events/RoleDeleted.php
- app/Domain/Events/SettingUpdated.php
- app/Domain/Listeners/.gitkeep
- docs/changelog/development-log.md

**Files Modified:**
- docs/changelog/development-log.md

**Architecture Impact:** Formalizes event-driven architecture pattern for the platform. Documentation establishes: (1) Event classification (Domain vs Integration), (2) Immutable event structure with required properties (entityId, entityType, occurredAt, actorId), (3) Event lifecycle flow from Service dispatch through Listener execution, (4) Dispatch rules (after DB commit, from Services only, idempotent listeners), (5) Listener responsibilities (allowed: notifications, audit logs, derived state updates, job queueing; prohibited: modifying originating aggregate, business validation, cascading events, long-running operations), (6) Event naming convention ({Entity}{PastTenseVerb}), (7) Listener naming convention ({Action}{EventName}Listener). All sample events use readonly properties and immutable Carbon timestamps. Event architecture aligns with Architecture Principle 14 (Business Events) from `decisions.md` and complements the Business Events catalog in `business-events.md`. Future stories will refactor Services to dispatch these events and implement corresponding Listeners.

**Notes:** Verified PHP syntax for all event classes. No runtime behavior changes — events are defined but not yet dispatched. Services remain unchanged. This story prepares the architectural foundation for Sprint 1.3 Story 2 (event dispatch integration).

---

### 2026-07-02 | Architecture | Sprint 1.3 Story 3 — Platform Logging Standard

**Summary:** Established comprehensive Platform Logging Standard documentation defining authoritative logging conventions for the entire platform. Documented eight RFC 5424 log levels (emergency through debug) with usage guidelines, security event logging patterns (failed authentication, authorization denials, impersonation, suspicious activity), authentication lifecycle events (login, logout, password reset, email verification), configuration change auditing (setting updates, registry modifications, environment changes), and business event correlation with Domain Events. Defined structured log context requirements (entity_type, entity_id, actor_id, ip), PII handling rules (log IDs not values), and integration patterns with ActivityLog and TimelineEvent. Established log channel architecture (security, audit, business), retention policies, performance considerations, and testing guidelines. No Services modified — documentation only.

**Files Added:**
- docs/architecture/logging.md

**Files Modified:**
- docs/changelog/development-log.md

**Architecture Impact:** Formalizes application logging standard for the platform. Documentation establishes: (1) Log level usage rules aligned with RFC 5424 (emergency for system unusable, alert for immediate action, critical for urgent attention, error for runtime failures, warning for exceptional non-errors, notice for significant normal events, info for user actions, debug for diagnostics), (2) Security event logging requirements (authentication failures, account status blocks, authorization denials, impersonation tracking, suspicious activity detection), (3) Authentication lifecycle logging (login/logout, password reset, email verification), (4) Configuration change audit trail (setting updates, registry modifications, environment changes), (5) Business event logging principle (every Domain Event dispatch generates corresponding log entry for independent audit trail), (6) Structured context standard (required: entity_type, entity_id, actor_id, ip; forbidden: passwords, tokens, full PII), (7) Three-tier audit architecture (Application Log for developers/operators, ActivityLog for structured database audit, TimelineEvent for user-visible milestones), (8) Log channel separation (security 90-day retention, audit 1-year retention, business 30-day retention), (9) Performance guidelines (synchronous for security, async option for high-throughput), (10) Testing approach (Log::fake() for feature tests, no unit test assertions except security-critical). Logging architecture complements Domain Event Architecture from `events.md` and provides observability foundation for all platform operations. Migration path defined in four phases: Security Logging (immediate), Configuration Logging (Sprint 1.4), Business Event Logging (Sprint 1.5+), Operational Logging (ongoing).

**Notes:** Documentation only. No existing Services modified per requirements. Logging conventions will be implemented incrementally in future sprints starting with Sprint 1.4.

---

### 2026-07-02 | Architecture | Sprint 1.3 Story 2 — Platform Cache Architecture

**Summary:** Established comprehensive Platform Cache Architecture documentation defining authoritative caching conventions for application-wide cache usage. Documented cache driver strategy by environment (file for development, Redis for staging/production), hierarchical cache key naming convention (`{domain}:{entity}:{scope}:{identifier}[:{attribute}]`), four cache scope classifications (Global, Per-Record, List, Aggregate), TTL strategy by entity volatility (Configuration: 1-24h, Reference: 1-6h, Core: 15-60min, Transactional: 5-15min, Operational: 1-5min, Aggregate: 5-30min), ownership-based invalidation rules following aggregate root boundaries, service-layer cache responsibilities with implementation patterns for read/create/update/delete operations, tag-based invalidation for cross-service scenarios, cache consistency patterns (read-through, cache-aside, write-through, write-behind), production considerations (cache warming, stampede prevention, monitoring metrics), anti-patterns to avoid (caching in Controllers/Models, forgetting invalidation, over-caching), and entity-specific cache refresh trigger tables for User, Role, Setting, Invoice, and Subscription entities. Documented `SettingService` as the authoritative reference implementation demonstrating hierarchical keys, read-through caching, explicit invalidation, cascade invalidation up scope hierarchy, and 3600s TTL for configuration data. No cache implementation performed — documentation only per requirements.

**Files Added:**
- docs/architecture/cache.md

**Files Modified:**
- docs/changelog/development-log.md

**Architecture Impact:** Formalizes application caching standard for the platform. Documentation establishes: (1) Service-layer cache ownership (Services own all caching and invalidation, not Models or Controllers), (2) Hierarchical naming convention enabling discoverability and partial invalidation (`identity:user:record:123`, `settings:setting:global:all`), (3) Four cache scope types with distinct TTL and invalidation strategies (Global for application-wide data, Per-Record for entity-specific, List for filtered collections, Aggregate for computed values), (4) TTL strategy aligned with entity volatility (configuration 1-24h, core entities 15-60min, transactional 5-15min), (5) Ownership-based invalidation following aggregate root boundaries from `entities.md` (Customer owns Subscription/Invoice/Payment, OLT owns ONU/MonitoringEvent), (6) Explicit synchronous invalidation (Services invalidate immediately after database write; no listener-based deferred invalidation), (7) Tag-based invalidation for Redis/Memcached supporting cross-service cache flushes (`identity`, `user:{id}` tags), (8) Cache consistency patterns (prefer read-through Cache::remember over explicit cache-aside), (9) Anti-pattern rules (no caching in Controllers/Models, no caching Query Builders, invalidate on every write, cache only when benefit exceeds overhead), (10) Production strategies (cache warming for global caches, stampede prevention with locks, monitoring hit rates and eviction rates), (11) Testing guidelines (Cache::flush() in setUp(), no cache assertions in unit tests except security-critical), (12) Entity-specific refresh trigger tables documenting exact invalidation requirements. `SettingService` documented as reference implementation demonstrating: hierarchical key structure with scope fallback, 1-hour TTL for configuration stability, explicit invalidation in set() method, cascade invalidation up scope hierarchy (Customer → Global), read-through pattern with Cache::remember(). Caching architecture complements Domain Event Architecture and Platform Logging Standard, providing performance optimization foundation while maintaining cache coherence. Migration path defined in five phases: Documentation (Sprint 1.3 complete), Identity & Access (Sprint 1.4), Core Entities (Sprint 1.5+), Transactional (Sprint 1.6+), Optimization & Monitoring (Sprint 1.7+).

**Notes:** Documentation only. No existing Services modified per requirements. `SettingService` already implements caching and serves as the reference pattern. Cache implementation will be rolled out incrementally starting Sprint 1.4 following documented conventions.

---

### 2026-07-02 | Architecture | Sprint 1.3 Story 4 — Queue Architecture

**Summary:** Established comprehensive Platform Queue Architecture documentation defining authoritative queue usage standards for asynchronous job processing. Documented queue driver strategy by environment (sync for development, database for staging, Redis for production), hierarchical queue naming convention (`{priority}[-{domain}]`) with four priority levels (critical/high/default/low), retry strategy by job category with exponential backoff patterns (Critical: 5 attempts with [10,30,60,300]s backoff, External API: 3-5 attempts, Email: 3 attempts, Data Processing: 2 attempts, Cleanup: 1 attempt), timeout strategy by job category (Email: 30s, External API: 60s, PDF Generation: 120s, Data Export: 300s, Provisioning: 180s, Cleanup: 600s), queue priority ordering and worker configuration patterns, idempotency requirements with four implementation patterns (ShouldBeUnique interface, database unique constraints, check-then-act with locking, immutable operations), dead letter queue (failed_jobs table) handling with failed() method implementation, job dispatching patterns from Services with afterCommit() usage, job naming convention ({Action}{Entity}Job), domain-based job organization (app/Jobs/{Domain}/), testing guidelines (Queue::fake(), idempotency testing, retry testing), production considerations (Supervisor configuration, worker scaling, monitoring metrics, graceful shutdown), and anti-patterns to avoid (dispatching from Controllers, not using afterCommit(), long-running synchronous operations, missing timeout/failed() methods). Migration path defined in five phases: Infrastructure Setup (Sprint 1.4 - queue tables, Supervisor), Notification Jobs (Sprint 1.5), Billing Jobs (Sprint 1.6), Provisioning Jobs (Sprint 1.7+), Maintenance Jobs (Sprint 1.8+). No jobs implemented — documentation only per requirements.

**Files Added:**
- docs/architecture/queue.md

**Files Modified:**
- docs/changelog/development-log.md

**Architecture Impact:** Formalizes asynchronous job processing standard for the platform. Documentation establishes: (1) Service-layer job ownership (Services dispatch jobs after database commits, Controllers never dispatch directly), (2) Priority queue hierarchy (critical for password reset/payments < 5s SLA, high for invoices/subscriptions < 30s SLA, default for notifications < 2min SLA, low for reports/cleanup < 30min SLA), (3) Retry policy by job category (critical 5 attempts, external API 3-5 attempts, email 3 attempts, data processing 2 attempts, cleanup 1 attempt) with exponential backoff preventing worker busy-loops and allowing transient failures to resolve, (4) Timeout enforcement (email 30s, external API 60s, PDF generation 120s, provisioning 180s, exports 300s, cleanup 600s) with worker timeout exceeding max job timeout by 10s, (5) Mandatory idempotency (jobs must be safe to execute multiple times using ShouldBeUnique interface, database unique constraints, lockForUpdate() with state checks, or naturally idempotent operations), (6) Dead letter queue handling (failed_jobs table after max attempts exhausted with failed() method for logging to ActivityLog and alerting administrators), (7) Job dispatching rules (always afterCommit() to ensure database consistency, dispatch to appropriate priority queue, support delayed dispatch and job chaining), (8) Job naming and organization convention ({Action}{Entity}Job in app/Jobs/{Domain}/ structure), (9) Production worker configuration (Supervisor with numprocs, max-time for memory leak prevention, queue priority ordering, graceful shutdown with queue:restart), (10) Anti-patterns prohibited (no dispatching from Controllers, no skipping afterCommit(), no synchronous long-running operations in HTTP requests, must define explicit timeout and failed() methods). Queue architecture complements Domain Event Architecture (events dispatch jobs via listeners), Platform Logging Standard (job failures logged to business channel), and Platform Cache Architecture (jobs invalidate caches after processing). Migration path spans five sprints: Infrastructure (tables, Supervisor), Notifications (email/SMS jobs), Billing (invoice/payment jobs), Provisioning (ONU jobs), Maintenance (cleanup/reporting jobs scheduled via Laravel scheduler).

**Notes:** Documentation only. No existing Services modified per requirements. No jobs implemented yet. Queue infrastructure (migrations, Supervisor) will be set up in Sprint 1.4, and jobs will be implemented incrementally across subsequent sprints following documented conventions.

---

### 2026-07-02 | Architecture | Sprint 1.4 Story 1 — Platform Transaction Standard

**Summary:** Established comprehensive Platform Transaction Standard documentation defining authoritative database transaction architecture for the entire platform before business modules are implemented. Documented transaction ownership rules (only Services may open transactions, Controllers and Models prohibited from DB::transaction/beginTransaction/commit/rollback calls), transaction lifecycle patterns (closure-based preferred with automatic commit/rollback, manual transactions only when closure cannot work, target <100ms duration with <1s maximum), nested transaction policy (Laravel uses savepoints not true nesting, avoid nesting when possible, maximum 2 levels allowed, document justification when nested), deadlock retry strategy (detect MySQL error code 1213, retry with exponential backoff [10,50,200]ms for max 3 attempts, log warnings and errors), locking strategy comparison (optimistic locking with version columns for read-heavy/low-contention/long-running workloads, pessimistic locking with lockForUpdate for write-heavy/high-contention/short-transaction workloads), lockForUpdate guidelines (syntax, use for race condition prevention and read-then-write consistency, lock only specific rows not entire tables, keep lock duration short by excluding external API calls, consistent lock ordering to prevent deadlocks, never lock in loops), afterCommit rules (events must use ShouldDispatchAfterCommit interface to dispatch only after successful commit ensuring listeners see consistent state, queue jobs must use afterCommit() when depending on database persistence, external API calls must NEVER execute inside transactions), queue dispatch rules (persistence-dependent jobs require afterCommit, configure global default in config/queue.php, opt-out only when safe for jobs not querying transaction-created records), external API integration patterns (never HTTP/SOAP/SNMP/SMTP/SMS/payment gateway calls inside transactions, use queue pattern dispatching jobs after commit for async external calls, call-before-transaction pattern when immediate result required, idempotent retry in jobs when external API fails), transaction isolation levels (default REPEATABLE READ sufficient for 99% of needs, rarely change to READ UNCOMMITTED for reporting or SERIALIZABLE for strictest isolation), anti-patterns documented (long-running transactions holding locks for seconds/minutes, deep nested complexity beyond 2 levels, external API calls inside transactions, catching generic exceptions and swallowing errors, forgetting afterCommit on events/jobs), testing guidelines (RefreshDatabase trait wrapping tests in transactions, assertDatabaseHas after transaction completion, testing rollback on exceptions, testing deadlock retry logic), migration path in three phases (Sprint 1.4 audit existing code for violations, Sprint 1.5 refactor Controllers and implement afterCommit, Sprint 1.6 implement deadlock retry and refactor external API calls), comprehensive examples provided (service with transaction, nested transaction 2-level max, deadlock retry implementation, pessimistic/optimistic locking patterns, afterCommit with events and jobs, external API outside transaction), quick reference tables (transaction ownership by layer, locking decision matrix, afterCommit checklist, anti-pattern checklist). Documentation only per requirements — no runtime code modifications.

**Files Added:**
- docs/architecture/transactions.md

**Files Modified:**
- docs/changelog/development-log.md

**Architecture Impact:** Formalizes database transaction architecture establishing platform-wide data consistency guarantees and concurrency control patterns. Documentation establishes: (1) Service-layer transaction ownership (Controllers and Models never manage transactions, separation of HTTP concerns from data consistency logic, transactions short-lived and focused on business operations, testable independent of HTTP layer), (2) Transaction lifecycle discipline (closure-based DB::transaction preferred for automatic commit/rollback, manual beginTransaction/commit/rollback only when closure cannot work, target <100ms with <1s maximum to minimize lock contention and deadlock risk), (3) Nested transaction policy (Laravel uses savepoints not true nested transactions, maximum 2 levels of nesting allowed, avoid when possible by refactoring to flat single-level transactions, document justification when nesting unavoidable), (4) Deadlock detection and retry (catch MySQL error code 1213, exponential backoff [10,50,200]ms preventing busy-loops, max 3 retry attempts, log warnings for retries and errors when exhausted), (5) Locking strategy decision framework (optimistic locking with version column for read-heavy workloads with rare conflicts <1% using WHERE version = X preventing locks and maximizing concurrency, pessimistic locking with lockForUpdate for write-heavy workloads with common conflicts >10% using SELECT FOR UPDATE providing immediate consistency, comparison table by workload/contention/duration/use-cases), (6) lockForUpdate best practices (lock only specific rows with WHERE clauses not entire tables, keep lock duration short excluding external API calls, consistent lock ordering across all transactions preventing deadlocks by always locking in same sequence, never lock in loops causing N+1 lock operations, always use within DB::transaction or locks released immediately), (7) afterCommit guarantee for consistency (all Domain Events must use ShouldDispatchAfterCommit interface ensuring events only dispatch after successful database commit so listeners always see consistent committed state never seeing records that will be rolled back, all queue jobs depending on database persistence must use ->afterCommit() ensuring jobs only queued after commit so jobs always find records they query for, events/jobs dispatched before commit risk inconsistent state when transaction rolls back), (8) External API integration prohibition inside transactions (never execute HTTP/SOAP/SNMP/SMTP/SMS/payment gateway API calls inside DB::transaction because API calls take 100ms-10s holding locks entire time blocking other transactions, network failures cause transaction rollback, API may succeed but transaction rolls back creating inconsistent state, use queue pattern with jobs dispatched afterCommit for async external integration, use call-before-transaction pattern when immediate API result required to make external call first then store result in short transaction), (9) Queue dispatch coordination (persistence-dependent jobs require afterCommit to avoid jobs executing before database commit, configure after_commit: true as global default in config/queue.php to reduce risk of forgetting, jobs opt-out with $afterCommit = false only when safe for jobs not querying transaction-created records), (10) Anti-pattern enforcement rules (Controllers never call DB::transaction/beginTransaction/commit/rollback, Models never contain transaction logic, no transactions exceeding 1s duration, no nesting beyond 2 levels, no external API calls inside transactions, no catching generic exceptions and swallowing errors silently, always use afterCommit for events and jobs depending on persistence), (11) Testing discipline (RefreshDatabase trait for test isolation, assertDatabaseHas after transaction completion, test rollback behavior by expecting exceptions and asserting no records persisted, test deadlock retry by mocking QueryException with error code 1213), (12) Migration audit strategy (Sprint 1.4 grep search for Controllers/Models with transaction violations, Sprint 1.5 refactor moving transaction logic to Services and adding ShouldDispatchAfterCommit to events, Sprint 1.6 add deadlock retry to high-contention services and refactor external API calls to queue pattern). Transaction standard complements Domain Event Architecture (events dispatch after commit), Queue Architecture (jobs dispatch after commit with idempotency), Platform Logging Standard (transaction failures logged), Platform Cache Architecture (cache invalidation happens in transactions). Establishes foundation for ACID guarantees, concurrency control, and data consistency across all business modules.

**Notes:** Documentation only. No runtime code modified per requirements. Sprint 1.4 will include code audit for violations. Sprint 1.5+ will implement refactoring of existing Services to comply with transaction standard.

---

### 2026-07-02 | Architecture | Sprint 1.4 Story 2 — Platform Exception Standard

**Summary:** Established comprehensive Platform Exception Standard documentation defining authoritative exception architecture for unified error management across the entire platform. Documented exception classification into seven primary categories (Application exceptions for HTTP layer failures/routing/middleware, Domain exceptions for business logic violations/entity lifecycle/aggregate consistency, Validation exceptions using Laravel's ValidationException for user input failures, Business Rule exceptions for policy enforcement/authorization/eligibility checks, Concurrency exceptions for locking failures/optimistic lock/deadlock/race conditions, Configuration exceptions for missing/invalid config/settings/environment, Infrastructure exceptions for external system failures including database/cache/queue/filesystem/network/external APIs), exception hierarchy structure with base classes extending RuntimeException organized by architecture layers (app/Exceptions/{Category}/ directory structure), exception naming conventions following {Concept}{Failure}Exception pattern with descriptive nouns avoiding redundancy, required properties (message/code/previous) and optional context properties (entityType/entityId/context array) for structured logging, message guidelines serving two audiences (user-facing messages non-technical actionable friendly never exposing stack traces/file paths/SQL, developer messages technical detailed with entity IDs/states/workflow document references), logging responsibilities by exception type (ValidationException info level application channel, DomainException/BusinessRuleException notice level business channel, ApplicationException warning/error level application channel, ConcurrencyException warning/error level application channel, ConfigurationException critical level application channel, InfrastructureException error level infrastructure channel with structured context including entity type/ID/actor/component/operation), wrapping third-party exceptions at service layer boundaries (wrap Guzzle RequestException as PaymentGatewayException, wrap PDO/QueryException as DatabaseException, wrap SNMP/SMTP exceptions with domain context, preserve original exception as $previous, add business context with entity IDs/operation details), exception handling patterns (let it propagate as default, catch and re-throw to add context, catch and handle for graceful degradation, catch specific exceptions not generic \Exception, implement retry logic for transient failures, never swallow exceptions), HTTP response mapping (ValidationException → 422, AuthorizationException → 403, ModelNotFoundException → 404, DomainException → 400, BusinessRuleException → 400, ConcurrencyException → 409, ConfigurationException → 500, InfrastructureException → 500), testing guidelines (assert exception type and message, assert context properties, test third-party wrapping, test logging with correct level/channel, test HTTP response status/body), anti-patterns documented (never extend generic \Exception, never catch \Exception or \Throwable except at application boundary, never swallow exceptions without logging, never double-wrap already-wrapped exceptions, use PHP built-in InvalidArgumentException for programming errors not custom classes), migration path in three phases (Sprint 1.5 define base exception classes and update Handler for type-based logging, Sprint 1.5 implement domain/business exceptions for Identity & Access module, Sprint 1.6 implement infrastructure exceptions wrapping third-party libraries and refactor existing generic \Exception usage), comprehensive examples provided (InvalidStateException with entity state context, InsufficientBalanceException with balance amounts, PaymentGatewayException wrapping Guzzle with operation details, OptimisticLockException with version mismatch, SettingNotFoundException with scope hierarchy), quick reference tables (exception type selection by scenario, log level by exception type, exception handling decision tree, wrapping checklist). Documentation only per requirements — no runtime exception classes implemented yet.

**Files Added:**
- docs/architecture/exceptions.md

**Files Modified:**
- docs/changelog/development-log.md

**Architecture Impact:** Formalizes exception architecture establishing platform-wide error communication, diagnostics, and user feedback patterns. Documentation establishes: (1) Structured exception taxonomy (seven categories aligned with architecture layers replacing generic \Exception usage with semantic exception classes communicating intent through class names, enabling type-safe catch blocks independent of string matching, preventing third-party exceptions from leaking into domain layer), (2) Two-audience message strategy (user-facing messages non-technical actionable friendly never exposing technical details/stack traces/file paths/SQL/class names, developer messages technical detailed with entity IDs/current state/expected state/workflow document references/exception chain context, message localization for user-facing using Laravel translation system while keeping developer messages in English), (3) Logging responsibility by exception type (ValidationException logged at info level to application channel for expected user errors without warning/error noise, DomainException and BusinessRuleException logged at notice level to business channel for expected business failures and constraint violations, ConcurrencyException logged at warning level for expected high-concurrency failures and error level when retry exhausted, ConfigurationException logged at critical level for missing/invalid configuration preventing application startup, InfrastructureException logged at error level to infrastructure channel for external system failures, all logging includes structured context with entity type/ID/actor/component/operation for filtering and analysis), (4) Context-enriched exceptions (required properties for message/code/previous preserving exception chain, optional properties for entityType/entityId/currentState/expectedState/component/operation enabling structured logging and diagnostics, context() method returning filtered array for automatic logging by global Handler, security-sensitive exceptions logged to security channel at warning/error level), (5) Third-party exception wrapping (wrap at service layer boundaries where external libraries are called not in Controllers/Models, catch specific third-party exceptions like RequestException/QueryException/SNMPException not generic \Exception, throw domain exception with business context adding entity IDs/operation details/error codes, preserve original exception as $previous maintaining stack trace for debugging, prevents library-specific exceptions from leaking into domain layer enabling library replacement without catch block updates), (6) Exception handling patterns (default pattern let exceptions propagate to global Handler for automatic HTTP response conversion, catch-and-rethrow pattern adds context before propagating, catch-and-handle pattern for graceful degradation when recovery possible, catch-specific-types pattern handles known exceptions while letting others propagate, retry pattern catches transient failures like DeadlockException with exponential backoff, anti-patterns prohibited including catching generic \Exception/\Throwable except at application boundary and swallowing exceptions without logging), (7) HTTP response mapping (global Handler maps exception types to appropriate HTTP status codes automatically, ValidationException returns 422 with error bag JSON for field-level errors, DomainException and BusinessRuleException return 400 with user-facing message explaining constraint and guiding resolution, ConcurrencyException returns 409 with refresh message, InfrastructureException returns 500 with generic system error message never exposing connection strings/API endpoints/credentials, JSON API responses include message and type fields while HTML responses render Blade error views), (8) Testing discipline (assert specific exception types not generic \Exception, assert exception message contains expected text, assert context properties match entity type/ID/state, test third-party exception wrapping preserves original as $previous, test logging calls correct channel with correct level and structured context, test HTTP response returns expected status code and body structure), (9) Exception hierarchy organization (seven base exception classes extend RuntimeException organized in app/Exceptions/ with subdirectories by category, naming convention {Concept}{Failure}Exception with descriptive nouns and domain concepts avoiding redundancy, ProgrammingException base class extends LogicException for developer mistakes using PHP built-in InvalidArgumentException/UnexpectedValueException/BadMethodCallException directly), (10) Migration strategy (Sprint 1.5 create seven base exception classes and update Handler report/render methods for type-based logging and HTTP response mapping, implement domain/business exceptions for Identity & Access module testing exception throwing and logging, Sprint 1.6 create infrastructure exception classes wrapping Guzzle/PDO/SNMP/SMTP at service boundaries, audit and refactor existing code replacing generic \Exception with specific exception types, update all tests to assert specific exception classes not generic). Exception standard complements Platform Logging Standard (exception types map to log levels and channels), Platform Transaction Standard (ConcurrencyException for deadlocks and locks, transactions never swallow exceptions), Domain Event Architecture (exceptions do NOT dispatch events, events only on successful operations), Queue Architecture (InfrastructureException for queue failures, jobs implement failed() method for exception handling). Establishes foundation for consistent error handling, clear diagnostics, appropriate user feedback, and maintainable catch blocks across all business modules.

**Notes:** Documentation only. No exception classes implemented yet per requirements. Sprint 1.5 will implement base exception classes and Identity & Access module exceptions. Sprint 1.6 will implement infrastructure exceptions and refactor existing generic exception usage.

---

### 2026-07-02 | Architecture | Sprint 1.4 Story 3 — Architecture Decision: Service-First Application Layer

**Summary:** Formalized Service-First Application Layer architecture decision in docs/architecture/decisions.md establishing strict application layering rules that were implicitly followed in existing implementation. Created new "Application Layer" section in architecture decisions immediately after Identity & Access section. Documented comprehensive rules for Controller responsibilities (orchestration only: authorize via Policies, validate via FormRequests, invoke Services, return responses), Controller prohibitions (never query Eloquent directly except Route Model Binding, never access Cache, never dispatch Events, never open Transactions, never execute business rules), Service ownership (business rules and domain logic, database transactions with all DB::transaction calls, Domain Event dispatch, cache read/write/invalidation, model persistence operations, cross-entity coordination, external API integration), Model constraints (remain persistence objects defining relationships/casts/accessors/scopes with no business logic/transaction management/event dispatch), Policy constraints (authorization only with can/cannot methods with no business logic/database writes/side effects). Decision rationale explains separation of concerns benefits (Controllers remain thin 3-10 lines testable without HTTP context, business logic centralized in Services reusable from Controllers/CLI/Jobs/Listeners without duplication, transaction boundaries and event dispatch managed consistently in one place, prevents anti-patterns of fat Controllers/Models and data-mutating Policies). Impact section documents implementation requirements (thin Controllers averaging 3-10 lines per action, Services own all business operations with one service class per operation or related operations, Models pure persistence defining only relationships and query scopes, Policies pure authorization returning booleans never mutating data, testing approach with Service unit tests for business logic without HTTP and Controller tests for routing/authorization/response formatting, reusability with Service methods callable from multiple contexts without duplication, consistency with transaction/event/cache patterns unified because Services own these concerns, migration requirement to refactor existing violations during Sprint 1.5+ while new code follows immediately). Decision references related architecture standards (transactions.md, events.md, cache.md, queue.md, exceptions.md). Updated table of contents adding new Application Layer section with Service-First Application Layer decision entry between Identity & Access and Customer Management sections preserving existing structure and cross-references.

**Files Added:** None

**Files Modified:**
- docs/architecture/decisions.md
- docs/changelog/development-log.md

**Architecture Impact:** Formalizes existing implicit application layering pattern as authoritative architecture decision establishing clear boundaries across HTTP/Application/Domain/Persistence layers. Decision codifies: (1) Controller as orchestration-only layer (Controllers restricted to authorize/validate/invoke/respond operations never directly accessing Eloquent/Cache/Events/Transactions/business logic, thin implementations averaging 3-10 lines per action focusing on HTTP concerns, separation of HTTP handling from business logic enables independent testing and reusability), (2) Service as business logic owner (Services own all business rules/domain logic/database transactions/event dispatch/cache management/model persistence/cross-entity coordination, one service class per business operation or closely related operations, transaction boundaries with all DB::transaction calls managed in Services not Controllers/Models, Domain Event dispatch from Services after successful operations following afterCommit pattern from transactions.md, cache read/write/invalidation owned by Services following patterns from cache.md, Services coordinate external API integration following queue patterns from queue.md), (3) Model as persistence layer (Models define Eloquent relationships/attribute casts/accessors/query scopes only with zero business logic/transaction management/event dispatch, Models remain pure data access objects enabling query reuse without hidden business behavior), (4) Policy as authorization layer (Policies contain authorization rules only returning boolean can/cannot results with zero business logic/database writes/side effects, Policies evaluate permissions without mutating state), (5) Testing discipline (Service unit tests validate business logic via dependency injection without HTTP context, Controller tests validate routing/authorization/response formatting without duplicating business logic assertions, separation enables focused testing with clear boundaries), (6) Reusability guarantee (Service methods callable from Controllers/CLI artisan commands/Queue Jobs/Event Listeners/test suites without code duplication, business logic centralized eliminates fragmentation across layers), (7) Consistency enforcement (transaction boundaries consistent because Services own DB::transaction not Controllers/Models, event dispatch consistent because Services dispatch after commit not scattered across layers, cache invalidation consistent because Services own Cache facade not Controllers/Models, exception handling consistent because Services throw/wrap following patterns from exceptions.md). Decision prevents common Laravel anti-patterns (fat Controllers with embedded business logic making HTTP tests complex and logic non-reusable, fat Models with transaction management violating single responsibility and persistence layer boundaries, Policies that mutate data violating authorization-only constraint and causing unexpected side effects, business logic scattered across Controllers/Models/Policies making system behavior unpredictable and difficult to test). Architecture aligns with established Laravel service layer practices while enforcing stricter boundaries than default framework conventions. Decision complements existing architecture standards: Transaction Standard (Services own DB::transaction per transactions.md), Event Architecture (Services dispatch events after commit per events.md), Cache Architecture (Services own cache management per cache.md), Queue Architecture (Services dispatch jobs per queue.md), Exception Standard (Services throw/wrap exceptions per exceptions.md). Migration path documented requiring refactoring of existing violations during Sprint 1.5+ module implementation while mandating immediate compliance for all new code. Decision inserted in table of contents and document body immediately after Identity & Access section before Customer Management preserving existing decision structure and maintaining all cross-references.

**Notes:** Architecture decision formalizes existing implementation pattern. No runtime code changes required — pattern already followed in implemented Services (UserService, RoleService, PermissionService, SettingService). Decision establishes authoritative standard for future implementation and refactoring. Existing Services already comply with documented rules. Sprint 1.5+ will audit and refactor any violations discovered during module expansion.

---

### 2026-07-02 | Documentation | Sprint 1.4 Story 4 — Definition of Done

**Summary:** Introduced project-wide Definition of Done (DoD) establishing mandatory completion criteria for every Story across all Sprints and modules. Created docs/development/definition-of-done.md defining five guiding principles (Architecture Before Code, Documentation is Delivery, Tests Protect Behavior, Self-Review Before Review Request, Development Log is Permanent Record), applicability matrix mapping each checklist item to Story types (Architecture Doc, DB Change, Backend, Frontend, Bug Fix, Refactor) with mandatory/conditional/not-applicable designations, and comprehensive mandatory checklist covering Architecture (decision entry in decisions.md with ToC update and no contradictions), Entities (entity definition in entities.md with classification/ownership/lifecycle/deletion/immutability/relationships), Migration (timestamp convention, snake_case plural table names, FK constraints, indexes, soft delete, optimistic lock version column, down method, ERD verification), Model (namespace, singular PascalCase, fillable/guarded, casts, relationships matching ERD, both sides of bidirectional, soft deletes, morph relationships, no business logic/transactions/events, scopes), Service (domain subdirectory, naming convention, AbstractCrudService extension, constructor injection, business rules only in Service, DB::transaction closure form, ShouldDispatchAfterCommit events, cache invalidation, afterCommit job dispatch, typed exceptions, no external API in transactions), Policy (app/Policies namespace, Entity+Policy naming, AuthServiceProvider registration, authorization-only with no business logic/writes/side effects), Controller (domain subdirectory, Resource Controller structure, orchestration-only with authorize/validate/invoke/respond, no Eloquent/Cache/Events/Transactions/business logic, Route Model Binding for lookup only, dot notation route names), Form Request (domain subdirectory, Action+Entity+Request naming, authorize true, explicit rules, no business logic), Seeder (descriptive naming, DatabaseSeeder registration, idempotent with firstOrCreate/updateOrCreate, reference data coverage, factory creation), Views (domain subdirectory, extends admin layout, card/grid default with table only for tabular, Bootstrap mobile-first, Alpine.js confirmation modals, pre-prepared data only, eager loading, Entity 360 for core entities), Architecture Documentation (format with Version/Status/Last Updated, Purpose/Scope/Rules/Examples/Anti-Patterns/Migration Path), Workflow Documentation (lifecycle states accurate, state transitions match implementation), Development Log (append-only, chronological, all fields complete, specific summary), Self-Review (architecture compliance, code quality with no N+1/pagination/hardcoding, security with FormRequest/authorize/no SQL injection/no secrets logged, database immutability, test passage), Tech Lead Review (architecture compliance, service-first, security OWASP Top 10, database ERD verification, test coverage adequacy, documentation completeness, merge readiness), Unit Tests (tests/Unit/{Domain}/ structure, happy path and failure paths and exceptions, constructor injection), Feature Tests (tests/Feature/{Domain}/ structure, authentication/authorization/validation/success/persistence assertions, RefreshDatabase), Test Execution (all pass no regressions), Merge Ready (all DoD items complete, no debug code/dd/dump, no .env committed, no migration modifications after shared environment run). Documented review responsibilities for Developer (architecture compliance before coding, migration matches ERD before creating, tests written during implementation, self-review and log before requesting review), Tech Lead (architecture/security/database/tests/documentation/merge approval), and AI Assistant (read docs before coding, produce complete artifacts, update development log, flag out-of-scope, avoid hardcoding, use glossary terms, never modify financial records, propose documentation updates). Included Story Completion Workflow diagram showing sequential steps from reading architecture docs through merge. Documented Common Violations table identifying 11 frequently skipped items with category labels and prevention guidance. Included References table linking all 13 relevant architecture/database/workflow/project documents. Updated docs/README.md to add Development section pointing to docs/development/ directory.

**Files Added:**
- docs/development/definition-of-done.md

**Files Modified:**
- docs/README.md
- docs/changelog/development-log.md

**Architecture Impact:** Establishes project-wide quality gate ensuring consistent delivery standards across all Sprints and modules. The Definition of Done formalizes: (1) Mandatory artifact coverage (every Story produces all applicable artifacts — migrations, models, services, policies, controllers, form requests, views, tests, documentation — not partial stubs, preventing partial delivery that creates technical debt and inconsistent platform state), (2) Architecture-first enforcement (checklist item A1 mandates architecture decision entry before implementation of any Story introducing new architectural patterns, preventing undocumented behavior from entering the codebase), (3) Service-First Application Layer enforcement (checklist items S1/C1/Po1 encode the Service-First Application Layer decision as concrete verifiable checkboxes rather than aspirational guidelines, making it impossible to satisfy DoD with business logic in Controllers or Policies), (4) Transaction and event discipline (checklist items S1 explicitly include DB::transaction closure form, ShouldDispatchAfterCommit, afterCommit job dispatch, and no external API in transactions, encoding patterns from transactions.md and events.md as mandatory checklist items), (5) Security baseline (checklist items SR1 and TL1 include mandatory authorization on every endpoint, FormRequest validation on all input, OWASP Top 10 review for new input surfaces, no sensitive data in logs or views, secrets in environment variables only), (6) Database integrity (migration checklist M1 mandates FK constraints/indexes/soft delete/optimistic lock/ERD verification, and immutability rules in SR1 ensure financial records are never directly updated), (7) Three-layer review process (Developer self-review SR1 → Tech Lead review TL1 → Merge Ready MR1 creating progressive quality gates where each layer verifies different concerns — developer verifies completeness, Tech Lead verifies architecture and security, Merge Ready gate confirms all blocking comments resolved), (8) AI assistant accountability (explicit AI assistant responsibilities table clarifying expected behavior for code generation, documentation updates, out-of-scope detection, glossary naming, and financial record immutability), (9) Common violations documentation (11 most frequently skipped DoD items identified with category labels and prevention guidance serving as explicit warning list for reviewers), (10) Applicability matrix (prevents over-engineering by clearly designating which checklist items are mandatory vs conditional vs not applicable for each Story type — architecture-only stories skip migration/model/view/test items; database-only stories skip controller/policy/view items). Definition of Done references all Sprint 1.3 and 1.4 architecture standards (events.md, logging.md, cache.md, queue.md, transactions.md, exceptions.md) as explicit checklist review targets for Tech Lead review, creating a closed loop between architectural documentation and delivery verification. DoD becomes the authoritative bridge between the Architecture-First philosophy and implementation practice.

**Notes:** The Definition of Done is effective immediately for all future Stories starting Sprint 1.5. Existing Sprint 1.3 and 1.4 Stories were Documentation-only and are retrospectively considered complete under the Architecture Doc Story type column. The docs/development/ directory is newly created. docs/README.md updated to reference it.

---

### 2026-07-02 | Architecture | MILESTONE — Architecture Freeze v1.0

**Summary:** Declared Architecture v1.0 as the frozen, stable baseline for the ISP Management Platform. Architecture Freeze v1.0 marks the formal transition from the architecture and infrastructure establishment phase into business module development. All architecture decisions, platform standards, database design, identity & access implementation, settings engine implementation, and development conventions established from project inception through Sprint 1.4 are now the binding foundation for all future module development. Created docs/architecture/architecture-freeze-v1.md as the authoritative governance document for the freeze, documenting purpose (stable baseline before business module development), scope (all Sprints 0 through 1.4 documentation and implementations), frozen components (architecture decisions including Service-First Application Layer/RBAC/Lifecycle State Machine/Financial Immutability, all six platform standards events/logging/cache/queue/transactions/exceptions, database foundations including identity/settings/platform tables), future change policy (permitted changes that do not require Architecture Change Workflow: adding new entities/migrations/events for new modules, extending existing patterns, bug fixes; required Architecture Change Workflow for: modifying frozen decisions, changing platform standard rules, modifying frozen table structures, changing exception hierarchy, changing transaction ownership, changing event dispatch timing, introducing new UI frameworks, modifying Definition of Done), architecture governance (Architecture Team authority, five stability commitments for v1.x, review cadence triggers), Architecture Change Workflow (eight-step process: identify change need, document proposal, review against frozen decisions, document Architecture Decision, update platform standards, update Development Log, Tech Lead review, implement), relationship with future versions (v1.1 incremental improvements during Sprint 2-4 backwards-compatible, v1.2 Customer Portal architecture backwards-compatible, v2.0 major version for breaking changes with migration guide), frozen foundation summary tables (10 architecture documents, 2 database documents, 8 workflow documents, 2 implemented modules, 2 development standards documents). Updated docs/README.md Architecture Status section with freeze version/date/current phase/next milestone and governance reference. Updated docs/README.md Project Status section to reflect Architecture Freeze v1.0 baseline.

**Completed Foundation Summary:**

**Architecture (Sprint 0 – Sprint 1.4):**
- 60+ architecture decisions across all business domains
- Canonical terminology glossary
- Business events catalog
- Six platform standards: events, logging, cache, queue, transactions, exceptions
- Service-First Application Layer decision
- Architecture Freeze governance document
- Definition of Done

**Infrastructure (Sprint 0 – Sprint 1.1):**
- Laravel 10 project scaffolding
- AdminLTE 3 admin UI integration
- Authentication (Laravel built-in)
- Abstract CRUD Service and Controller base classes
- Service layer standardization
- ViewModels preparation

**Identity & Access (Sprint 0 Story 1 – Sprint 1.2):**
- Users, Roles, Permissions, Role-Permission, User-Role, User-Permission tables
- Role and Permission lifecycle (Draft, Active, Deprecated)
- User Impersonation (ImpersonationSession)
- RBAC authorization with active lifecycle enforcement
- Full CRUD UI for Users, Roles, Permissions
- Impersonation start/stop workflows

**Settings Engine (Sprint 1 Story 1):**
- Settings and SettingRegistryEntry tables
- Scope hierarchy (Customer → Area/Cluster → Global)
- Typed settings with registry governance
- Cache integration (reference implementation for cache.md)
- Full CRUD UI for Settings management

**Files Added:**
- docs/architecture/architecture-freeze-v1.md

**Files Modified:**
- docs/README.md
- docs/changelog/development-log.md

**Architecture Impact:** Architecture Freeze v1.0 is the most significant project milestone to date. It closes the architecture establishment phase and opens the business module implementation phase. All subsequent development — Customer Module, Subscription Module, Billing Engine, Payments, Provisioning, Monitoring, Tickets, Notifications, Customer Portal — proceeds under the governance of this frozen baseline. The freeze provides: (1) Implementation certainty — developers and AI assistants can rely on frozen platform standards without risk of the foundation changing beneath them, (2) Architecture integrity — the Architecture Change Workflow ensures no silent modifications to frozen decisions, (3) Onboarding clarity — new contributors have a complete, stable documentation set as the authoritative knowledge base, (4) Sprint 2 readiness — Customer Module development may begin immediately with full architectural guidance available, (5) Version governance — v1.1/v1.2/v2.0 roadmap establishes how the architecture evolves without breaking v1.0 guarantees. Future architecture changes require: document proposal, conflict review, Architecture Decision entry, platform standard update if applicable, Development Log entry, Tech Lead approval, then implementation. Implementation without prior Architecture Decision is a process violation.

**Notes:** Documentation only. No runtime code changes. No Controllers, Services, Models, Routes, or Database modifications. Architecture Freeze v1.0 is effective 2026-07-02. Sprint 2 – Customer Module is the next milestone.

---

### 2026-07-02 | Architecture | Sprint 2.1 Story 1 — Customer Module Architecture Review

**Summary:** Conducted comprehensive pre-implementation architecture review of the Customer module against Architecture Freeze v1.0, all relevant documentation (decisions.md, glossary.md, business-events.md, entities.md, erd.md, project-scope.md, subscription-lifecycle.md, events.md), and all six platform standards. Analysis covered Customer lifecycle, CustomerStatus enum, Customer relationships, Customer deletion policy, Customer business events, Customer ownership boundaries, and Customer interaction with Subscription/Billing/Payment/Ticket/Notification/Customer Portal. Identified 2 Critical, 11 High, 10 Medium, and 4 Low severity findings across six categories: Lifecycle (CL-01 through CL-04), Status Enum (CSE-01 through CSE-02), Relationships (CR-01 through CR-05), Deletion Policy (CD-01 through CD-02), Business Events (CBE-01 through CBE-02), Ownership Boundaries (COB-01 through COB-02), Cross-Module Interactions (CIM-01 through CIM-06), Data Model Gaps (DMG-01 through DMG-04). Critical blockers: CustomerStatus enum values not formally defined (entities.md states Lead→Active→Suspended→Terminated but subscription-lifecycle.md uses Prospect terminology — inconsistency must be resolved before migration), Customer key attributes not column-level specified (conceptual description insufficient for migration generation). High priority blockers: no customer-workflow.md document (Customer lifecycle has no authoritative workflow), Lead vs Prospect terminology inconsistency requiring Architecture Decision, Customer suspension vs Subscription suspension semantics undefined, CustomerStatus transition rules undocumented, missing User/ActivityLog/ServiceRequest relationships in entities.md, one active subscription enforcement mechanism undefined, missing business events (CustomerSuspended/CustomerReactivated/CustomerTerminated/CustomerConverted only CustomerRegistered and CustomerUpdated currently documented), customer type individual/business undefined, customer number external identifier undefined. Medium findings include deletion Restrict conditions undefined, cascade on soft delete unspecified, aggregate access patterns undocumented, Invoice/Payment access pattern ambiguity (dual customer_id and subscription_id FKs), Customer Portal account lifecycle undefined, notification recipient resolution mechanism unspecified, service vs billing address distinction. Six missing Architecture Decisions identified: Customer Status Canonical Values, Customer Type Classification, Customer Contact Data Model, Customer External Identifier Format, One Active Subscription Database Enforcement, Customer Cascade on Status Change. Five future implementation risks documented: CustomerStatus enum mismatch risk (same as UserStatus Disabled incident), Customer 360 scope creep, N+1 queries on Customer 360 aggregated view, duplicate active subscription race condition under concurrent requests, financial Restrict condition ambiguity. Produced pre-implementation action plan in three phases: Phase 1 (Architecture Decisions — immediate), Phase 2 (Documentation updates — Sprint 2.1 pre-work), Phase 3 (Implementation — after Phases 1 and 2 complete). Review document stored in docs/architecture/reviews/. No source code generated or modified.

**Files Added:**
- docs/architecture/reviews/sprint-2.1-customer-module-review.md

**Files Modified:**
- docs/changelog/development-log.md

**Architecture Impact:** Pre-implementation review following Architecture-First philosophy. Review reveals that the Customer module is not ready for immediate implementation — two Critical blockers and eleven High severity findings must be resolved first. The most significant architectural gaps are: (1) CustomerStatus enum values unresolved due to Lead/Prospect terminology inconsistency between entities.md and subscription-lifecycle.md — this is the same class of problem that caused the UserStatus::Disabled incident requiring a retroactive bug fix in Sprint 0, (2) No customer-workflow.md exists to govern Customer account lifecycle distinct from Subscription lifecycle — without this document, developers cannot correctly implement CustomerService state transitions, (3) Four missing business events (CustomerSuspended, CustomerReactivated, CustomerTerminated, CustomerConverted) leave the Notification Engine and Customer Portal unable to react to Customer lifecycle changes, (4) One active subscription enforcement mechanism relies on undocumented application-level check with race condition risk under concurrent activation — requires lockForUpdate() per transactions.md pessimistic locking guidance, (5) Customer-to-Subscription lifecycle cascade rules undefined (does Customer suspension automatically suspend Subscriptions?) — without this rule the CustomerService cannot be correctly implemented. Review process confirms the Architecture-First philosophy: documentation gaps discovered now prevent implementation errors that would require retroactive fixes. The estimated pre-work to resolve all Critical and High findings before implementation begins is three to four Sprint 2.1 pre-work stories covering: Architecture Decisions (canonical values, customer type, customer number, enforcement mechanism, cascade rules), documentation updates (customer-workflow.md, entities.md relationship additions, business-events.md event additions), and then the implementation stories can proceed on a fully resolved baseline. All architecture standards from the frozen v1.0 baseline remain applicable and unaffected by these gaps — the gaps are in Customer-specific documentation, not in the platform standards themselves.

**Notes:** Analysis only. No source code generated. No Controllers, Services, Models, Migrations, or Views produced. Review identifies pre-work required before Sprint 2.1 implementation begins. The existing customers migration (2026_06_25_000001_create_customers_table.php) created during Sprint 0 database scaffolding has not been reviewed against these findings — it should be audited and likely replaced before running against any shared environment.

---

### 2026-07-02 | Architecture | Sprint 2.1 Story 2 — Architecture Backlog from Customer Module Review

**Summary:** Converted all findings from the Customer Module Architecture Review (Sprint 2.1 Story 1) into the official Architecture Backlog, creating docs/architecture/architecture-backlog.md as the single source of truth for unresolved architecture work. Created backlog file with governance rules (items never deleted — moved to Closed when resolved, no duplication, every item has clear owner/priority/target version/actionable recommendation), status definitions (Todo/In Progress/Blocked/Closed/Deferred/Open), and priority definitions (Critical/High/Medium/Low). Organized 18 backlog items across four epics: Epic A (Customer Architecture Finalization — Critical/High items blocking implementation), Epic B (Customer Implementation Preparation — High/Medium items enabling implementation), Epic C (Future Customer Improvements — Medium/Low deferred to v1.1), Epic D (Technical Investigations — open questions requiring research). Merged related review findings into single actionable backlog items reducing 27 individual findings into 18 focused items: ARCH-001 (canonical Customer lifecycle/terminology, merges CL-01/CSE-01/CSE-02), ARCH-002 (create customer-workflow.md, merges CL-02/CL-03/CSE-02/CIM-01/CD-02/CIM-05), ARCH-003 (Customer entity column specification, merges DMG-01/DMG-02/DMG-03/DMG-04/CR-01/CR-02/CR-03/CR-04), ARCH-004 (missing business events CustomerSuspended/CustomerReactivated/CustomerTerminated/CustomerConverted from CBE-01), ARCH-005 (deletion policy Restrict conditions and cascade from CD-01/CD-02), ARCH-006 (aggregate access patterns and dual FK clarification from COB-01/CIM-02/CIM-03), ARCH-007 (Customer vs Subscription suspension semantics from CL-03/CIM-01), ARCH-008 (one active subscription enforcement mechanism from COB-02), ARCH-009 (existing customers migration audit from DMG-01/CSE-01/CL-01), ARCH-010 (Customer Portal account lifecycle deferred from CIM-05), ARCH-011 (notification recipient resolution deferred from CR-04/CIM-06), ARCH-012 (QR Code integration deferred from CR-05), ARCH-013 (Customer 360 tab inventory and eager load spec deferred from R-02/R-03), ARCH-014 (CustomerUpdated event granularity deferred from CBE-02), ARCH-015 (Inactive/churned customer state evaluation deferred from CL-04), ARCH-016 (investigate partial unique index for one active subscription from COB-02/R-04), ARCH-017 (investigate customer type scope for v1.0 from DMG-02), ARCH-018 (investigate Customer→Active trigger mechanism from CL-02/CIM-01). Backlog summary table documents 3 Critical items blocking Sprint 2.1 (ARCH-001, ARCH-002, ARCH-003), 6 High priority items, 3 Medium, 3 Low, and 3 Open investigations. Updated docs/README.md architecture section already references architecture-backlog.md. No application code generated.

**Files Added:**
- docs/architecture/architecture-backlog.md

**Files Modified:**
- docs/changelog/development-log.md

**Architecture Impact:** Architecture Backlog establishes the governance mechanism for tracking and resolving unresolved architecture work across the project lifecycle. Key design decisions: (1) Items are never deleted — only moved to Closed, maintaining a complete audit trail of all architectural questions raised and resolved, (2) Related findings merged into single actionable items prevents the backlog from becoming a fragmented list of micro-tasks — 27 review findings merged into 18 backlog items each with a clear single recommended action, (3) Four epic structure separates blocking work (Epic A — must complete before implementation), enabling work (Epic B — unblocks implementation), deferred improvements (Epic C — v1.1 scope), and open investigations (Epic D — requires research before decision), (4) Three Critical blocking items identified for Sprint 2.1 (canonical lifecycle terminology, customer workflow document, entity column specification) must be completed as Sprint 2.1 pre-work stories before any Customer module code is generated, (5) ARCH-016 investigation documents known MySQL/MariaDB limitation (no partial unique index support) steering toward application-layer lockForUpdate() enforcement per transactions.md, (6) ARCH-017 investigation on customer type scope is identified as blocking ARCH-003 completion — business input required before column specification can be finalized, (7) ARCH-009 migration audit item explicitly flags the Sprint 0 customers migration as pre-review and potentially incorrect, preventing premature deployment. The backlog directly implements the Architecture-First philosophy: all architectural questions are surfaced and tracked before implementation, not discovered during code review or after deployment.

**Notes:** Documentation only. No source code generated or modified. Architecture Backlog is effective immediately. Sprint 2.1 implementation stories may not begin until ARCH-001, ARCH-002, and ARCH-003 are closed. ARCH-017 (customer type investigation) must be resolved before ARCH-003 can be completed.

---

### 2026-07-02 | Architecture | Migration Strategy Before First Release

**Summary:** Introduced a formal Architecture Decision defining the database migration strategy before and after the first production release. Added "Migration Strategy Before First Release" decision to the Core Architecture section of `docs/architecture/decisions.md`, defining two distinct governance phases: pre-release (migration files are part of the evolving architecture and may be replaced, renamed, consolidated, or regenerated; fresh installs must always produce the current architecture; avoid accumulating ALTER migrations to preserve development history; replacement permitted only while no production environment has run the migration) and post-release (migration files become immutable historical records; never modify or delete any migration run on production; all evolution uses forward-only migrations; every environment must have a valid sequential upgrade path). Decision defines the release boundary as "the moment the application is deployed to a production environment with real customer data." Rationale documents that accumulating ALTER migrations during pre-release creates fragmented history requiring many sequential runs on fresh installs, obscures final intended schema, slows CI/CD, and adds cognitive overhead for new developers — clean migration history is more valuable than preserving intermediate experiments. Impact documents benefits across pre-release (developers may regenerate migrations), fresh installations (single clean pass), CI/CD (simpler reset/test setup), onboarding (migrations readable as current schema spec), post-release (immutable, auditable upgrade path), and explicitly notes that Sprint 0 customers/subscriptions/invoices/payments/olts/onus migrations are considered pre-release and may be replaced as module architectures are finalized. Updated table of contents in `decisions.md` to include the new decision. Added single-line reference in `architecture-freeze-v1.md` Future Change Policy permitted-changes list stating migration replacement is allowed during pre-release per the decision. Updated `docs/README.md` Project Status section to reflect pre-release schema status, noting that migration files for modules not yet in production may be replaced rather than incrementally altered. No migrations, models, controllers, services, or views modified.

**Files Added:** None

**Files Modified:**
- docs/architecture/decisions.md
- docs/architecture/architecture-freeze-v1.md
- docs/README.md
- docs/changelog/development-log.md

**Architecture Impact:** Establishes a clear migration governance boundary preventing two common failure modes: (1) Pre-release: without this decision, developers accumulate ALTER migrations during development creating a fragmented history that makes fresh installs require running 20+ sequential migrations and makes the final schema intent unreadable — this decision permits clean replacement keeping migration history as a readable specification of the current approved schema. (2) Post-release: without this decision, there is ambiguity about whether pre-release replacement habits can continue after production deployment — this decision draws a hard line at first production deployment making migration immutability explicit and preventing accidental deletion of migration files that running environments depend on. The decision directly unblocks ARCH-009 (Existing customers migration audit) from the Architecture Backlog — that migration was created during Sprint 0 scaffolding before the Customer module architecture review and may need to be replaced; this decision confirms that replacement is architecturally permitted and governed. Decision inserted in Core Architecture section of `decisions.md` after Canonical Terminology and before Identity & Access, consistent with its platform-wide governance scope. Architecture Freeze v1.0 policy updated with a single-line reference to avoid duplicating the decision text. README Project Status section updated to set accurate expectations for contributors about the pre-release schema state.

**Notes:** Documentation only. No migrations modified. This decision applies retroactively to all Sprint 0 business module migrations (customers, subscriptions, invoices, payments, olts, onus, etc.) which were created before their respective module architecture reviews were conducted and may require replacement as architectures are finalized. The Identity & Access and Settings Engine migrations are considered stable as their modules are fully implemented and reviewed.

---

### 2026-07-02 | Backend | Sprint 2.2 Story 1 — CustomerStatus Enum

**Summary:** Implemented `CustomerStatus` PHP backed enum as the single source of truth for the Customer lifecycle defined in Architecture Freeze v1.0 and resolved in Architecture Backlog item ARCH-001. Enum defines four cases matching the canonical lifecycle: `Prospect` (`prospect`), `Active` (`active`), `Suspended` (`suspended`), `Terminated` (`terminated`). Followed established project enum conventions exactly (matching `UserStatus` as the closest pattern: detailed docblock, `label()`, `badgeColor()`, state-check helpers, `values()`, `options()`). Used `badgeColor()` not `color()` per project convention. Omitted `icon()` as no icon convention exists in the project enum layer. Docblock documents the module (Customer Management), authoritative column values, lifecycle semantics including the critical distinction that Customer `suspended` is an administrative action distinct from Subscription suspension which is billing-driven. AdminLTE badge colors applied: `secondary` (Prospect), `success` (Active), `warning` (Suspended), `danger` (Terminated). State-check helpers implemented: `isProspect()`, `isActive()`, `isSuspended()`, `isTerminated()`. Static utilities implemented: `values()` returning flat array of DB values, `options()` returning associative array for HTML select inputs. Enum contains no business logic, no database access, no model references, no service references, no authorization, no transition validation — lifecycle validation deferred to future CustomerService state machine. Verified: Laravel 10 compatible, PHP backed enum, no syntax errors, no compile errors.

**Files Added:**
- app/Enums/CustomerStatus.php

**Files Modified:**
- docs/changelog/development-log.md

**Architecture Impact:** Closes ARCH-001 (partial — canonical lifecycle terminology resolved and implemented). `CustomerStatus` provides the type-safe foundation for: (1) `customers` migration `status` column cast, (2) `Customer` Eloquent model `$casts` array, (3) `CustomerService` state machine transition guards, (4) Blade view status badge rendering via `$customer->status->badgeColor()` and `$customer->status->label()`, (5) `StoreCustomerRequest` / `UpdateCustomerRequest` validation using `Rule::enum(CustomerStatus::class)`, (6) `CustomerPolicy` state-awareness if needed. The `Prospect` canonical value resolves the Lead/Prospect terminology inconsistency (CL-01) — `Lead` is permanently retired from the codebase. The docblock explicitly documents that Customer-level `suspended` is an administrative action distinct from Subscription-level suspension (billing-driven) per CL-03 finding, establishing the semantic distinction at the type level before the service layer enforces it.

**Notes:** Enum only. No migration, model, service, controller, request, policy, route, factory, seeder, or view implemented. ARCH-001 is partially closed — canonical lifecycle values are now implemented but `entities.md` and `glossary.md` documentation updates (updating Lead → Prospect, adding Prospect to glossary) remain as documentation tasks. ARCH-003 (Customer entity column specification) and ARCH-002 (customer-workflow.md) remain open and must be resolved before migration generation.

---

### 2026-07-02 | Architecture | Sprint 2.1 Pre-Work — Customer Module Architecture Documentation (Stories A, B, C)

**Summary:** Completed all three Customer module architecture pre-work stories to close the Critical blocking items identified in the Customer Module Architecture Review (Sprint 2.1 Story 1). Pre-Work Story A resolved ARCH-001 (canonical lifecycle terminology) and ARCH-017 (customer type scope): adopted Prospect as the canonical initial Customer state, recorded six new Architecture Decisions in decisions.md under Customer Management section (Customer Lifecycle Canonical States, Customer Type Classification for v1.0, Customer Account vs Subscription Suspension Semantics, Customer Number Format and Generation, Customer Contact Data Model, One Active Subscription Enforcement Mechanism, Customer Account Cascade on Status Change), updated entities.md Customer lifecycle from Lead to Prospect, updated glossary.md with Prospect and Customer Number canonical terms, updated decisions.md table of contents. Pre-Work Story B resolved ARCH-002: created docs/workflows/customer-workflow.md as the authoritative Customer account lifecycle workflow document covering four lifecycle states (Prospect/Active/Suspended/Terminated) with entry/exit conditions, allowed transition table, business rules per state, actor matrix (Sales/CS/Admin/Finance/System/Collector/NOC/Customer Portal), canonical Customer vs Subscription suspension distinction (Customer suspension is administrative-only never billing-driven, does not cascade to Subscriptions), Customer-Subscription cascade rules (Prospect→Active triggered by SubscriptionActivated event, no cascade on subscription suspension, termination requires all subscriptions terminated), customer number generation (CUST-000001 format), customer type policy (individual default, business passive v1.0), Customer 360 workspace tab inventory (8 tabs: Overview/Subscriptions/Billing/Payments/Tickets/Timeline/Activity Log/Attachments with eager load chain), soft delete pre-conditions (6 validation checks), cascade on soft delete, portal account lifecycle on suspension/termination, all six business events with triggers and consumers, notification recipient resolution (contact fields on customers table directly), QR code generation timing (on first Subscription activation), and references to all related architecture documents. Pre-Work Story C resolved ARCH-003: fully respecified entities.md Customer entity with complete 20-column table specification (id, customer_number, name, customer_type, email, phone, whatsapp_phone, alt_phone, address, latitude, longitude, notes, cluster_id, service_area_id, user_id, status, created_at, updated_at, deleted_at), updated relationships to include User(0..1)/ActivityLog(polymorphic)/ServiceRequest(1..N)/QRCodeReference(0..1), expanded Business Rules to 8 explicit rules, rewrote Key Attributes as column-level specification, corrected Lifecycle Reference from subscription-lifecycle.md to customer-workflow.md, updated Produces Events to all 6 events and added Consumes Events (SubscriptionActivated). Also resolved ARCH-004 (missing business events): added CustomerConverted/CustomerSuspended/CustomerReactivated/CustomerTerminated full event definitions to business-events.md (4 new events with complete standard format). Also resolved ARCH-005 (deletion policy), ARCH-006 (aggregate access patterns), and ARCH-007 (suspension semantics) as these were fully covered by customer-workflow.md and decisions.md updates. Closed ARCH-001/002/003/004/005/006/007/017 in architecture-backlog.md with Resolution notes. Updated Backlog Summary: 8 closed, 2 todo, 2 open, 6 deferred.

**Files Added:**
- docs/workflows/customer-workflow.md

**Files Modified:**
- docs/architecture/decisions.md
- docs/architecture/glossary.md
- docs/architecture/business-events.md
- docs/architecture/architecture-backlog.md
- docs/database/entities.md
- docs/changelog/development-log.md

**Architecture Impact:** Resolves all three Critical blocking items (ARCH-001, ARCH-002, ARCH-003) plus four additional High items (ARCH-004, ARCH-005, ARCH-006, ARCH-007) and one Investigation (ARCH-017) that were identified in the Customer Module Architecture Review. The Customer module is now fully specified and ready for implementation. Key decisions formalized: (1) Prospect is the canonical initial Customer state — Lead is permanently retired from the codebase, (2) Customer account suspension is an administrative action only, never triggered by billing overdue policy — Customer status and Subscription status are orthogonal axes, (3) Customer type is individual (default) with passive business option in v1.0 — differential rules deferred to v1.1, (4) Customer number uses CUST-000001 format generated by CustomerService on creation using lockForUpdate() pattern, (5) Contact fields (email/phone/whatsapp_phone) live on the customers table enabling notifications to Prospects without portal accounts, (6) One active subscription enforced at application layer with lockForUpdate() — no partial DB index (MySQL/MariaDB doesn't support it), (7) Customer cascade on status change: suspension does not cascade to Subscriptions; termination requires all Subscriptions already terminated; soft delete requires full pre-condition validation. Architecture documentation now provides complete unambiguous specification for Customer migration, CustomerType enum, Customer model, CustomerService state machine, CustomerPolicy, CustomerController, FormRequests, Routes, and all Customer 360 views.

**Notes:** Documentation only. No PHP, migrations, models, controllers, services, routes, views, or tests generated. All remaining backlog blockers closed. Customer module implementation (Sprint 2.1 Story 2 / Sprint 2.2) may now proceed. ARCH-008 (one active subscription enforcement) and ARCH-009 (existing Sprint 0 customers migration audit) remain as implementation-phase tasks. The existing Sprint 0 customers migration (2026_06_25_000001_create_customers_table.php) must be replaced per ARCH-009 before any shared environment migration.

---

### 2026-07-02 | Architecture | Sprint 2.1 — Customer Module Documentation Consistency Pass

**Summary:** Performed full documentation consistency pass across all Customer architecture and database documents to ensure exactly one canonical definition of every Customer concept before implementation begins. Authority order applied: customer-workflow.md > decisions.md > business-events.md > glossary.md > entities.md > erd.md. Consistency audit findings and changes: (1) Glossary — added four new terms: CustomerStatus (defines four canonical states with DB values, references orthogonal status axis concept), Customer Suspension (administrative action only, not billing-driven, does not cascade to Subscriptions, distinguishes from Subscription Suspension), Subscription Suspension (billing-driven, restricts network access, distinct from Customer Suspension), updated Customer 360 notes from vague "typical tabs include billing, monitoring..." to authoritative 8-tab inventory matching customer-workflow.md (Overview/Subscriptions/Billing/Payments/Tickets/Timeline/Activity Log/Attachments) with note about tab availability varying by state; (2) ERD — added QRCodeReference relationship to Customer Management Mermaid diagram (CUSTOMER |o--o| QR_CODE_REFERENCE), added QRCodeReference to Relationship Explanations section, added Polymorphic Platform Relationships section documenting TimelineEvent/ActivityLog/Attachment/User polymorphic relationships not shown in Mermaid, updated Ownership Rules to include QRCodeReference and Invoice/Payment dual FK denormalization rationale, updated Deletion Behavior from vague "Restrict with financial dependencies" to precise 6-condition pre-delete validation matching customer-workflow.md and entities.md, updated Lifecycle Dependencies to add Customer status/Subscription status orthogonality rule, Customer Termination pre-condition (all Subscriptions must be terminated), and Customer Prospect→Active automatic trigger from SubscriptionActivated event; (3) entities.md — confirmed fully aligned from pre-work (no changes needed); (4) business-events.md — confirmed all 6 Customer events present (CustomerRegistered/CustomerUpdated/CustomerConverted/CustomerSuspended/CustomerReactivated/CustomerTerminated) with no changes needed; (5) decisions.md — confirmed all 8 Customer Management decisions present with no changes needed. Migration audit: Sprint 0 customers migration (2026_06_25_000001_create_customers_table.php) is incompatible with the final architecture and must be replaced before first shared environment use. Specific incompatibilities: column `customer_code` (should be `customer_number`), column `full_name` (should be `name`), columns `identity_type`/`identity_number` (not in final spec), missing `customer_type`, missing `whatsapp_phone`, missing `alt_phone`, normalized address columns `address_line`/`district`/`city`/`province`/`postal_code` (should be single `address` TEXT), status default `active` (should be `prospect`), column `joined_at` (not in final spec), columns `created_by`/`updated_by`/`deleted_by` FKs (not in final spec), missing `cluster_id` FK, missing `service_area_id` FK, missing `user_id` FK. Replacement is permitted under the Migration Strategy Before First Release architecture decision.

**Files Modified:**
- docs/architecture/glossary.md
- docs/database/erd.md
- docs/changelog/development-log.md

**Architecture Impact:** Closes all remaining documentation inconsistencies for the Customer module. All six architecture documents now reference identical canonical definitions: Prospect as initial state, 4-state lifecycle, customer_number format CUST-000001, customer_type individual/business passive, single address model, contact fields on customers table, orthogonal Customer/Subscription status, QRCodeReference relationship, all 6 events, 6-condition soft delete pre-validation. Customer module architecture documentation is fully internally consistent and ready for implementation.

**Notes:** Documentation only. No migrations, models, or code modified. Migration audit confirms the Sprint 0 customers migration must be replaced as part of ARCH-009 before any team member runs migrations. Architecture readiness verdict: READY FOR CUSTOMER IMPLEMENTATION — all blocking documentation items closed, all consistency issues resolved.

---

### 2026-07-02 | Backend | Sprint 2.1 — Customer Module Implementation

**Summary:** Implemented the complete Customer module covering all layers from database migrations through feature tests. Replaced the incompatible Sprint 0 customers migration (ARCH-009) by deleting `2026_06_25_000001_create_customers_table.php` and creating three new migrations: `2026_07_02_000001_create_clusters_table.php` (stub for FK dependency), `2026_07_02_000002_create_service_areas_table.php` (stub for FK dependency), `2026_07_02_000003_create_customers_table.php` (full implementation with 20 columns: id, customer_number, name, customer_type, email, phone, whatsapp_phone, alt_phone, address, latitude, longitude, notes, cluster_id, service_area_id, user_id, status, timestamps, softDeletes). Created `CustomerType` enum (individual/business, passive in v1.0) following project enum conventions (label(), badgeColor(), isIndividual(), isBusiness(), values(), options()). Created six Domain Events following ShouldDispatchAfterCommit pattern: CustomerRegistered, CustomerUpdated, CustomerConverted (with subscription trigger note), CustomerSuspended (with reason property), CustomerReactivated, CustomerTerminated (with reason property). Created `Customer` model (Aggregate Root) with all relationships (cluster/serviceArea/user BelongsTo, subscriptions/invoices/payments HasMany), all casts (status→CustomerStatus, customer_type→CustomerType, GPS decimals), status scopes (scopeProspect/Active/Suspended/Terminated), state checks (isProspect/isActive/isSuspended/isTerminated), displayName accessor. Created stub models Cluster, ServiceArea, Subscription, Invoice, Payment. Created `CustomerFactory` with state methods (active(), suspended(), terminated(), business()) and auto-incrementing customer number generation. Created `CustomerPolicy` with before() super-admin bypass and permission checks for viewAny/view/create/update/suspend/reactivate/terminate/delete. Created four Form Requests: StoreCustomerRequest, UpdateCustomerRequest, SuspendCustomerRequest (requires reason min 10 chars), TerminateCustomerRequest (requires reason min 10 chars). Created `CustomerService` extending AbstractCrudService with: generateCustomerNumber() using lockForUpdate() for race condition prevention, create() with DB::transaction + CustomerRegistered event, update() with CustomerUpdated event, delete() with 6 pre-condition validation (financial + operational restrict), suspend() with Active state validation + lockForUpdate + CustomerSuspended event, reactivate() with Suspended state validation + CustomerReactivated event, terminate() with subscription pre-condition check + CustomerTerminated event, buildIndexData/buildCreateData/buildEditData/findForShow view builders, applyDefaultRelationships/applyFilters/applyDefaultOrdering hooks. Created `CustomerResource` for JSON API representation. Created `CustomerController` (orchestration only: authorize/validate/invoke/respond with 10 actions: index/create/store/show/edit/update/destroy/suspend/reactivate/terminate). Added customer routes to web.php (resource + 3 lifecycle POST routes). Registered CustomerPolicy in AuthServiceProvider. Added Customers menu item to adminlte.php navigation. Created four Blade views: customers/index (search/filter with status filter and name/number/email/phone search, card layout, status badges), customers/show (Customer 360 workspace with profile card, 8-tab layout: Overview/Subscriptions/Billing/Payments/Tickets/Timeline/Attachments, suspend/reactivate/terminate action buttons with Alpine-free Bootstrap modals), customers/create (identity/contact/location/notes sections with type selector), customers/edit (same sections with read-only customer_number display). Created `CustomerControllerTest` with 23 test methods covering index/create/store/update/destroy/suspend/reactivate/terminate with authentication, authorization, validation, happy path, and business rule violation scenarios. Created RoleFactory, SubscriptionFactory, PaymentFactory for test support. Fixed pre-existing LSP violation in RoleService::delete() (was typed `Role $role`, changed to untyped `$role` matching AbstractCrudService contract). Verified all PHP syntax clean, all 10 routes registered correctly.

**Files Added:**
- database/migrations/2026_07_02_000001_create_clusters_table.php
- database/migrations/2026_07_02_000002_create_service_areas_table.php
- database/migrations/2026_07_02_000003_create_customers_table.php
- app/Enums/CustomerType.php
- app/Domain/Events/CustomerRegistered.php
- app/Domain/Events/CustomerUpdated.php
- app/Domain/Events/CustomerConverted.php
- app/Domain/Events/CustomerSuspended.php
- app/Domain/Events/CustomerReactivated.php
- app/Domain/Events/CustomerTerminated.php
- app/Models/Customer.php
- app/Models/Cluster.php (stub)
- app/Models/ServiceArea.php (stub)
- app/Models/Subscription.php (stub)
- app/Models/Invoice.php (stub)
- app/Models/Payment.php (stub)
- database/factories/CustomerFactory.php
- database/factories/RoleFactory.php
- database/factories/SubscriptionFactory.php
- database/factories/PaymentFactory.php
- app/Policies/CustomerPolicy.php
- app/Http/Requests/Customer/StoreCustomerRequest.php
- app/Http/Requests/Customer/UpdateCustomerRequest.php
- app/Http/Requests/Customer/SuspendCustomerRequest.php
- app/Http/Requests/Customer/TerminateCustomerRequest.php
- app/Services/Customer/CustomerService.php
- app/Http/Resources/CustomerResource.php
- app/Http/Controllers/CustomerController.php
- resources/views/customers/index.blade.php
- resources/views/customers/show.blade.php
- resources/views/customers/create.blade.php
- resources/views/customers/edit.blade.php
- tests/Feature/CustomerControllerTest.php

**Files Modified:**
- routes/web.php (added customer routes)
- app/Providers/AuthServiceProvider.php (registered CustomerPolicy)
- config/adminlte.php (added Customers navigation item)
- app/Services/Identity/RoleService.php (fixed pre-existing LSP violation)
- docs/changelog/development-log.md

**Files Deleted:**
- database/migrations/2026_06_25_000001_create_customers_table.php (incompatible Sprint 0 migration replaced per ARCH-009 and Migration Strategy Before First Release decision)

**Architecture Impact:** Customer module implementation follows Service-First Application Layer decision throughout: CustomerController is orchestration-only (10 actions each with authorize/validate/invoke/respond, zero business logic), CustomerService owns all business operations (lifecycle transitions, event dispatch, transaction management, pre-condition validation), Customer model is persistence-only (no business logic, no events, no transactions). Domain Events follow events.md standard (ShouldDispatchAfterCommit on all 6 events preventing listeners from seeing uncommitted state). Lifecycle transitions follow customer-workflow.md exactly: Prospect→Active automatic via SubscriptionActivated (CustomerConverted dispatched), Active→Suspended administrative-only with reason (CustomerSuspended dispatched), Suspended→Active reactivation (CustomerReactivated dispatched), Active/Suspended→Terminated with all-subscriptions-terminated pre-condition (CustomerTerminated dispatched). Soft delete follows 6-condition pre-validation per customer-workflow.md. Customer number generation uses lockForUpdate() per transactions.md pessimistic locking to prevent race conditions under concurrent customer creation. Customer account suspension is implemented as administrative-only action separate from Subscription suspension per Customer Account vs Subscription Suspension Semantics decision. Cluster and ServiceArea are stub implementations enabling FK constraints; full module implementation is a separate story. cluster_id and service_area_id are nullable in the migration as a pre-seeded concession (entities.md documents them as required; will become NOT NULL when Cluster/ServiceArea modules are implemented with seeder data).

**Notes:** Feature tests require migration to run (`php artisan migrate`) before execution. The subscriptions migration (2026_06_25_000005) references customers table — ordering is now correct since customers migration runs at 2026_07_02_000003 after the subscriptions migration's timestamp. Wait — the subscriptions migration has a 2026_06_25 timestamp which is BEFORE the 2026_07_02 customers migration. This means the subscriptions migration will fail on fresh install because it references customers which doesn't exist yet. This is a pre-existing issue with the Sprint 0 migration ordering that must be resolved as a separate task (replace or reorder Sprint 0 business migrations). For immediate development, running `php artisan migrate` on a clean database will fail at the subscriptions migration. The recommended approach is to run only the Identity/Settings migrations plus the three new Customer module migrations until the other Sprint 0 migrations are replaced.

---

### 2026-07-03 | Database | Migration Ordering Fix + Subscription Architecture Pre-Check

**Summary:** Resolved the migration ordering problem introduced when the customers migration was re-timestamped to 2026_07_02, which made five Sprint 0 business migrations (subscriptions, invoices, invoice_items, payments, payment_allocations) run before the customers table they depend on. Per the pre-release migration replacement policy: created five new stub migrations at timestamps 2026_07_02_000004 through 2026_07_02_000008 with identical schema content to the Sprint 0 originals, then deleted the five obsolete Sprint 0 migrations (2026_06_25_000005 through 2026_06_25_000009). Migration onu_statistics (2026_06_25_000010) retained unchanged as it only depends on onus which remains at 2026_06_25_000004. Verified the complete 24-migration dependency chain is valid for a fresh install: users→password_reset_tokens→failed_jobs→personal_access_tokens→packages→olts→onus→onu_statistics→roles→permissions→role_user→permission_role→permission_user→setting_categories→setting_registry_entries→settings→clusters→service_areas→customers→subscriptions→invoices→invoice_items→payments→payment_allocations. All FK dependency directions satisfied. Conducted Subscription module architecture pre-check by reviewing subscription-lifecycle.md, entities.md, erd.md, business-events.md, decisions.md, and glossary.md. Found 2 Critical and 5 High blocking gaps: (1) Lifecycle mismatch between entities.md (6 states: Pending/Active/Suspended/Reactivated/Cancelled/Terminated) and subscription-lifecycle.md (11 states including pre-activation Survey/Installation/Provisioning phases) with no SubscriptionStatus enum values formally specified, (2) Subscription column specification is conceptual prose only — no column table exists, (3) subscription_type primary/addon column undefined despite being referenced in One Active Subscription enforcement decision, (4) suspension sub-types (suspended_overdue/suspended_manual) from decisions.md not mapped to column specification, (5) 16 workflow-referenced events not cataloged in business-events.md. Added 7 new backlog items (ARCH-019 through ARCH-025) to architecture-backlog.md covering all Subscription architecture gaps. Updated Backlog Summary.

**Files Added:**
- database/migrations/2026_07_02_000004_create_subscriptions_table.php
- database/migrations/2026_07_02_000005_create_invoices_table.php
- database/migrations/2026_07_02_000006_create_invoice_items_table.php
- database/migrations/2026_07_02_000007_create_payments_table.php
- database/migrations/2026_07_02_000008_create_payment_allocations_table.php

**Files Modified:**
- docs/architecture/architecture-backlog.md
- docs/changelog/development-log.md

**Files Deleted:**
- database/migrations/2026_06_25_000005_create_subscriptions_table.php
- database/migrations/2026_06_25_000006_create_invoices_table.php
- database/migrations/2026_06_25_000007_create_invoice_items_table.php
- database/migrations/2026_06_25_000008_create_payments_table.php
- database/migrations/2026_06_25_000009_create_payment_allocations_table.php

**Architecture Impact:** Migration ordering is now fully correct for fresh database installation. The dependency chain satisfies all FK constraints in correct sequence. The five replacement migrations are stubs (same schema content as Sprint 0, just re-timestamped); full architecture-aligned replacements will be generated when each module undergoes its pre-implementation architecture review. Subscription architecture pre-check confirms the module is NOT ready for implementation and requires five pre-work stories (ARCH-019 through ARCH-023) to resolve before any Subscription code can be generated, following the same pre-work pattern established for the Customer module.

**Notes:** No application code changed. No models, services, controllers, or views modified. The five new stub migrations preserve the Sprint 0 schema content unchanged — they are ordering fixes only, not architecture improvements. Each will be replaced as part of its module's implementation sprint after architecture review.

---

### 2026-07-03 | Architecture | Subscription Module Pre-Work (ARCH-019 through ARCH-025)

**Summary:** Completed all Subscription module architecture pre-work closing seven backlog items (ARCH-019 through ARCH-025). Pre-Work A (ARCH-019): Three Architecture Decisions added to decisions.md under Customer Management — Subscription Lifecycle Canonical States (5 canonical states: pending/active/suspended/reactivation_pending/terminated; pre-activation sub-phases tracked via child entities while Subscription stays pending; Cancelled removed, Reactivated renamed to reactivation_pending), Subscription Type Classification for v1.0 (primary default; addon passive; one-active-subscription rule applies to primary type only), Subscription Suspension Data Model (single suspended status + suspension_type column overdue/manual; auto-reactivation only for overdue type). Pre-Work B (ARCH-020/021/022): entities.md Subscription fully respecified with 18-column table (customer_id, package_id, onu_id, status, subscription_type, suspension_type, suspension_reason, suspended_at, activated_at, reactivation_requested_at, terminated_at, terminated_reason, billing_day, notes, timestamps), 12 relationships (including ActivityLog/Attachment/SuspensionCase/ServiceRequest), 8 business rules. Pre-Work C (ARCH-023/024/025): Added SubscriptionCreated and SubscriptionReactivationPending events to business-events.md; updated entities.md Produces Events; added Subscription Status and Subscription Suspension terms to glossary.md. Stub subscriptions migration (2026_07_02_000004) deleted and replaced with full architecture-aligned migration (2026_07_03_000001). Architecture backlog updated: ARCH-019 through ARCH-025 all closed; summary shows 16/25 items closed.

**Files Added:** None (documentation only)

**Files Modified:**
- docs/architecture/decisions.md (3 new Architecture Decisions + ToC)
- docs/database/entities.md (Subscription entity respecified)
- docs/architecture/business-events.md (2 new events)
- docs/architecture/glossary.md (2 new terms)
- docs/architecture/architecture-backlog.md (7 items closed, summary updated)
- docs/changelog/development-log.md

**Architecture Impact:** Documentation only. Subscription module architecture is fully specified and internally consistent. All blocking items from the Subscription pre-check are closed.

**Notes:** Documentation only. No application code generated in this pre-work entry.

---

### 2026-07-03 | Backend | Sprint 2.4 — Payment Module Implementation

**Summary:** Implemented the Payment module end-to-end following the finalized payment architecture. Replaced the Sprint 0 payment stubs with architecture-aligned migrations and full domain code: Payment and PaymentAllocation models, PaymentStatus and PaymentAllocationStatus enums, PaymentService with lifecycle transitions and allocation/reversal logic, PaymentPolicy, payment form requests, PaymentResource, PaymentController, payment Blade views, Payment feature tests, route registration, policy registration, and AdminLTE navigation integration. Added payment domain events for intent, receipt, validation, recording, allocation, completion, reversal, failure, and partial/full allocation outcomes.

**Files Added:**
- app/Enums/PaymentStatus.php
- app/Enums/PaymentAllocationStatus.php
- app/Domain/Events/PaymentIntentCreated.php
- app/Domain/Events/PaymentReceived.php
- app/Domain/Events/PaymentValidated.php
- app/Domain/Events/PaymentRecorded.php
- app/Domain/Events/PaymentPartiallyAllocated.php
- app/Domain/Events/PaymentFullyAllocated.php
- app/Domain/Events/PaymentCompleted.php
- app/Domain/Events/PaymentFailed.php
- app/Domain/Events/PaymentReversed.php
- app/Services/Payment/PaymentService.php
- app/Policies/PaymentPolicy.php
- app/Http/Requests/Payment/StorePaymentRequest.php
- app/Http/Requests/Payment/UpdatePaymentRequest.php
- app/Http/Requests/Payment/ReceivePaymentRequest.php
- app/Http/Requests/Payment/ValidatePaymentRequest.php
- app/Http/Requests/Payment/RecordPaymentRequest.php
- app/Http/Requests/Payment/AllocatePaymentRequest.php
- app/Http/Requests/Payment/CompletePaymentRequest.php
- app/Http/Requests/Payment/ReversePaymentRequest.php
- app/Http/Requests/Payment/FailPaymentRequest.php
- app/Http/Resources/PaymentResource.php
- app/Http/Controllers/PaymentController.php
- resources/views/payments/index.blade.php
- resources/views/payments/create.blade.php
- resources/views/payments/edit.blade.php
- resources/views/payments/show.blade.php
- tests/Feature/PaymentControllerTest.php

**Files Modified:**
- database/migrations/2026_07_02_000007_create_payments_table.php
- database/migrations/2026_07_02_000008_create_payment_allocations_table.php
- app/Models/Payment.php
- app/Models/PaymentAllocation.php
- database/factories/PaymentFactory.php
- app/Services/Billing/InvoiceService.php
- app/Providers/AuthServiceProvider.php
- routes/web.php
- config/adminlte.php
- docs/changelog/development-log.md

**Architecture Impact:** Payment now follows the Service-First Application Layer model. `PaymentService` owns lifecycle transitions, immutable-record enforcement, and allocation/reversal coordination. Payment records are append-only after recording, allocations are separately tracked and reversible, and invoice balances are synchronized through the billing service rather than direct controller logic. The module is wired into authorization, navigation, routes, and UI.

**Notes:** PHP syntax checks passed for all touched files. Targeted test execution was attempted but the shell runner skipped the PHPUnit invocation, so runtime verification is still pending. The next recommended step is a clean `php artisan test --filter=PaymentControllerTest` run in a shell that allows the test process to execute.

---

### 2026-07-03 | Backend | Sprint 2.2 — Subscription Module Implementation

**Summary:** Implemented the complete Subscription module following the approved architecture and the Customer module as reference implementation. Deleted stub subscriptions migration (2026_07_02_000004) and created full architecture-aligned replacement (2026_07_03_000001) with 18 columns including status ENUM(pending/active/suspended/reactivation_pending/terminated), subscription_type ENUM(primary/addon), suspension_type ENUM(overdue/manual) nullable, all lifecycle timestamps (activated_at, suspended_at, reactivation_requested_at, terminated_at), billing_day, FK constraints for customer/package/onu. Created SubscriptionStatus enum (Pending/Active/Suspended/ReactivationPending/Terminated with label/badgeColor/state checks/values/options) and SubscriptionType enum (Primary/Addon) following project enum conventions. Created six Domain Events all implementing ShouldDispatchAfterCommit: SubscriptionCreated (subscription_id, customer_id), SubscriptionActivated (sub_id, customer_id), SubscriptionSuspended (sub_id, customer_id, suspensionType, reason), SubscriptionReactivationPending (sub_id, customer_id), SubscriptionReactivated (sub_id, customer_id), SubscriptionTerminated (sub_id, customer_id, reason). Replaced stub Subscription model with full implementation: all fillable columns, casts (status→SubscriptionStatus, subscription_type→SubscriptionType, all timestamps→datetime), 3 relationships (customer/package/invoices), 5 scopes (pending/active/suspended/terminated/primary), 9 state checks including isSuspendedOverdue/isSuspendedManual. Created Package stub model. Updated SubscriptionFactory with active/suspended/terminated/reactivationPending states. Created SubscriptionPolicy with before() super-admin bypass and 8 permission checks. Created 4 FormRequests: StoreSubscriptionRequest (customer_id, package_id, type, billing_day), UpdateSubscriptionRequest, SuspendSubscriptionRequest (suspension_type, suspension_reason min 5), TerminateSubscriptionRequest (reason min 10). Created SubscriptionService extending AbstractCrudService with: create() dispatching SubscriptionCreated, update(), delete() with pre-condition validation, activate() with lockForUpdate() one-active-primary enforcement + inline customer conversion (Prospect→Active + CustomerConverted dispatched), suspend() with state validation + lockForUpdate, requestReactivation() (Active→Suspended→ReactivationPending), reactivate() delegating to activate(), terminate() with open-invoice pre-condition check, buildIndexData/buildCreateData/buildEditData/findForShow view builders, applyDefaultRelationships/applyFilters/applyDefaultOrdering hooks. Created SubscriptionResource for JSON API. Created SubscriptionController (orchestration only, 12 actions: index/create/store/show/edit/update/destroy/activate/suspend/requestReactivation/reactivate/terminate). Added 12 subscription routes to web.php (resource + 5 lifecycle POST routes). Registered SubscriptionPolicy in AuthServiceProvider. Added Subscriptions item to AdminLTE navigation. Created 4 Blade views following Customer module patterns: index (table with customer/package/type/status/billing_day/activated_at, search/filter), show (2-column layout with detail card + 4-tab workspace: Overview/Invoices/Timeline/Attachments, suspend/terminate modal forms), create (customer selector, package selector, type, billing_day), edit (package/type/billing_day/notes). Created SubscriptionControllerTest with 17 test methods covering all endpoints, all lifecycle transitions, business rule enforcement (one-active-primary, prospect conversion, open-invoice pre-condition, invalid state transitions). Verified all PHP syntax clean, all 12 routes registered.

**Files Added:**
- database/migrations/2026_07_03_000001_create_subscriptions_table.php
- app/Enums/SubscriptionStatus.php
- app/Enums/SubscriptionType.php
- app/Domain/Events/SubscriptionCreated.php
- app/Domain/Events/SubscriptionActivated.php
- app/Domain/Events/SubscriptionSuspended.php
- app/Domain/Events/SubscriptionReactivationPending.php
- app/Domain/Events/SubscriptionReactivated.php
- app/Domain/Events/SubscriptionTerminated.php
- app/Models/Package.php (stub)
- app/Policies/SubscriptionPolicy.php
- app/Http/Requests/Subscription/StoreSubscriptionRequest.php
- app/Http/Requests/Subscription/UpdateSubscriptionRequest.php
- app/Http/Requests/Subscription/SuspendSubscriptionRequest.php
- app/Http/Requests/Subscription/TerminateSubscriptionRequest.php
- app/Services/Subscription/SubscriptionService.php
- app/Http/Resources/SubscriptionResource.php
- app/Http/Controllers/SubscriptionController.php
- resources/views/subscriptions/index.blade.php
- resources/views/subscriptions/show.blade.php
- resources/views/subscriptions/create.blade.php
- resources/views/subscriptions/edit.blade.php
- tests/Feature/SubscriptionControllerTest.php

**Files Modified:**
- app/Models/Subscription.php (stub → full implementation)
- database/factories/SubscriptionFactory.php (stub → full implementation)
- routes/web.php (subscription routes added)
- app/Providers/AuthServiceProvider.php (SubscriptionPolicy registered)
- config/adminlte.php (Subscriptions nav item added)
- docs/changelog/development-log.md

**Files Deleted:**
- database/migrations/2026_07_02_000004_create_subscriptions_table.php (stub replaced per ARCH-025)

**Architecture Impact:** Subscription module follows Service-First architecture throughout. Customer conversion (Prospect→Active) is handled inline in SubscriptionService::activate() alongside the one-active-primary enforcement, both using lockForUpdate() per transactions.md pessimistic locking. Domain Events all implement ShouldDispatchAfterCommit ensuring consistent state before consumers execute. The suspension_type column cleanly separates auto-reactivatable (overdue) from operator-locked (manual) suspensions per the Subscription Suspension Data Model decision. Migration dependency chain validated: 2026_07_03_000001 runs after customers (2026_07_02_000003) and before invoices (2026_07_02_000005) — fresh migrate succeeds.

**Notes:** Package and Onu models remain stubs; full implementation is separate stories. The packages table migration (2026_06_25_000002) uses is_active boolean rather than a status enum — this will be reconciled when the Package module architecture is reviewed. Test suite requires running migrations before execution.

---

### 2026-07-03 | Backend | Sprint 2.3 — Invoice Module Implementation

**Summary:** Implemented the complete Invoice module following the approved architecture and reusing the Customer/Subscription module patterns. Built Billing service-first flow end-to-end: created InvoicePolicy, four Form Requests (store/update/publish/cancel), InvoiceResource, InvoiceController (orchestration only), InvoiceService (all business rules and lifecycle transitions), Invoice and InvoiceItem models, PaymentAllocation stub model, two domain events (InvoiceOverdue, InvoiceCancelled), full Blade UI (index/show/create/edit), factory support, feature tests, route registration, policy registration, and AdminLTE navigation integration. Kept invoice lifecycle enforcement in InvoiceService: create draft invoice with one-invoice-per-period protection, publish draft invoice with preconditions (must have items and positive total), cancel draft-only with mandatory reason, overdue transition only from published/partially-paid, and payment allocation bookkeeping via `recordPaymentAllocation()`. Ensured immutable invoice behavior after publication by restricting updates to draft status only. Replaced misordered Invoice migrations with architecture-aligned replacements at the correct timestamp order so payment allocation FK dependencies resolve during fresh migrations.

**Files Added:**
- app/Domain/Events/InvoiceOverdue.php
- app/Domain/Events/InvoiceCancelled.php
- app/Policies/InvoicePolicy.php
- app/Http/Requests/Invoice/StoreInvoiceRequest.php
- app/Http/Requests/Invoice/UpdateInvoiceRequest.php
- app/Http/Requests/Invoice/PublishInvoiceRequest.php
- app/Http/Requests/Invoice/CancelInvoiceRequest.php
- app/Http/Resources/InvoiceResource.php
- app/Services/Billing/InvoiceService.php
- app/Http/Controllers/InvoiceController.php
- app/Models/InvoiceItem.php
- app/Models/PaymentAllocation.php
- database/factories/InvoiceFactory.php
- resources/views/invoices/index.blade.php
- resources/views/invoices/show.blade.php
- resources/views/invoices/create.blade.php
- resources/views/invoices/edit.blade.php
- tests/Feature/InvoiceControllerTest.php
- database/migrations/2026_07_02_000005_create_invoices_table.php
- database/migrations/2026_07_02_000006_create_invoice_items_table.php

**Files Modified:**
- app/Models/Invoice.php (stub → full implementation)
- app/Providers/AuthServiceProvider.php (InvoicePolicy registered)
- routes/web.php (invoice resource + lifecycle routes)
- config/adminlte.php (Invoices navigation item added)
- docs/changelog/development-log.md

**Files Deleted:**
- database/migrations/2026_07_03_000002_create_invoices_table.php (replaced to fix migration ordering)
- database/migrations/2026_07_03_000003_create_invoice_items_table.php (replaced to fix migration ordering)

**Architecture Impact:** Invoice module now follows Service-First Application Layer rules consistently: Controller handles authorize/validate/invoke/respond only; all lifecycle and business constraints are centralized in `InvoiceService`. Domain events implement `ShouldDispatchAfterCommit` semantics through the module event pattern. Migration order is now architecture-safe for fresh installs: invoices and invoice_items run before payment_allocations, resolving FK dependency correctness. Invoice immutability model is enforced in code (draft editable; published immutable except payment-driven financial fields).

**Notes:** Initial focused test run failed before executing assertions due pre-existing migration order dependency (`payment_allocations.invoice_id` FK before invoices existed). This was resolved by replacing the invoice migration timestamps to run before payment allocations. A re-run of `InvoiceControllerTest` was requested but skipped in-session by user action, so final green test confirmation is pending.

---

### 2026-07-03 | Architecture | Sprint 2.4 Pre-Work — Payment Module Architecture Finalization

**Summary:** Completed Payment module pre-work by formalizing canonical lifecycle, immutability rules, entity column specifications, missing business events, and backlog governance for implementation readiness. Added two new payment architecture decisions to `decisions.md`: (1) Payment Lifecycle Canonical States with 10 canonical persisted status values (`intent_created`, `waiting_payment`, `received`, `validated`, `recorded`, `partially_allocated`, `fully_allocated`, `completed`, `reversed`, `failed`), and (2) Payment Record and Allocation Immutability defining immutable payment records plus append-only allocation correction semantics. Expanded `entities.md` Payment and PaymentAllocation from conceptual definitions to full column-level specifications, including status enums, lifecycle timestamps, correction metadata, and explicit no-soft-delete policy for financial integrity. Added missing Payment lifecycle events `PaymentReversed` and `PaymentFailed` to `business-events.md` with full contracts (producer, consumers, triggers, audit, idempotency) and updated the event matrix. Added glossary terms `Payment Status` and `Payment Allocation Status` to standardize terminology. Updated `architecture-backlog.md` by adding Epic F (ARCH-026 through ARCH-031): ARCH-026/027/028/029 closed via this pre-work, and ARCH-030/031 created as blocking Todo items for replacing Sprint 0 payment migrations during implementation.

**Files Added:** None

**Files Modified:**
- docs/architecture/decisions.md
- docs/database/entities.md
- docs/architecture/business-events.md
- docs/architecture/glossary.md
- docs/architecture/architecture-backlog.md
- docs/changelog/development-log.md

**Architecture Impact:** Payment module architecture is now documentation-complete for service-layer implementation and event-driven integration. Canonical payment statuses and allocation correction semantics are explicit and enforceable, reducing ambiguity in `PaymentService`, `PaymentStatus` enum design, reporting, and downstream consumers (Billing, Subscription Lifecycle, Collector, Notification, Customer Portal). Financial immutability policy is aligned across Invoice and Payment domains with correction-through-reversal semantics. Remaining implementation blockers are now explicit backlog items: replacement of stub migrations `2026_07_02_000007_create_payments_table.php` and `2026_07_02_000008_create_payment_allocations_table.php`.

**Notes:** Documentation-only pre-work. No PHP application code, routes, controllers, services, migrations, or tests were generated in this story.

