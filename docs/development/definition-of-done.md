# Definition of Done

**Version:** 1.0  
**Status:** Authoritative  
**Last Updated:** 2026-07-02  
**Owner:** Architecture Team

---

## Purpose

This document defines the mandatory Definition of Done (DoD) for every Story delivered in the ISP Management Platform project.

A Story is **not complete** until every applicable item in this checklist has been satisfied. Partial completion is not delivery. A Story that ships without satisfying the DoD is considered open.

The DoD applies uniformly across all Sprints, all modules, and all Story types — backend, frontend, architecture, documentation, and database.

---

## Guiding Principles

### 1. Architecture Before Code

Architecture documentation must be created or updated before implementation begins on any Story that introduces new business behavior, entities, workflows, or integration boundaries.

Code that contradicts existing architecture documentation must not be merged. It must either be corrected or trigger a documented architecture update.

### 2. Documentation is Delivery

Updating documentation is not optional cleanup after a Story is "code-complete". Documentation is part of the delivery. A Story without documentation is incomplete.

### 3. Tests Protect Behavior

Tests are not optional. Every Service method with business logic must have a unit test. Every HTTP endpoint must have a feature test. Tests that pass before and after the Story demonstrate that the implementation is correct and does not regress existing behavior.

### 4. Self-Review Before Review Request

Developers must review their own work against this checklist before requesting Tech Lead review. Reviews that identify items the developer should have caught independently waste review time.

### 5. Development Log is Permanent Record

The Development Log is an append-only project history. Every completed Story produces a Development Log entry. Skipping log entries creates gaps in the project record that cannot be recovered.

---

## Applicability Matrix

Not every checklist item applies to every Story type. Use this matrix to determine which items are mandatory for each Story category.

| Checklist Item | Arch Doc | DB Change | Backend | Frontend | Bug Fix | Refactor |
|---|---|---|---|---|---|---|
| Architecture Decision | ✅ | — | ✅ | — | — | — |
| Entities | — | ✅ | ✅ | — | — | — |
| Migration | — | ✅ | ✅ | — | — | — |
| Model | — | ✅ | ✅ | — | — | — |
| Service | — | — | ✅ | — | ✅ | ✅ |
| Policy | — | — | ✅ | — | — | — |
| Controller | — | — | ✅ | — | — | — |
| Form Request | — | — | ✅ | — | — | — |
| Seeder | — | ✅ | ○ | — | — | — |
| Views | — | — | ○ | ✅ | ○ | — |
| Architecture Doc | ✅ | — | ○ | — | — | ○ |
| Development Log | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Self-Review | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Tech Lead Review | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Unit Tests | — | — | ✅ | — | ✅ | ✅ |
| Feature Tests | — | — | ✅ | ✅ | ✅ | — |
| Merge Ready | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

**Legend:**
- ✅ Mandatory — must be completed before the Story is done
- ○ Conditional — mandatory if the Story introduces or modifies this artifact type
- — Not applicable for this Story type

---

## Checklist

### ✦ Architecture

#### A1. Architecture Decision (when applicable)

- [ ] If the Story introduces a new architectural pattern, integration boundary, or platform-level rule, a decision entry has been added to `docs/architecture/decisions.md`
- [ ] The decision follows the established format: Decision, Reason, Impact
- [ ] The table of contents in `decisions.md` has been updated
- [ ] The decision does not contradict any existing architecture decision
- [ ] If the Story supersedes or modifies an existing decision, the previous decision has been explicitly marked as superseded

**Applies to:** Architecture stories, new module introduction, new integration boundary, new cross-cutting platform rule.

**Skip condition:** Story adds no new architectural patterns or platform-level rules.

---

### ✦ Entities

#### E1. Entity Definition (when applicable)

- [ ] If the Story introduces a new entity, it has been added to `docs/database/entities.md`
- [ ] Entity classification is documented (Core / Transaction / Infrastructure / Platform / Operational / Identity)
- [ ] Aggregate root ownership is documented
- [ ] Lifecycle states are documented if the entity is lifecycle-managed
- [ ] Deletion behavior is documented (Soft Delete / Restrict / Archive / Cascade)
- [ ] Immutability rules are documented if applicable
- [ ] All relationships are documented with cardinality

