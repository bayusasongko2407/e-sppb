# 01_DATABASE_ANALYSIS.md — E-SPPB Enterprise Database Analysis

> **Audit Type:** Database & Migrations Deep Dive (Read-Only)  
> **Project:** E-SPPB Enterprise  
> **Date:** 2026-07-16  

---

## Executive Summary

The database uses MariaDB/MySQL (`e_sppb_enterprise`) with the InnoDB engine. It contains 47 tables, implementing relational constraints, composite keys, unique indexes, and structured enumerations. While the schema is highly consistent with standard transactional setups, there are minor gaps such as missing foreign key references for some historical log tables, duplicate index patterns, and cleanups from legacy tables that were dropped by migrations but left some unused permission records behind.

---

## Database Engine & Metadata

- **DBMS Connection:** MySQL/MariaDB
- **Storage Engine:** InnoDB (all tables)
- **Character Set:** `utf8mb4_unicode_ci` (Laravel standard)
- **Primary Schema Owner:** `e_sppb_enterprise`
- **Total Tables:** 47

---

## Complete Table Inventory

| Table Name | Description | Rows (Approx) | Keys / Constraints |
|---|---|---|---|
| `users` | Auth and corporate users, manager reference, theme options. | 6 | PK, Unique (Email, NIK), FK (Plants, Departments) |
| `plants` | High-level organizational units (tenants/factories). | 4 | PK, Unique (Code) |
| `departments` | Organizational divisions. | 2 | PK, Unique (Code) |
| `positions` | Corporate titles and ranks. | 7 | PK, Unique (Code) |
| `user_positions` | N-to-N join for users and positions, tracking primary position. | 6 | PK, Unique (User, Position) |
| `locations` | Physical locations within a Plant (origin/destination). | 2 | PK, Unique (Code) |
| `units` | Measurement units (e.g. Kg, Pcs, Ton). | 26 | PK, Unique (Code) |
| `items` | Standard inventory goods, categorization. | 30 | PK, Unique (Code), FK (Units) |
| `assets` | Trackable enterprise equipment assets. | 1 | PK, Unique (Code), FK (Locations) |
| `sppb_headers` | SPPB requests, lifecycle state, tracking workflow. | 5 | PK, Unique (SPPB number), FK (Users, Plants, Departments) |
| `sppb_details` | Line items for SPPBs, references to Items or Assets. | 6 | PK, FK (Headers, Items, Assets, Units) |
| `sppb_status_logs` | Audit trail for SPPB transitions and actors. | 27 | PK, FK (Headers, Users, Instances, Steps) |
| `workflow_templates` | Blueprint configurations for workflow routing. | 2 | PK, Unique (Code + Version) |
| `workflow_steps` | Steps within a template, approver rules, configurations. | 4 | PK, Unique (Template + Code) |
| `workflow_instances` | Runs of a workflow template bound to an SPPB revision. | 5 | PK, Unique (SPPB + Revision), UUID |
| `workflow_instance_steps` | Executed states of workflow steps for an instance. | 10 | PK, Unique (Instance + Sequence) |
| `workflow_step_approvers` | Direct approver assignments and delegation notes. | 11 | PK, Unique (Step + Approver) |
| `workflow_delegations` | Out-of-office delegation mappings for approval authority. | 0 | PK, Multi-indexes |
| `workflow_commands` | Command store/journal for CQRS/idempotent processing. | 13 | PK, Unique UUID |
| `goods_releases` | Gate releases for SPPBs, receiver/sender details. | 0 | PK, Unique number, UUID |
| `goods_release_items` | Gate release quantities, checking condition. | 0 | PK, FK (Release, Detail) |
| `document_templates` | Generation blueprints (PDF layout configs). | 1 | PK, Unique code, UUID |
| `document_generations` | Rendered output records, hashes, expiration logs. | 26 | PK, Unique stored name, UUID |
| `document_pages` | QR codes validation and checksum mappings per page. | 25 | PK, Unique UUID, unique QR payload checksum |
| `document_validations` | QR scans verification history, device fingerprints. | 8 | PK, Unique UUID, correlation ID |
| `document_accesses` | Scope permissions mapped by user, module, plant. | 33 | PK, Multi-column FK |
| `email_change_requests` | Verification log for changing corporate emails. | 1 | PK, FK (Users) |
| `enum_controls` | System-wide drop-down values cache mapping translation labels. | 15 | PK, Indexed categories |
| `roles` / `permissions` | Spatie RBAC system tables. | 6 / 194 | Standard Spatie structure |
| `activity_logs` | Generic system event audits. | 0 | PK, Multi-indexes |
| `cache` / `sessions` | Operational store for app performance. | - | Core Laravel structure |
| `jobs` / `failed_jobs` | Queue management. | - | Core Laravel structure |

---

## Relationship Analysis

- **Plant Constraints:** Plant is the highest organizational boundary. All master tables (`locations`, `users`, `workflow_templates`, `document_templates`) reference `plant_id` via RESTRICT foreign keys. This guarantees no orphans are created.
- **Cascading Rules:** 
  - Relational mapping tables like `role_has_permissions`, `model_has_roles`, and `model_has_permissions` have `ON DELETE CASCADE` on their role/permission FKs.
  - Transactional tables (`sppb_details`, `workflow_step_approvers`, `goods_release_items`) strictly use `ON DELETE RESTRICT` to ensure transaction history cannot be deleted if child entries exist.
- **Missing Relations / Missing FKs:**
  - `email_change_requests.approved_by_id` references `users.id` but has no foreign key constraint declared in migrations.
  - `activity_logs` uses generic columns without strict relational constraints to support polymorphic structures.

---

## Indexes & Performance Optimization

- **Unique Composite Constraints:**
  - `workflow_templates`: Unique index on `(code, version)`.
  - `workflow_instance_steps`: Unique index on `(workflow_instance_id, sequence)`.
  - `workflow_step_approvers`: Unique composite index `idx_wf_step_approver_unique` on `(workflow_instance_step_id, approver_id)`.
  - `user_positions`: Unique composite index on `(user_id, position_id)`.
- **Indexing Coverage:** 
  - `workflow_delegations` includes composite indexes on active delegators: `(delegator_id, starts_at, ends_at, is_active)` and `(delegate_id, starts_at, ends_at, is_active)`. This is highly optimized for fast lookup during active workflow processes.

---

## Detected Database Schema Issues

1. **Missing Foreign Key Constraints:**
   - `email_change_requests` references `approved_by_id` back to `users(id)` without a defined database-level foreign key constraint.
2. **Orphan Permissions:**
   - Segenap permission records referencing a legacy model `LegacyReference` (e.g. `create_legacyreference`, `view_legacyreference`) are still stored in the `permissions` table, although the actual migrations dropped the `legacy_references` table (refer to migration `2026_07_15_110645_cleanup_legacy_tables_and_columns.php`).
3. **Nullable Plant References:**
   - In `workflow_templates` and `document_templates`, `plant_id` is nullable. While this allows global template fallbacks (resolved by code), it raises the risk of accidental cross-plant data exposures if queries fail to enforce boundaries.
