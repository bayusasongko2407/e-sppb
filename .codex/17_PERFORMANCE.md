# Performance Guide

## Targets

- Dashboard: under 2 seconds.
- Normal CRUD: under 1 second.
- General API target: under 500 ms.
- Heavy processes run via Queue.

## Laravel

- Eager loading.
- Avoid N+1.
- Cache config, route, view.
- Use Queue for heavy work.
- No business logic in Resource.

## Database

- Index according to query.
- Use `EXPLAIN`.
- Avoid `SELECT *`.
- Paginate large datasets.
- Keep transactions scoped.

## API

- Default pagination.
- Server-side filtering and sorting.
- Compact response.
- Stable versioning.
- Cache reference data.

## Queue Candidates

Email, notification, import, export, heavy report.