**Applies to:** Stories introducing new database tables, models, or entity lifecycle changes.

**Skip condition:** Story introduces no new entities and does not change existing entity definitions.

---

### ✦ Migration

#### M1. Database Migration

- [ ] Migration file created with correct timestamp prefix (`YYYY_MM_DD_NNNNNN_create_{table}_table.php`)
- [ ] Table name follows `snake_case` plural convention per `docs/database/erd.md`
- [ ] All columns defined as documented in `entities.md` and `erd.md`
- [ ] All foreign keys use `constrained()` with explicit `onDelete` behavior
- [ ] Indexes defined for all foreign key columns
- [ ] Indexes defined for all columns used in WHERE clauses and ORDER BY
- [ ] Soft delete column (`deleted_at`) added where documented
- [ ] `version` column added where optimistic locking is documented
- [ ] Migration verified against `erd.md` — no undocumented tables or columns
- [ ] Migration does not include hardcoded data (use Seeders for data)
- [ ] Down method implemented correctly for reversal

**Applies to:** Stories that add or modify database tables.

**Skip condition:** Story makes no database schema changes.

---

### ✦ Model

#### Mo1. Eloquent Model

- [ ] Model class created in correct namespace (`App\Models\{Domain}\` or `App\Models\`)
- [ ] Model name is singular PascalCase matching entity name from `glossary.md`
- [ ] `$fillable` or `$guarded` defined explicitly — never use unguarded `$guarded = []`
- [ ] All attribute casts defined in `$casts` array
- [ ] All relationships defined with correct Eloquent method types matching ERD cardinalities
- [ ] Both sides of bidirectional relationships defined
- [ ] Soft deletes trait added where documented (`SoftDeletes`)
- [ ] Timeline, ActivityLog, and Attachment morphMany relationships added where applicable
- [ ] Model contains NO business logic
- [ ] Model contains NO transaction management
- [ ] Model contains NO event dispatch
- [ ] Scopes defined for common query constraints (status filters, active records, etc.)

**Applies to:** Stories introducing new Eloquent models or modifying existing models.

**Skip condition:** Story introduces no new models and does not modify model definitions.

---

### ✦ Service

#### S1. Service Class

- [ ] Service class created in `app/Services/{Domain}/`
- [ ] Service name follows `{Verb}{Entity}Service` or `{Entity}Service` convention per `glossary.md`
- [ ] Service extends `AbstractCrudService` where standard CRUD is appropriate
- [ ] Dependencies injected via constructor (no Facade usage for primary dependencies)
- [ ] All business rules implemented in the Service — none in Controller, Model, or Policy
- [ ] All database transactions opened in the Service using `DB::transaction()` closure form
- [ ] Domain Events dispatched from Service after successful commit (using `ShouldDispatchAfterCommit`)
- [ ] Cache invalidation performed in Service after state-changing operations
- [ ] Queue jobs dispatched from Service using `->afterCommit()` where persistence is involved
- [ ] Service throws typed exceptions from the exception hierarchy (`DomainException`, `BusinessRuleException`, etc.)
- [ ] Service does NOT call `DB::transaction()` inside a Model method
- [ ] Service does NOT perform external API calls inside a database transaction
- [ ] Service is independently testable via constructor injection without HTTP layer

**Applies to:** All Backend stories introducing new business operations.

**Skip condition:** Story introduces no new business logic and does not change Service behavior.

---

### ✦ Policy

#### Po1. Authorization Policy

- [ ] Policy class created in `app/Policies/`
- [ ] Policy name follows `{Entity}Policy` convention
- [ ] Policy registered in `AuthServiceProvider`
- [ ] Policy methods implement `can`/`cannot` authorization logic only
- [ ] Policy checks active Role and Permission lifecycle per architecture decision
- [ ] Policy contains NO business logic
- [ ] Policy contains NO database writes
- [ ] Policy contains NO side effects (no events, no cache invalidation, no state changes)
- [ ] Every controller action calls `$this->authorize()` with the corresponding Policy method

**Applies to:** Stories introducing new entities with access control or new operations requiring authorization.

**Skip condition:** Story introduces no new authorization requirements.

---

### ✦ Controller

#### C1. Resource Controller

- [ ] Controller created in `app/Http/Controllers/{Domain}/`
- [ ] Controller follows Resource Controller structure (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`)
- [ ] Controller contains ONLY: authorize, validate (via FormRequest), invoke Service, return response
- [ ] Controller does NOT query Eloquent directly (Route Model Binding for lookup only)
- [ ] Controller does NOT access Cache facade
- [ ] Controller does NOT dispatch Events
- [ ] Controller does NOT open Transactions
- [ ] Controller does NOT execute business rules
- [ ] Every action calls `$this->authorize()` before any other logic
- [ ] Every write action uses a FormRequest for input validation
- [ ] Route registered in `routes/web.php` or `routes/api.php` with correct name
- [ ] Route name follows `{entity}.{action}` dot notation convention (e.g., `subscription.activate`)

