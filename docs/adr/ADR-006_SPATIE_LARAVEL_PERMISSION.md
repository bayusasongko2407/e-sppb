# ADR-006: Spatie Laravel Permission for Role-Based Authorization

- Status: Accepted
- Date: 2026-07-13
- Decision owners: User and Software Architecture
- Related requirements: FR-004 section 7, DOC-025 sections 3 and 11

## Context

E-SPPB requires the documented roles Admin, Requester, BAT, Manager, and Warehouse, permission-based authorization, and Laravel Policies. The frozen user blueprint does not define role and permission tables.

The user explicitly approved introducing Spatie Laravel Permission and its standard schema on 2026-07-13.

## Decision

Use `spatie/laravel-permission` version 6 with the standard non-team schema:

- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

Use the `web` guard and these stable permissions:

| Permission | Purpose |
|---|---|
| `master-data.manage` | Manage master data |
| `sppb.create` | Create SPPB |
| `sppb.approve` | Approve, reject, or request revision |
| `sppb.view-approved` | View approved SPPB |

The approved role access matrix is synchronized through `AuthorizationService`. Model access is enforced through Laravel Policies; UI visibility alone is not an authorization control. Inactive users are denied by master-data policies as defense in depth.

## Consequences

- The database gains the five standard Spatie authorization tables.
- Roles and permissions have centralized enum values and an idempotent seeder.
- Existing and future web/API callers can use the same permissions and policies.
- SPPB-specific policies will consume the reserved SPPB permissions when its frozen models are implemented.
- Teams are disabled because no team-scoped authorization rule has been approved.
