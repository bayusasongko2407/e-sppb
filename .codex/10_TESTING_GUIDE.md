# Testing Guide

## Testing Pyramid

1. Unit tests.
2. Feature tests.
3. Integration tests.
4. End-to-end/UAT.

## Required Coverage Focus

- Service Layer.
- Workflow transitions.
- SPPB submit/approval/revision/rejection.
- API contracts.
- Authorization and validation.
- Queue/listener behavior where side effects matter.

## Test Types

- Unit: services, helpers, domain logic.
- Feature: HTTP, workflow, SPPB.
- Integration: database, queue, mail, storage.
- API: REST contract.
- UI: Filament behavior where practical.
- Regression: affected modules.
- UAT: business validation.

## Test Data

- Use separate test database.
- Use controlled deterministic seeders.
- Never use production data.
- Use temporary storage and mail sandbox.

## Quality Gates

Static Analysis -> Pint -> PHPUnit -> Feature Test -> API Test -> Manual Verification.

## Exit Criteria

- No critical bugs.
- Tests pass.
- UAT approved when required.
- Release checklist complete.