**Applies to:** Stories introducing new HTTP endpoints or controller actions.

**Skip condition:** Story introduces no new HTTP endpoints.

---

### ✦ Form Request

#### FR1. Form Request Validation

- [ ] FormRequest class created in `app/Http/Requests/{Domain}/`
- [ ] FormRequest name follows `{Action}{Entity}Request` convention (e.g., `StoreInvoiceRequest`)
- [ ] `authorize()` method returns `true` (authorization delegated to Policy via Controller)
- [ ] All expected input fields declared in `rules()` with explicit validation rules
- [ ] Sensitive fields (passwords, tokens) use appropriate rules (`confirmed`, `min:8`, etc.)
- [ ] Custom error messages defined in `messages()` where default messages are unclear
- [ ] FormRequest does NOT contain business logic
- [ ] FormRequest does NOT query Eloquent for business validation (use `exists:` rule for FK checks only)

**Applies to:** Stories introducing new HTTP write endpoints (store, update, destroy).

**Skip condition:** Story introduces no new form submissions or API write endpoints.

---

### ✦ Seeder

#### Se1. Database Seeder (when applicable)

- [ ] Seeder created in `database/seeders/` with descriptive name (`{Entity}Seeder.php`)
- [ ] Seeder registered in `DatabaseSeeder.php` in correct order respecting foreign key dependencies
- [ ] Seeder is idempotent — safe to run multiple times without duplicating records
- [ ] Required reference data seeded (Roles, Permissions, Settings registry entries, Event Catalog entries)
- [ ] Seeder uses `firstOrCreate()` or `updateOrCreate()` not plain `create()` for reference data
- [ ] Seeder does NOT contain business logic
- [ ] Seeder does NOT dispatch Events
- [ ] Factory created in `database/factories/` if entity requires test data generation

**Applies to:** Stories introducing new reference data, lookup tables, permissions, roles, settings registry entries.

**Skip condition:** Story introduces no reference data requirements.

---

### ✦ Views

#### V1. Blade Views

- [ ] Blade views created in `resources/views/{domain}/`
- [ ] Layout extends the project's base admin layout (`layouts.admin` or equivalent)
- [ ] Index view uses card/grid layout by default (table only for naturally tabular data)
- [ ] Mobile-first responsive layout using Bootstrap grid
- [ ] Status badges use consistent colors aligned with Settings Engine configuration
- [ ] Destructive actions (delete, suspend, terminate) use Alpine.js confirmation modal — never `confirm()`
- [ ] Views contain NO business logic and NO direct database queries
- [ ] Views render only pre-prepared data passed from Controller
- [ ] All relationships eager-loaded in Controller/Service before passing to view
- [ ] Entity 360 workspace pattern followed for core entity detail pages (Overview, Timeline, Activity Log, Attachments, domain tabs)
- [ ] All user-visible text uses translation strings (`__('key')` or `@lang('key')`) where applicable

**Applies to:** Stories introducing new pages or modifying existing views.

**Skip condition:** Story is backend/architecture/documentation only with no view changes.

---

### ✦ Documentation

#### D1. Architecture Documentation (when applicable)

- [ ] If the Story introduces a new platform pattern, cross-cutting concern, or architectural standard, a new document created in `docs/architecture/`
- [ ] If an existing architecture document is affected by the Story, it has been updated
- [ ] Document follows the established format with Version, Status, Last Updated header
- [ ] Document includes: Purpose, Scope, Rules/Standards, Examples, Anti-Patterns, Migration Path
- [ ] All code examples in documentation are syntactically valid PHP/Laravel
- [ ] `docs/README.md` updated if a new documentation file has been added

