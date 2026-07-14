# Database Rules

## Governance

MariaDB is the primary DBMS. Schema is frozen. Do not rename tables, change columns, indexes, constraints, foreign keys, or relationships without explicit instruction and ADR/review.

Use `/docs/08_DATABASE_MASTER_PLAN.md` and `docs/Old Blueprint/*.yaml` before implementing migrations/models.

## Database Standards

- Charset: `utf8mb4`.
- Collation: `utf8mb4_unicode_ci`.
- ORM: Eloquent.
- Migration: Laravel Migration.
- Minimum normalization: 3NF.
- Referential integrity is required.
- Primary key on every table.
- Foreign keys indexed.
- Unique indexes for document numbers and stable unique codes.
- Composite indexes for frequent multi-column filters.
- Timestamps follow Laravel standard when applicable.
- Soft delete only if approved in design.

## Domain Table Map

- Organization: companies, plants, departments, locations, units, positions.
- Security: users, user_positions.
- Asset: items, assets, asset areas/history if implemented from approved design.
- Workflow: running_numbers, workflow_templates, workflow_steps, workflow_instances, workflow_instance_steps.
- Transaction: sppb_headers, sppb_details.
- Attachment: attachments.
- Audit: activity_logs, sppb_status_logs.
- Utility/Validation: document_validations, notifications.

Note: standards mention `tbl_<entity>` naming. Existing blueprint uses model-derived table names. Before implementation, verify exact frozen physical names from approved schema.

## Key Fields From Blueprint

### Organization

- Company: `code` unique, `name`, `description`, `is_active`.
- Plant: `company_id`, `code`, `name`, `description`, `is_active`; unique `company_id, code`.
- Department: `plant_id`, `code`, `name`, `description`, `is_active`; unique `plant_id, code`.
- Location: `code` unique, `name`, `address`, `description`, `is_active`.
- Unit: `code` unique, `name`, `description`, `is_active`.
- Position: `code` unique, `name`, `description`, `is_active`.

### Security

- User: `company_id`, `plant_id`, `department_id`, `nik` unique, `name`, `email` unique nullable, `password`, `last_login_at`, `is_active`.
- UserPosition: `user_id`, `position_id`, `is_primary`, `is_active`; unique `user_id, position_id`.

### Item and Asset

- Item: `code` unique, `name`, `specification`, `unit_id`, `item_type` enum `ASSET, NON_ASSET`, `is_active`.
- Asset: `item_id`, `company_id`, `plant_id`, `location_id`, `barcode` unique, `serial_number`, `condition` enum `GOOD, FAIR, DAMAGED, SCRAP`, `status` enum `AVAILABLE, RESERVED, IN_USE, REPAIR, DISPOSED`, `notes`, `is_active`.

### SPPB

- SppbHeader includes organization references, requester, workflow instance, current workflow step/approver, `document_uuid` unique, `document_number` unique, request locations, requester name, purpose, status, revision fields, totals, attachment count, submitted/approved/rejected/cancelled/pdf timestamps and users.
- SppbHeader statuses from blueprint: `DRAFT`, `SUBMITTED`, `WAITING_BAT`, `WAITING_MANAGER`, `APPROVED`, `REJECTED`, `CANCELLED`.
- SppbDetail includes `sppb_header_id`, `line_no`, optional `asset_id`, optional `item_id`, `item_type`, barcode/item code/name/specification, unit, quantity, approved quantity, delivered quantity, remarks; unique `sppb_header_id, line_no`.
- SppbStatusLog includes `sppb_header_id`, optional `workflow_step_id`, optional `user_id`, `action`, `status`, `remarks`, `logged_at`.

### Workflow and Utility

- RunningNumber: optional `company_id`, optional `plant_id`, `document_type`, `prefix`, `reset_type` enum `NONE, YEARLY, MONTHLY`, `digit`, `last_number`, `is_active`; unique `company_id, plant_id, document_type`.
- WorkflowTemplate: `code` unique, `name`, `description`, `is_active`.
- WorkflowStep: `workflow_template_id`, `sequence`, `position_id`, `step_name`, `is_final`; unique `workflow_template_id, sequence`.
- WorkflowInstance: `workflow_template_id`, `reference_type`, `reference_uuid`, `current_step`, status enum `DRAFT, IN_PROGRESS, APPROVED, REJECTED, CANCELLED`, `started_at`, `finished_at`.
- WorkflowInstanceStep: `workflow_instance_id`, `workflow_step_id`, `approver_id`, status enum `PENDING, APPROVED, REJECTED, SKIPPED`, `remarks`, `approved_at`.

### Attachment, Notification, Audit, Validation

- Attachment: `sppb_header_id`, optional `sppb_detail_id`, `original_name`, `stored_name` unique, disk/directory/path/mime/extension/file_size/checksum, `uploaded_by`.
- Notification: `user_id`, `notification_type` enum `SPPB, WORKFLOW, SYSTEM, INFO`, `title`, `message`, `url`, `is_read`, `read_at`.
- ActivityLog: optional `user_id`, `module`, `action`, `description`, optional `reference_type`, optional `reference_id`, `ip_address`, `user_agent`.
- DocumentValidation: `sppb_header_id`, `uuid` unique, `qr_code`, `expires_at`.

## Transaction Rules

- Submit SPPB must use DB transaction.
- Approval must use DB transaction.
- Roll back when workflow generation fails.
- Audit must be written before commit completes when relevant.
- Maintain ACID consistency.

## Performance Rules

- Avoid N+1.
- Use eager loading.
- Use pagination for large datasets.
- Use `EXPLAIN` for query analysis.
- Avoid `SELECT *` in performance-critical queries.

