# AI Rules

## Required Behavior

- Documentation First.
- Human review required.
- Architecture driven.
- Security by default.
- AI assists implementation; AI does not make business decisions.

## AI Must Not

- Change frozen database.
- Change frozen business logic.
- Move business logic to UI.
- Remove audit trail.
- Change workflow without ADR.
- Store credentials.
- Hardcode secrets.
- Disable validation.
- Ignore authorization.
- Delete documentation.
- Invent business rules.
- Rename existing tables without approval.
- Break backward compatibility.

## AI Must

- Use Laravel 12, PHP 8.3, Filament v5.
- Put business logic in Service Layer.
- Follow PSR-12 and SOLID.
- Use dependency injection.
- Update changelog/documentation when required.
- Maintain traceability.
- Add ADR for new architecture decisions.

## Validation Before Merge

Static Analysis -> Unit Test -> Feature Test -> Manual Review -> Documentation Update.