**Applies to:** Architecture stories, platform standard stories, and any story that materially changes how the platform is built.

**Skip condition:** Story produces no new or changed architecture documentation.

---

#### D2. Workflow Documentation (when applicable)

- [ ] If the Story introduces or modifies a business workflow, the relevant `docs/workflows/*.md` document has been updated
- [ ] Lifecycle state transitions are accurately reflected in the workflow document
- [ ] State transition diagram updated if states have changed
- [ ] Business rules in the workflow document match implementation exactly

**Applies to:** Stories implementing or modifying lifecycle-managed business processes.

**Skip condition:** Story does not change any workflow behavior.

---

### ✦ Development Log

#### L1. Development Log Entry

- [ ] Entry appended to `docs/changelog/development-log.md`
- [ ] Entry is append-only — no previous entries modified (except correction of obvious spelling/formatting errors)
- [ ] Entry is in correct chronological order
- [ ] Entry includes: Date, Category, Title
- [ ] Entry includes: Summary describing what was done and why
- [ ] Entry includes: Files Added (all new files created)
- [ ] Entry includes: Files Modified (all existing files changed)
- [ ] Entry includes: Architecture Impact (none — or meaningful description)
- [ ] Entry includes: Notes (optional context, decisions made, known limitations)
- [ ] Summary is specific enough to reconstruct what the Story did without reading the code

**Applies to:** Every Story of every type without exception.

**No skip condition.** Development Log entry is mandatory for every Story.

---

### ✦ Self-Review

#### SR1. Developer Self-Review

Before requesting Tech Lead review, the developer must personally verify:

**Architecture Compliance**
- [ ] Implementation follows all relevant architecture decisions in `decisions.md`
- [ ] No undocumented business events dispatched
- [ ] No undocumented state transitions introduced
- [ ] Exact glossary terms used in class names, table names, and routes
- [ ] No out-of-scope features implemented (confirmed against `project-scope.md`)

**Code Quality**
- [ ] Controllers are orchestration-only — no business logic
- [ ] Services own all business operations — no logic in Controllers, Models, or Policies
- [ ] Models are persistence objects only
- [ ] No N+1 queries — all relationships eager-loaded with `with()`
- [ ] All queries paginated for index/list operations
- [ ] No hardcoded values that belong in the Settings Engine
- [ ] No TODO stubs or placeholder logic in delivered code

**Security**
- [ ] All user input validated via FormRequest
- [ ] All controller actions guarded with `$this->authorize()`
- [ ] No SQL injection risk (no raw queries with unparameterized user input)
- [ ] No sensitive data (passwords, tokens, secrets) logged or exposed in views
- [ ] Secrets stored in environment variables, not hardcoded

**Database**
- [ ] Migration matches `erd.md` — no invented columns or tables
- [ ] Foreign key constraints declared in migration
- [ ] Immutability rules enforced in code (financial records, audit logs never updated)

**Tests**
- [ ] All new Service methods have unit tests
- [ ] All new HTTP endpoints have feature tests
- [ ] Tests pass locally with `php artisan test`
- [ ] No test data left in production seed files

**Applies to:** Every Story of every type without exception.

**No skip condition.** Self-review is mandatory for every Story.

---

### ✦ Tech Lead Review

#### TL1. Tech Lead Review

The Tech Lead (or designated reviewer) must verify:

**Architecture**
- [ ] Story implementation is consistent with `decisions.md`
- [ ] No undocumented patterns introduced
- [ ] Exception handling follows `exceptions.md`
- [ ] Transaction boundaries follow `transactions.md`
- [ ] Event dispatch follows `events.md`
- [ ] Cache management follows `cache.md`
- [ ] Queue dispatch follows `queue.md`

**Code Quality**
- [ ] Service-First Application Layer decision followed (no business logic in Controllers, Models, Policies)
- [ ] Service methods are small and single-responsibility
- [ ] Dependencies injected — no Facade abuse inside business Services
- [ ] No code duplication — shared components reused
- [ ] Naming conventions from `glossary.md` applied throughout

