# 07_RECOMMENDATION.md — E-SPPB Enterprise Recommendations

> **Audit Type:** Technical recommendations (Read-Only)  
> **Project:** E-SPPB Enterprise  
> **Date:** 2026-07-16  

---

## Executive Summary

Based on findings from Database, Source Code, API, Business Rules, and Consistency analyses, these recommendations address security, architectural alignment, and system stability.

---

## Prioritized Recommendations

### Priority 1: High Risk & Security Corrective Actions

#### 1. Implement Self-Approval Validation Check
- **Risk Level:** 🔴 High
- **Description:** Update `WorkflowService::queueApproval` to block approvals where the approver is the document requester and `allow_self_approval` is disabled.
- **Affected File:** `app/Services/WorkflowService.php`

#### 2. Create Missing Filament Policies
- **Risk Level:** 🟠 Medium
- **Description:** Implement Policies for the remaining resource models (`Position`, `Item`, `WorkflowInstance`, `WorkflowDelegation`, `RunningNumber`) to enforce granular permission checks.
- **Affected Directory:** `app/Policies/`

#### 3. Implement Global Plant Query Scopes
- **Risk Level:** 🟠 Medium
- **Description:** Replace the empty `ScopePlantMiddleware` with a tenant scoping mechanism. Use Eloquent Global Scopes on tenant-scoped models to restrict database queries by `plant_id`.
- **Affected File:** `app/Http/Middleware/ScopePlantMiddleware.php`

---

### Priority 2: Architectural Enhancements

#### 1. Build Goods Release Resource UI
- **Risk Level:** 🟠 Medium
- **Description:** Generate a Filament Resource (`GoodsReleaseResource`) and associated views to allow users to create and manage releases.
- **Affected Directory:** `app/Filament/Resources/`

#### 2. Clean Up Legacy Artifacts
- **Risk Level:** 🟢 Low
- **Description:** Remove the unused `LegacyReferenceFactory.php` file and delete orphan database permissions related to `legacyreference`.
- **Affected Directory:** `database/`

---

### Priority 3: Infrastructure & Scalability

#### 1. Configure Background Queue Workers
- **Risk Level:** 🟡 Low
- **Description:** Decouple command execution from web HTTP requests. Configure a persistent process runner (like Supervisor) to handle queued background jobs asynchronously.
- **Action:** Update queue configuration to process `WorkflowCommand` executions asynchronously.
