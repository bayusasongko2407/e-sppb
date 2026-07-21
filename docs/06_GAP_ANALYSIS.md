# 06_GAP_ANALYSIS.md — E-SPPB Enterprise Gap Analysis

> **Audit Type:** Architectural Gap Analysis (Read-Only)  
> **Project:** E-SPPB Enterprise  
> **Date:** 2026-07-16  

---

## Executive Summary

This document highlights the functional and structural gaps between the actual codebase implementation and standard enterprise patterns expected for a secure, highly scalable system.

---

## Technical & Architectural Gaps

### 1. Absence of REST API Layer
- **Status:** **NOT IMPLEMENTED**
- **Impact:** While the backend is built to support transactions, there are no endpoints for external systems or mobile apps. Integrating a mobile application would require rebuilding authentication (e.g., Sanctum/Passport) and setting up API routing.

### 2. Missing Goods Release UI
- **Status:** **NOT IMPLEMENTED** in Filament.
- **Impact:** The `GoodsRelease` model, migrations, and `GoodsReleaseService` exist, but no `GoodsReleaseResource` or related UI exists in Filament. Users cannot create, view, or dispatch goods releases through the web interface.

### 3. Synchronous Queue Execution
- **Status:** **MISALIGNED**
- **Impact:** Queue commands are placed in a database store but processed synchronously in the same request cycle due to the absence of active background workers. This blocks web workers on complex status changes or generation tasks.

---

## Security & Compliance Gaps

### 1. Ineffective Plant Tenancy Scoping
- **Status:** **PARTIAL**
- **Impact:** `ScopePlantMiddleware` is an empty stub. While the services filter results manually using `plant_id`, the system lacks a global query scope to prevent cross-plant data exposures.

### 2. Missing Model Policies
- **Status:** **PARTIAL**
- **Impact:** `Position`, `Item`, `WorkflowInstance`, `WorkflowDelegation`, and `RunningNumber` lack Policy classes, leaving access controls on these models dependent on global panel configurations instead of granular class-level checks.

### 3. Unconstrained Self-Approval
- **Status:** **VULNERABILITY**
- **Impact:** The `allow_self_approval` property is defined on workflow steps but is not enforced by `WorkflowService`. Requesters who resolve as valid approvers can approve their own requests.

---

## Database Integrity Gaps

- **Missing Foreign Key Constraints:**
  - `email_change_requests.approved_by_id` has no database-level foreign key constraint.
- **Legacy Artifacts:**
  - Orphan permissions (e.g. `create_legacyreference`) and dead factory code (`LegacyReferenceFactory.php`) remain in the codebase.
- **Email Notifications Not Configured:**
  - System notifications are written only to the database. External communication channels (email/SMS) are not configured.
