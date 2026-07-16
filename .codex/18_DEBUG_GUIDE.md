# Debug Guide

## First Checks

- Confirm environment.
- Confirm current release/branch.
- Check application log.
- Check audit/security log if business/security issue.
- Check queue worker.
- Check scheduler.
- Check database and Redis connectivity.
- Check storage permissions.

## Error Categories

Validation, Business Rule, Authorization, Authentication, Integration, Database, Infrastructure.

## Exception Rules

- Use specific exceptions.
- Do not use broad `catch (Exception)` without reason.
- Do not expose stack traces in production.
- Map exceptions to HTTP status consistently.

## Retry

Retry only transient queue/network/mail/notification failures. Do not retry business rule errors.

## Logs

Use proper levels. Include trace/correlation ID when available. Never log secrets.

