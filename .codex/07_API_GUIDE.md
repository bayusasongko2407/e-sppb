# API Guide

## Principles

API First, RESTful, stateless, versioned, secure by default, consistent contract, reusable Service Layer.

## Versioning

Base URL: `/api/v1`.

Breaking changes require a new version such as `/api/v2`.

## Endpoint Convention

- Collection: `GET /api/v1/sppb`.
- Detail: `GET /api/v1/sppb/{id}`.
- Create: `POST /api/v1/sppb`.
- Update: `PUT /api/v1/sppb/{id}`.
- Delete: `DELETE /api/v1/sppb/{id}` when allowed.
- Action: `POST /api/v1/sppb/{id}/submit`.

Workflow actions:

- `POST /sppb/{id}/submit`
- `POST /workflow/{id}/approve`
- `POST /workflow/{id}/reject`
- `POST /workflow/{id}/revision`
- `GET /workflow/{id}`

## Response Contract

Successful/error responses use:

- `success`
- `message`
- `data`
- `meta` optional
- `errors` when applicable

Error responses may include `error_code` and `trace_id`.

## HTTP Status Codes

Use 200, 201, 204, 400, 401, 403, 404, 409, 422, 500 consistently.

## Query Parameters

- Pagination: `page`, `per_page`.
- Filter example: `status=approved`.
- Sorting: `sort=created_at`, `direction=desc`.
- Search: `search=keyword`.

## Security

- Bearer token authentication.
- Role/Permission authorization.
- Rate limiting.
- HTTPS only.
- Audit sensitive endpoints.
- Validate files and all input.

## Testing

API features require feature/contract/regression tests for critical paths.

