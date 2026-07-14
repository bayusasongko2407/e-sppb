# Git Workflow

## Branch Model

- `main`: production.
- `develop`: integration.
- `feature/*`: feature development.
- `release/*`: release preparation.
- `hotfix/*`: production fixes.

## Branch Naming

- `feature/sppb-approval`
- `feature/master-asset`
- `release/v1.0.0`
- `hotfix/login-error`

## Commit Convention

Use Conventional Commits:

- `feat:`
- `fix:`
- `refactor:`
- `docs:`
- `test:`
- `chore:`
- `perf:`
- `ci:`

## Pull Request Requirements

Every PR includes:

- Summary.
- Related documents.
- Database impact.
- API impact.
- Test results.

Minimum one reviewer. Status checks must pass. Direct push to protected branches is prohibited.

## Merge and Release

- Squash merge for small features.
- Merge commit for release.
- Hotfix goes to `main`, then back-merge to `develop`.
- Use semantic versioning.

