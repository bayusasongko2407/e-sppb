# Deployment Guide

## Environments

Local, Development, Staging, Production.

## Target Infrastructure

Ubuntu Server LTS, Nginx, PHP-FPM 8.3, Laravel 12, MariaDB, Redis, Supervisor, Cron, GitHub repository.

## Workflow

1. Pull latest release.
2. Composer install.
3. Node build if needed.
4. Run migrations only under approved database policy.
5. Cache config/route/view.
6. Restart queue.
7. Health check.
8. Go-live verification.

## Health Checks

- HTTP availability.
- Database connectivity.
- Redis connectivity.
- Queue worker active.
- Scheduler active.
- Storage writable.
- Disk usage acceptable.

## Security

HTTPS only, firewall active, least privilege, SSH key authentication, Laravel file permissions.

## Rollback

Use Git tag/release. Roll back migrations only according to database policy. Document rollback.

