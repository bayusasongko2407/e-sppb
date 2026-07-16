# Project Memory

## Current Phase

Phase 5: Workflow Engine (workflow template and step configuration completed; instance generation pending approver-resolution rule).

## Completed

- Laravel application files were removed before Phase 0.
- `/docs` was preserved as source of truth.
- Existing Codex provider configuration exists at `.codex/config.toml` and must be preserved.
- Phase 0 repository preparation structure is present.
- Laravel 12 and Filament v5 dependencies are installed.
- The foundation architecture directories are present.
- A versioned health endpoint is available at `/api/v1/health`; Laravel's liveness endpoint remains at `/up`.
- API responses use a centralized `success`, `message`, `data`/`errors`, and `error_code` contract.
- API requests receive an `X-Correlation-ID`; valid client UUIDs are preserved and invalid/missing values are regenerated.
- API exceptions are centrally mapped to safe HTTP responses with a `trace_id`; internal exception details are not exposed.
- The API middleware group uses the named `api` limiter at 60 requests per minute per authenticated user or client IP.
- Runtime log files are created with group-write permission so PHP-FPM and CLI workflows can share them.
- API requests are logged with structured, non-sensitive access context including route, status, duration, client IP, and correlation ID.
- Interrupted work is tracked in `memory/continuation-log.md` with an active checkpoint and append-only history.
- Organization master data uses the approved Laravel plural physical tables `companies`, `plants`, and `departments`.
- Company, Plant, and Department migrations, Eloquent relationships, and service-layer CRUD are implemented with frozen blueprint fields and constraints.
- Location, Unit, and Position migrations, models, and service-layer CRUD are implemented with frozen blueprint fields, lengths, defaults, and indexes.
- User organization references, UserPosition assignments, Eloquent relationships, and `UserService` CRUD are implemented according to the frozen security blueprint.
- Item and Asset migrations, backed enums, Eloquent relationships, and `AssetService` CRUD are implemented according to the frozen item blueprint.
- Filament authentication supports Email or NIK, rejects inactive users, enforces active panel access, and records successful login timestamps.
- Spatie role and permission authorization is implemented with the approved Admin, Requester, BAT, Manager, and Warehouse access matrix and master-data policies.
- Workflow template and workflow step migrations, models, Position relationships, and service-layer CRUD are implemented from the frozen system blueprint.
- Notification service and model implemented with `NotificationType` enum.

## Durable Constraints

- Do not overwrite existing credentials.
- Do not regenerate database credentials.
- Do not delete documentation.
- Continue to preserve documentation and frozen business/database rules during foundation work.
