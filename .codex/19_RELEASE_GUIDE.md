# Release Guide

## Release Strategy

Use semantic versioning, release candidates, production releases, hotfix branches, and patch releases.

## Release Flow

`feature/*` -> `develop` -> `release/*` -> `main` -> tag -> production.

## Entry Criteria

- Requirement approved.
- Implementation complete.
- Build succeeds.
- Documentation updated.

## Exit Criteria

- No critical bugs.
- Tests pass.
- UAT approved where required.
- Release checklist complete.

## Post Deployment

- Login verified.
- Dashboard visible.
- Queue running.
- Scheduler active.
- Upload works.
- REST API validated.
- Logs free of critical errors.

