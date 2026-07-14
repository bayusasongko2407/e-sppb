# E-SPPB Enterprise Agent Guide

## Project Overview

E-SPPB Enterprise is an enterprise platform for digitalizing Surat Permintaan Pengeluaran Barang (SPPB). The project uses documentation-first delivery, clean architecture, service-layer business logic, REST API first design, full auditability, workflow approval, SLA monitoring, QR/document validation, and future Flutter readiness.

The `/docs` directory is the single source of truth for business rules, architecture, workflow, database, security, operations, and quality standards. Do not invent business rules. Do not change frozen business logic or frozen database schema without explicit instruction and documented approval.

## Repository Structure

- `AGENTS.md`: primary entry point for AI agents.
- `.codex/`: operational knowledge base extracted from `/docs`.
- `.codex/config.toml`: Codex provider configuration. Preserve it unless explicitly instructed.
- `.design/`: mandatory enterprise design system for every UI, page, widget, component, dashboard, table, form, and mobile layout.
- `docs/`: authoritative source documents and old blueprint schema files.
- `prompts/`: reusable prompt patterns.
- `templates/`: implementation and review templates.
- `playbooks/`: step-by-step operational workflows.
- `checklists/`: quality gates and validation lists.
- `knowledge/`: repository map and extracted domain knowledge.
- `decision-tree/`: task routing and implementation decisions.
- `memory/`: durable project memory for future agents.
- `memory/continuation-log.md`: active checkpoint and append-only history for resuming interrupted sessions.

## Technology Stack

- Laravel 12
- PHP 8.3
- Filament v5
- Livewire and Alpine.js
- MariaDB with utf8mb4 / utf8mb4_unicode_ci
- Redis for queue/cache
- Ubuntu Server, Nginx, PHP-FPM, Supervisor, Cron
- REST API `/api/v1`
- Flutter Android readiness, implementation deferred to later phase

## Context Loading Strategy

Never load every document into context by default.

1. Start with this `AGENTS.md`.
2. Open `.codex/00_INDEX.md` to route the task.
3. If resuming interrupted work, open `memory/continuation-log.md` before inspecting implementation files.
4. Open only the relevant `.codex/*` guide for the task.
5. Open `/docs/*` only when exact detailed rules are needed.
6. For database/schema detail, also inspect `docs/Old Blueprint/*.yaml` when the task touches tables, relationships, indexes, constraints, running numbers, workflow, status logs, assets, users, departments, plants, attachments, or audit.

## AI Workflow

1. Identify requested phase and do not jump ahead.
2. Load the minimum relevant context.
3. Understand architecture, business rules, database impact, workflow impact, API impact, Flutter impact, security impact, and testing impact before coding.
4. Implement only after requirements and constraints are clear.
5. Validate with tests/checks appropriate to risk.
6. Update documentation when behavior, architecture, API, deployment, or workflow changes.
7. Maintain `memory/continuation-log.md`: checkpoint before risky or multi-file work, update after each completed atomic task, and never write secrets into it.

## Laravel Standards

- Thin Controllers and thin Filament Resources.
- Fat Services: all business logic belongs in Service Layer.
- Use Form Requests for validation.
- Use Policies for authorization.
- Use Events and Listeners for business-driven asynchronous side effects.
- Use Queues for heavy work: notification, import, export, heavy reports, mail.
- Use Repository only when justified by real abstraction needs.
- Use Eloquent ORM, eager loading, transactions for critical flows, and pagination for large data.

## Filament Standards

- Filament v5 resources are presentation only.
- Resource actions such as submit, approve, reject, revision, cancel, validate, and export must call services.
- Apply Policy and role/permission visibility to resources and actions.
- Tables should be searchable, sortable, filterable, paginated, exportable where required, and N+1 safe.
- Forms use sections, responsive grids, clear placeholders, readonly states from business rules, and validation outside UI business logic.
- Navigation groups: Dashboard, Master Data, SPPB, Workflow, Monitoring, Laporan, Pengaturan.
- All Filament UI must follow `.design/` design system tokens, components, layout, accessibility, and responsive rules.

## PHP Standards

- PSR-1, PSR-4, PSR-12.
- Strict typing for new files where possible.
- Use enums for stable statuses.
- One responsibility per class.
- Dependency injection and constructor property promotion where appropriate.
- Avoid static helpers for business logic.
- Use specific exceptions; do not swallow exceptions.

## MariaDB Standards

- Database schema is frozen unless explicit approval and ADR exist.
- MariaDB is the primary database.
- Maintain referential integrity and auditability.
- Primary keys on all tables, foreign keys indexed, unique indexes for document numbers and immutable identifiers, composite indexes for frequent queries.
- Use transactions for SPPB submit and approval.
- Roll back if workflow generation or approval consistency fails.
- Use parameter binding. Do not use unvalidated dynamic SQL.
- Backup database and attachments separately; encrypt backups.

