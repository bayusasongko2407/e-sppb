# Troubleshooting Guide

## Incident Flow

Detect -> Alert -> Investigate -> Mitigate -> Recover -> Post Incident Review.

## Common Areas

- HTTP/Nginx/PHP-FPM availability.
- Laravel errors.
- MariaDB connectivity/constraints.
- Redis connectivity.
- Queue backlog/failure.
- Scheduler inactivity.
- Storage write/access issue.
- Authentication/authorization failure.
- Workflow invalid transition.
- Concurrent approval.
- Missing workflow template.
- SLA configuration missing.

## Recovery Priorities

1. Authentication.
2. Workflow.
3. SPPB.
4. Reporting.
5. Dashboard.

## Backup/DR Targets

- RPO: <= 24 hours.
- RTO: <= 4 hours.

## Restore Validation

Login, queue, scheduler, API, dashboard, and attachment access must be verified.

