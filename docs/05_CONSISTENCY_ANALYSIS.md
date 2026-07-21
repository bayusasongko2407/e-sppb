# 05_CONSISTENCY_ANALYSIS.md — E-SPPB Enterprise Consistency Analysis

> **Audit Type:** Cross-Layer Code Consistency Audit (Read-Only)  
> **Project:** E-SPPB Enterprise  
> **Date:** 2026-07-16  

---

## Executive Summary

A comprehensive scan matching migrations, database tables, Eloquent models, Policies, and Filament Resources was conducted to identify unused components, orphan tables, database anomalies, and dead code.

---

## Entity Mapping Matrix

| Entity / Concept | DB Table | Eloquent Model | Policy Class | Filament Resource | Status / Issues |
|---|---|---|---|---|---|
| User | `users` | `User` | `UserPolicy` | `UserResource` | Consistent |
| Plant | `plants` | `Plant` | `PlantPolicy` | `PlantResource` | Consistent |
| Department | `departments` | `Department` | `DepartmentPolicy` | `DepartmentResource` | Consistent |
| Location | `locations` | `Location` | `LocationPolicy` | `LocationResource` | Consistent |
| Unit | `units` | `Unit` | `UnitPolicy` | `UnitResource` | Consistent |
| Position | `positions` | `Position` | - | `PositionResource` | Policy is missing |
| Item | `items` | `Item` | - | `ItemResource` | Policy is missing |
| Asset | `assets` | `Asset` | `AssetPolicy` | `AssetResource` | Consistent |
| SppbHeader | `sppb_headers` | `SppbHeader` | `SppbHeaderPolicy` | `SppbHeaderResource` | Consistent |
| SppbDetail | `sppb_details` | `SppbDetail` | - | - | Handled inline |
| WorkflowTemplate | `workflow_templates` | `WorkflowTemplate` | `WorkflowTemplatePolicy` | `WorkflowTemplateResource` | Consistent |
| WorkflowInstance | `workflow_instances` | `WorkflowInstance` | - | `WorkflowInstanceResource` | Policy is missing |
| WorkflowInstanceStep| `workflow_instance_steps`| `WorkflowInstanceStep`| `WorkflowInstanceStepPolicy`| - | Consistent |
| WorkflowDelegation | `workflow_delegations`| `WorkflowDelegation`| - | `WorkflowDelegationResource` | Policy is missing |
| RunningNumber | `running_numbers` | `RunningNumber` | - | `RunningNumberResource` | Policy is missing |
| GoodsRelease | `goods_releases` | `GoodsRelease` | `GoodsReleasePolicy` | **MISSING** | No Filament UI found |
| ActivityLog | `activity_logs` | `ActivityLog` | - | **MISSING** | No Filament UI found |

---

## Database vs. Model Inconsistencies

1. **Email Change Request FK:**
   - `email_change_requests` references `users.id` via `approved_by_id` but lacks a foreign key index on the database level.
2. **Nullable Plant References in Master Templates:**
   - In `workflow_templates` and `document_templates`, `plant_id` is nullable. Although the resolvers handle this as a fallback mechanism, the database does not enforce strict tenancy.

---

## Policy & Permission Mismatches

- **Unused Permission Records:**
  - Migrations dropped legacy tables (such as `legacy_references`), but seeder logs or database permissions still populate permissions for `legacyreference` (e.g., `create_legacyreference`, `delete_legacyreference`).
- **Missing Resource Policies:**
  - `Position`, `Item`, `WorkflowInstance`, `WorkflowDelegation`, and `RunningNumber` models do not have associated Policy classes. In Filament v5, if a resource lacks a Policy, Filament defaults to checking general gate rules or grants open access if policies are globally skipped. This leaves access open unless permissions are manually mapped in panel configurations.

---

## Dead Code & Stubs

- **`ScopePlantMiddleware`:**
  - Registered inside routing arrays, but its class file contains no logic, making it a dead-weight middleware.
- **Unused Models:**
  - `LegacyReference` was cleaned up from the database and codebase. However, its factory file `LegacyReferenceFactory.php` is still present in the `database/factories/` directory.