## Coding Conventions

- Class names: `PascalCase`.
- Methods and variables: `camelCase`.
- Constants: `UPPER_CASE`.
- Migrations: `snake_case`.
- Tables per documentation: `tbl_<entity>` in standards; verify exact frozen names in schema docs/blueprints before implementing.
- Views: `vw_<entity>`.
- Foreign key fields: `<table>_id`.
- Boolean fields: `is_<name>`.
- Timestamps: `created_at`, `updated_at`.

## Git Workflow

- Branches: `main`, `develop`, `feature/*`, `release/*`, `hotfix/*`.
- Use Conventional Commits: `feat:`, `fix:`, `refactor:`, `docs:`, `test:`, `chore:`, `perf:`, `ci:`.
- Pull Requests must include summary, related docs, database impact, API impact, and test results.
- `main` and `develop` are protected; direct push is prohibited.
- Use semantic versioning: `MAJOR.MINOR.PATCH`.

## Prompt Rules

Every implementation prompt must include:

- Goal of change.
- Reference documents.
- Related module.
- Database impact.
- API impact.
- Flutter impact.
- Security impact.
- Testing requirement.

## Code Review Rules

Findings first. Verify:

- No frozen rule violation.
- Service Layer is used.
- No business query/business logic in UI.
- Policies and validation are present.
- Tests cover affected service/workflow/API.
- Docs are updated.
- No secrets or credentials are committed.

## Security Rules

- HTTPS only in deployed environments.
- Login uses Email or NIK.
- Inactive users cannot login.
- Role and Permission are mandatory.
- Use Laravel Policies for authorization.
- Audit important activities: login, logout, approval, important data changes, security errors.
- Validate all input and file uploads.
- Protect against CSRF, XSS, SQL injection.
- Do not log passwords, tokens, or secrets.

## Deployment Workflow

1. Pull latest release.
2. Composer install.
3. Build assets if needed.
4. Run migrations only when approved by database policy.
5. Cache config/routes/views.
6. Restart queue.
7. Run health checks.
8. Verify login, dashboard, queue, scheduler, upload, API, and logs.

## Testing Workflow

- Unit tests for services and domain logic.
- Feature tests for HTTP, workflow, and SPPB.
- Integration tests for database, queue, mail, storage.
- API contract tests for REST.
- Filament UI tests where practical.
- Regression tests before release.

## Documentation Workflow

- Update related `/docs` document for approved business/architecture changes.
- Update `.codex` operational guide when agent-facing workflow changes.
- Maintain traceability: requirement -> architecture -> service -> API/UI -> tests.
- Add ADR for new architecture decisions.

## Business Rule References

- Business scope and frozen rules: `docs/01_MASTER_PLAN.md`, `docs/02_PROJECT_SCOPE.md`, `docs/03_BUSINESS_REQUIREMENT.md`.
- Functional rules: `docs/04_FUNCTIONAL_REQUIREMENT.md`.
- Architecture: `docs/06_SYSTEM_ARCHITECTURE.md`.
- Domain/database: `docs/07_DOMAIN_MODEL.md`, `docs/08_DATABASE_MASTER_PLAN.md`, `docs/Old Blueprint/*.yaml`.
- Workflow: `docs/10_WORKFLOW_ENGINE_SPECIFICATION.md`.
- Service/event rules: `docs/11_SERVICE_CATALOG.md`, `docs/12_EVENT_CATALOG.md`.

## Decision Rules

- If the task changes business behavior, stop and verify frozen rules plus approval path.
- If the task changes tables/indexes/relationships, verify database frozen status and require ADR/explicit instruction.
- If UI and API need the same behavior, implement service first and call it from both.
- If a process is heavy or side-effect driven, use events/queue.
- If a task is Phase 0, do not create Laravel application code.

## Error Recovery

- Fail fast for invalid business state.
- Use specific exceptions for validation, authorization, business rule, integration, database, and infrastructure failures.
- Retry only transient failures: queue jobs, network, mail, notification.
- Do not retry business rule errors.
- Log with appropriate level and trace/correlation ID when available.
- Keep production messages safe and user-friendly.

## Self Validation Checklist

- [ ] Loaded only relevant context.
- [ ] Checked frozen business/database constraints.
- [ ] Preserved `/docs`.
- [ ] Preserved existing configuration and secrets.
- [ ] Used Service Layer for business logic.
- [ ] Applied Policy and Form Request where relevant.
- [ ] Considered workflow, audit, notification, API, and Flutter impact.
- [ ] Added or updated tests.
- [ ] Updated docs/checklists if behavior changed.
- [ ] No hardcoded secrets.
