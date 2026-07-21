# 02_SOURCE_ANALYSIS.md — E-SPPB Enterprise Source Code Analysis

> **Audit Type:** Source Code Architecture Deep Dive (Read-Only)  
> **Project:** E-SPPB Enterprise  
> **Date:** 2026-07-16  

---

## Executive Summary

The backend utilizes Laravel 12 features (e.g. native type declarations, context-aware service mappings) structured around an Enterprise Domain-Driven Service layer. Controller footprints are minimal, delegating all domain operations to transaction-wrapped Domain Services (`SppbService`, `WorkflowService`, `GoodsReleaseService`, `DocumentGenerationService`). Security is strictly enforced using Gates, Policies, and multi-tenant scoping.

---

## Models Analysis

There are 26 Eloquent models mapped to transactional and master tables.
- **Key Patterns:**
  - **UUIDs:** Many models (`SppbHeader`, `WorkflowInstance`, `WorkflowTemplate`, `GoodsRelease`, `DocumentTemplate`, `DocumentGeneration`, `DocumentPage`, `DocumentValidation`) use the `Illuminate\Database\Eloquent\Concerns\HasUuids` trait for secure identifier exposure.
  - **Cast Mapping:** Strict type casting is configured for standard types, booleans (`is_active`, `is_urgent`, `is_manual`), and timestamps (`effective_from`, `due_at`, `acted_at`).
  - **Accessors:** `SppbHeader` provides accessors to expose business parameters cleanly (e.g. matching `sppb_number` attributes).
  - **Security Binding:** Uses a custom `SecureRouteBinding` trait to prevent ID-enumeration vulnerabilities on model resolution in public routes.

---

## Service Layer & Domain Logic

1. **`SppbService`:**
   - **Responsibility:** Draft creation, draft modifications, detail attachments, and initial routing.
   - **Transaction Management:** All operations use DB transactions (`DB::transaction`) to ensure atomic execution. It prevents adding details to non-editable documents.
2. **`WorkflowService`:**
   - **Responsibility:** Workflow template resolution, workflow instance execution, approval step routing, status logs writing, and event notifications.
   - **Execution Model:** Contains a Command journal (`WorkflowCommand`) to guarantee execution idempotence. Since there are no background daemon processors configured, command execution runs synchronously inside transactions.
3. **`ApproverResolver`:**
   - **Responsibility:** Resolving candidates by role, user ID, corporate position, or requester's manager chain.
   - **Tenancy Boundary:** Filters resolved users by `plant_id`. If `document_accesses` mappings are present, it checks `hasDocumentAccess` logic; otherwise, it falls back to basic `plant_id` matching.

---

## Authentication & Authorization Architecture

- **Authentication Service (`AuthService`):**
  - Allows authentication via either NIK (Nomor Induk Karyawan) or Corporate Email.
  - Implements account lockout rules: 5 failed attempts locks the account for 15 minutes. Lock is evaluated atomically.
  - Checks if the user's account is marked active (`is_active`). If not active, logins are rejected.
- **Authorization & Policies:**
  - Strict mapping between model actions and Spatie permission records.
  - `AppServiceProvider` contains a super admin gate override: `Gate::before(fn ($user) => $user->hasRole('super_admin') ? true : null)`.
  - Tenancy policies are reinforced by scoping resource listings.

---

## Middleware & Handlers

- **`EnsureUserIsActive`:** Logs out any authenticated user whose status is changed to inactive during an active session.
- **`EnsureCorrelationId`:** Generates or passes down a correlation UUID in the `X-Correlation-ID` header. Useful for tracing async events and logs across different processes.
- **`ScopePlantMiddleware`:** Placed in global routing configuration but contains only an empty template stub. It does not actively enforce any active session scope.

---

## Job & Queue Processors

There are 4 active Job classes:
1. `ProcessDocumentGenerationJob` — Generates official PDF documents. It runs synchronously inside the `SppbPreviewController` to ensure immediate preview availability.
2. `GenerateDataExportJob` — Handles background data exports.
3. `ProcessDataImportJob` — Processes background master data spreadsheet imports.
4. `PurgeExpiredDocumentExportJob` — Scheduled task cleaning up expired document exports.

---

## Trait Analysis

- **`SecureRouteBinding`:** A custom trait applied to core tenant-facing entities (`SppbHeader`, `WorkflowTemplate`, `GoodsRelease`). It overrides default model route binding lookup to resolve models via UUIDs instead of auto-incrementing integer IDs.

---

## Detected Source Issues

1. **Empty Stub Middleware:**
   - `ScopePlantMiddleware` is registered in routing pipelines but does not contain actual logic.
2. **Synchronous Execution of Queue Logic:**
   - `WorkflowService::queueSubmission` and `queueApproval` execute their internal methods (`generateWorkflow`, `approve`, `reject`) synchronously within the same HTTP thread immediately after command queueing. This negates the scalability advantage of the CQRS command table.
3. **Implicit Dependency on First Template:**
   - In `SppbPreviewController::preview`, if no document template matches the current plant, it falls back to the first available template in the database, risking cross-plant configuration bleeding.
