# Code Style

## PHP

- PSR-1, PSR-4, PSR-12.
- Strict typing for new files when possible.
- Use enums for stable statuses.
- Constructor property promotion where appropriate.
- Specific exceptions.

## Naming

- Class: `PascalCase`.
- Method: `camelCase`.
- Variable: `camelCase`.
- Constant: `UPPER_CASE`.
- Migration: `snake_case`.
- Table: verify approved schema; documented standard is `tbl_*`.
- View: `vw_*`.
- Foreign key: `<table>_id`.
- Boolean: `is_<name>`.

## Rules

- One responsibility per class.
- Avoid duplicated code.
- Avoid static helper business logic.
- No query in View/Resource.
- Use eager loading.
- Use transaction for critical processes.

