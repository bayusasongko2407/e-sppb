
---
document_id: DOC-004
title: Functional Requirement Specification
version: 3.0.0
status: Draft
classification: Internal
owner: Product Owner
reviewer: Software Architect
approver: Steering Committee
documentation_level: Enterprise Level 5
depends_on:
  - DOC-001
  - DOC-002
  - DOC-003
---

# E-SPPB Enterprise
# FUNCTIONAL REQUIREMENT SPECIFICATION

> Dokumen ini mendefinisikan seluruh kebutuhan fungsional sistem yang akan menjadi acuan desain, implementasi, pengujian, dan REST API.

# 1. Executive Summary

Functional Requirement Specification (FRS) menerjemahkan kebutuhan bisnis menjadi fungsi sistem yang dapat diimplementasikan.

Semua fungsi harus konsisten dengan database dan business logic yang telah berstatus Frozen.

---

# 2. Scope

Dokumen ini mencakup:

- Authentication
- Authorization
- Master Data
- Workflow Engine
- SPPB
- Attachment
- Validation
- Notification
- Dashboard
- Reporting
- Audit Trail
- REST API Readiness

---

# 3. Functional Modules

| Module ID | Module | Priority |
|-----------|--------|----------|
| MOD-001 | Authentication | Must |
| MOD-002 | Authorization | Must |
| MOD-003 | Master Data | Must |
| MOD-004 | Workflow Engine | Must |
| MOD-005 | SPPB Transaction | Must |
| MOD-006 | Attachment | Must |
| MOD-007 | Validation & QR | Should |
| MOD-008 | Dashboard | Should |
| MOD-009 | Reporting | Should |
| MOD-010 | Notification | Should |
| MOD-011 | Audit Trail | Must |
| MOD-012 | REST API | Must |

---

# 4. Functional Requirements

| ID | Requirement | Module | Priority |
|----|-------------|--------|----------|
| FR-001 | Login menggunakan Email atau NIK | Authentication | Must |
| FR-002 | Validasi status pengguna aktif | Authentication | Must |
| FR-003 | Kelola master plant | Master Data | Must |
| FR-004 | Kelola master department | Master Data | Must |
| FR-005 | Kelola master asset | Master Data | Must |
| FR-006 | Membuat Draft SPPB | SPPB | Must |
| FR-007 | Submit SPPB | SPPB | Must |
| FR-008 | Generate workflow approval | Workflow | Must |
| FR-009 | Approve / Reject / Revision | Workflow | Must |
| FR-010 | Upload lampiran | Attachment | Must |
| FR-011 | Riwayat approval | Audit | Must |
| FR-012 | Dashboard eksekutif | Dashboard | Should |
| FR-013 | Export PDF / Excel | Reporting | Should |
| FR-014 | REST API tersedia untuk seluruh proses | API | Must |

---

# 5. Functional Flow

Requester
↓
Draft
↓
Submit
↓
Workflow
↓
Approval
↓
Validation
↓
Completed

---

# 6. CRUD Matrix

| Module | C | R | U | D |
|--------|:-:|:-:|:-:|:-:|
| Plant | ✔ | ✔ | ✔ | ✔ |
| Department | ✔ | ✔ | ✔ | ✔ |
| Asset | ✔ | ✔ | ✔ | ✔ |
| SPPB Header | ✔ | ✔ | ✔ | ✖ |
| SPPB Detail | ✔ | ✔ | ✔ | ✖ |
| Attachment | ✔ | ✔ | ✔ | ✔ |

---

# 7. Role Access Matrix

| Function | Admin | Requester | BAT | Manager | Warehouse |
|----------|:----:|:---------:|:---:|:-------:|:---------:|
| Master Data | ✔ | ✖ | ✖ | ✖ | ✖ |
| Create SPPB | ✖ | ✔ | ✖ | ✖ | ✖ |
| Approval | ✖ | ✖ | ✔ | ✔ | ✖ |
| View Approved | ✔ | ✔ | ✔ | ✔ | ✔ |

---

# 8. Business Rules Mapping

- BR-001 → FR-003 s.d. FR-005
- BR-002 → FR-007 s.d. FR-009
- BR-003 → FR-011
- BR-004 → FR-011
- BR-005 → FR-010
- BR-006 → FR-012
- BR-007 → FR-014

---

# 9. Requirement Traceability Matrix

Business Objective
↓
Business Requirement
↓
Functional Requirement
↓
Service Layer
↓
REST API
↓
UI
↓
Testing

---

# 10. Acceptance Criteria

- Semua FR memiliki ID unik.
- Seluruh kebutuhan bisnis dipetakan.
- Seluruh fungsi dapat diuji.
- Siap menjadi dasar System Architecture dan desain Service Layer.

---

# 11. Future Expansion

- Flutter Android
- Push Notification
- Offline Synchronization
- Multi Company
- ERP Integration

---

# Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Enterprise Functional Requirement Specification|