**Security**
- [ ] Input validation complete and appropriate
- [ ] Authorization enforced on every endpoint
- [ ] No sensitive data exposed in logs, views, or API responses
- [ ] OWASP Top 10 concerns reviewed for any new input surface

**Database**
- [ ] Migration matches ERD — Tech Lead verifies against `erd.md`
- [ ] Foreign keys, indexes, and deletion behaviors correct
- [ ] No raw queries with injection risk

**Tests**
- [ ] Test coverage is adequate for business logic complexity
- [ ] Tests assert behavior, not implementation details
- [ ] Edge cases covered (invalid state, missing data, concurrent modification)

**Documentation**
- [ ] Development Log entry present, accurate, and complete
- [ ] Architecture documentation updated where needed
- [ ] Workflow documentation updated where needed

**Merge Readiness**
- [ ] No unresolved review comments
- [ ] Branch is up to date with target branch
- [ ] No merge conflicts

**Applies to:** Every Story of every type without exception.

**No skip condition.** Tech Lead review is mandatory for every Story.

---

### ✦ Testing

#### T1. Unit Tests

- [ ] Unit test file created in `tests/Unit/{Domain}/`
- [ ] Test class name matches Service: `{Entity}ServiceTest` or `{Verb}{Entity}ServiceTest`
- [ ] Every public Service method that contains business logic has at least one test
- [ ] Happy path tested (operation succeeds with valid input)
- [ ] Failure paths tested (invalid state, missing entity, business rule violation)
- [ ] Domain exceptions tested (correct exception type thrown, correct message)
- [ ] Dependencies mocked via constructor injection — no real database calls in unit tests (use `RefreshDatabase` for integration-style unit tests where needed)

#### T2. Feature Tests

- [ ] Feature test file created in `tests/Feature/{Domain}/`
- [ ] Test class name: `{Entity}ControllerTest`
- [ ] Every HTTP endpoint tested (GET and POST/PUT/DELETE)
- [ ] Authentication required — assert 401/403 for unauthenticated/unauthorized access
- [ ] Authorization tested — assert correct role/permission required
- [ ] Validation tested — assert 422 for invalid input with expected error messages
- [ ] Success path tested — assert correct redirect, response, and database state
- [ ] `assertDatabaseHas` and `assertDatabaseMissing` used to verify persistence
- [ ] Tests use `RefreshDatabase` for clean state isolation

#### T3. Test Execution

- [ ] All tests pass with `php artisan test`
- [ ] No pre-existing tests broken by the Story changes
- [ ] No test warnings or deprecation notices

**Applies to:** All Backend and Frontend stories.

**Skip condition:** Architecture-only or Documentation-only stories with no runtime code changes.

---

### ✦ Merge Ready

#### MR1. Merge Readiness

- [ ] All mandatory DoD items for this Story type are completed
- [ ] Self-Review (SR1) completed and signed off by developer
- [ ] Tech Lead Review (TL1) completed with no open blocking comments
- [ ] All tests pass (`php artisan test`)
- [ ] No lint errors or static analysis warnings
- [ ] Branch is up to date with the target branch (no merge conflicts)
- [ ] Commit history is clean and meaningful (no "WIP", "fix typo", "asdf" messages)
- [ ] Development Log entry appended and accurate
- [ ] No debug code, `dd()`, `dump()`, `var_dump()`, or `console.log()` in committed files
- [ ] No `.env` changes committed — only `.env.example` updated if new variables added
- [ ] No migration modifications after the migration has been run in any shared environment

**Applies to:** Every Story of every type without exception.

**No skip condition.** Merge Readiness is the final gate for every Story.

---

## Review Responsibilities

### Developer

The developer is responsible for completing all mandatory DoD items before requesting review. This includes writing tests, updating documentation, and completing the Development Log entry.

Submitting a Story for review without completing the DoD is a process violation. Review time must not be used to complete items the developer should have completed.

**Developer responsibilities:**

| Responsibility | When |
|---|---|
| Architecture compliance | Before writing any implementation code |
| Migration matches ERD | Before creating migration |
| Unit tests written | Before completing Service implementation |
| Feature tests written | Before completing Controller implementation |
| Self-review SR1 completed | Before requesting Tech Lead review |
| Development Log updated | Before requesting Tech Lead review |
| Documentation updated | Before requesting Tech Lead review |

