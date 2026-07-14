# Laravel Guide

## Baseline

Use Laravel 12 and PHP 8.3.

## Required Structure

- `app/Actions`
- `app/Services`
- `app/DTOs`
- `app/Enums`
- `app/Events`
- `app/Jobs`
- `app/Listeners`
- `app/Models`
- `app/Policies`
- `app/Repositories` only when justified

## Implementation Rules

- Controllers are thin.
- Filament Resources are thin.
- Business logic lives in Services.
- Use Form Requests for validation.
- Use Policies for authorization.
- Use Events for asynchronous business side effects.
- Use Queues for heavy work.
- Use database transactions for submit/approval and other critical state changes.
- Use specific exceptions: `DomainException`, `ValidationException`, `AuthorizationException`, `BusinessRuleException`.

## Service Method Pattern

Before writing a method, identify:

- Functional requirement ID.
- Business rule.
- Database impact.
- Workflow state impact.
- Audit event/status log.
- Domain event.
- API/UI caller.
- Tests.

## Status Handling

Use enums for stable status lists. Preserve documented status names unless approved otherwise.

## Repository Rule

Do not create repositories by default. Use Eloquent directly in services unless a repository removes real complexity, isolates an external data source, or matches an established local pattern.

