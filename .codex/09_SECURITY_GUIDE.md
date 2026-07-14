# Security Guide

## Principles

Security by Design, Least Privilege, Defense in Depth, Zero Trust principles, Secure Defaults, Auditability.

## Identity and Access

- Login uses Email or NIK.
- Inactive users cannot login.
- Role & Permission required.
- Laravel Policies required.
- Important activities are audited.
- Authorization roles and permissions use the approved Spatie Laravel Permission standard schema documented in `docs/adr/ADR-006_SPATIE_LARAVEL_PERMISSION.md`.
- Master-data model access requires `master-data.manage` through Laravel Policies; inactive users are denied.

## Application Security

- Validate every input.
- Use CSRF protection for web.
- Escape output and prevent XSS.
- Use parameter binding to prevent SQL injection.
- Validate upload type, size, extension, storage path, and access.

## API Security

- HTTPS only.
- Bearer token authentication.
- Role-based authorization.
- Rate limiting.
- API versioning.
- Audit sensitive endpoints.

## Secret Rules

Do not commit secrets. Do not regenerate credentials unless explicitly instructed. Preserve existing configuration. Keep APP_KEY, DB password, Redis password, mail credentials, and API keys in environment.

## Logging

Never log password, token, secret, or sensitive payload. Use suitable log levels and trace/correlation ID where available.