### Tech Lead

The Tech Lead is responsible for reviewing architecture compliance, code quality, security, and completeness before approving a merge.

The Tech Lead review is not a substitute for self-review. If a review identifies items from the DoD that the developer should have caught, the review is returned without approval until self-review is completed.

**Tech Lead responsibilities:**

| Responsibility | When |
|---|---|
| Architecture compliance review | During code review |
| Security review | During code review |
| Database correctness | During migration review |
| Test coverage adequacy | During test review |
| Documentation completeness | During review |
| Merge approval | After all review comments resolved |

### AI Assistant (GitHub Copilot)

The AI assistant must follow this Definition of Done when generating or modifying code, migrations, documentation, or configuration.

**AI assistant responsibilities:**

| Responsibility | Behavior |
|---|---|
| Read architecture docs before coding | Always — per Architecture Principles |
| Produce complete artifacts | Generate all mandatory artifacts, not partial stubs |
| Update Development Log | Mandatory after every completed Story |
| Flag out-of-scope features | Inform developer and stop before implementing |
| Avoid hardcoded values | Always use Settings Engine for policy-driven values |
| Use exact glossary terms | Apply `glossary.md` naming throughout |
| Never modify financial records | Corrections only via corrective records |
| Propose documentation updates | When implementation would conflict with architecture |

---

## Story Completion Workflow

```
Developer writes Story
        │
        ▼
Read architecture docs
(decisions.md, glossary.md, relevant workflow, entities.md, erd.md)
        │
        ▼
Implement Story (code, migration, views)
        │
        ▼
Write tests (unit + feature)
        │
        ▼
Run php artisan test — all pass
        │
        ▼
Update documentation (architecture, workflow, entities as applicable)
        │
        ▼
Update Development Log
        │
        ▼
Complete Self-Review checklist (SR1)
        │
        ▼
Request Tech Lead Review
        │
        ▼
Tech Lead reviews and approves (TL1)
        │
        ▼
Merge Ready (MR1) — Story Complete
```

---

## Common Violations

These are the most frequently skipped DoD items. Reviewers should check these explicitly.

| Violation | Category | Prevention |
|---|---|---|
| Business logic in Controller | Service-First | Controller should call `$this->service->method()` only |
| Missing `$this->authorize()` call | Authorization | Every action starts with authorize |
| Migration missing FK constraints | Database | Always use `constrained()` with `onDelete` |
| N+1 query in view | Performance | Always eager-load with `with()` in Controller/Service |
| `event()` dispatched before commit | Events | Use `ShouldDispatchAfterCommit` on all Domain Events |
| Job dispatched without `afterCommit()` | Queue | Always use `->afterCommit()` for persistence-dependent jobs |
| No unit test for Service logic | Testing | Write tests alongside Service implementation |
| Development Log not updated | Documentation | Last step before requesting review |
| Hardcoded policy value | Configuration | Every policy value goes in Settings Engine |
| Generic `\Exception` thrown | Exceptions | Use typed exceptions from exception hierarchy |
| External API call inside transaction | Transaction | Queue or call-before-transaction patterns |

---

## References

| Document | Purpose |
|---|---|
| `docs/architecture/decisions.md` | Architecture decisions — read before every Story |
| `docs/architecture/glossary.md` | Canonical terminology — naming for all artifacts |
| `docs/architecture/events.md` | Domain event dispatch standard |
| `docs/architecture/logging.md` | Logging levels and channel assignments |
| `docs/architecture/cache.md` | Cache naming, TTL, and invalidation rules |
| `docs/architecture/queue.md` | Queue naming, retry, priority, and idempotency |
| `docs/architecture/transactions.md` | Transaction ownership and locking strategies |
| `docs/architecture/exceptions.md` | Exception hierarchy and handling patterns |
| `docs/database/entities.md` | Entity definitions and lifecycle rules |
| `docs/database/erd.md` | Authoritative relationship model and cardinalities |
| `docs/workflows/*.md` | Business workflow lifecycle states and transitions |
| `docs/project/project-scope.md` | Feature scope — confirm before implementing |
| `docs/changelog/development-log.md` | Append-only project history |

---

**End of Document**
