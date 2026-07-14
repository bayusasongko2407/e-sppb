
---
document_id: DOC-007
title: Domain Model
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: Software Architect
reviewer: Lead Backend Engineer
approver: Steering Committee
depends_on:
  - DOC-006
---

# E-SPPB Enterprise
# DOMAIN MODEL

## 1. Purpose

Dokumen ini mendefinisikan domain bisnis utama, bounded context, hubungan antardomain, dan tanggung jawab masing-masing domain sebagai fondasi implementasi Laravel 12 + Filament v5.

---

## 2. Domain Principles

- High Cohesion
- Low Coupling
- Single Responsibility
- Domain-centric
- Business Logic berada pada Service Layer

---

## 3. Domain Landscape

### Core Domains
- SPPB
- Workflow
- Asset

### Supporting Domains
- Organization
- Security
- Attachment
- Notification
- Reporting
- Audit
- Validation

### Generic Domains
- Configuration
- Running Number
- Logging
- File Storage

---

## 4. Bounded Context

| Domain | Responsibility |
|---------|----------------|
| Organization | Plant, Department, Unit |
| Security | User, Role, Permission |
| Asset | Master Asset & Import |
| Workflow | Approval Engine |
| SPPB | Header, Detail, Status |
| Attachment | File Management |
| Reporting | Dashboard & KPI |
| Audit | Activity & History |

---

## 5. Domain Dependency

```text
Organization
      │
      ▼
Security
      │
      ▼
SPPB
 ├── Asset
 ├── Workflow
 ├── Attachment
 ├── Validation
 ├── Notification
 └── Audit
```

---

## 6. Aggregate Roots

| Aggregate | Root |
|-----------|------|
| SPPB | SPPB Header |
| Workflow | Workflow Instance |
| Asset | Asset |
| Organization | Plant |
| Security | User |

---

## 7. Domain Services

- SPPBService
- WorkflowService
- ApprovalService
- AssetService
- AttachmentService
- NotificationService
- ReportingService
- AuditService

---

## 8. Domain Events

- SppbCreated
- SppbSubmitted
- WorkflowGenerated
- ApprovalApproved
- ApprovalRejected
- RevisionRequested
- AttachmentUploaded
- DocumentValidated

---

## 9. Invariants

- Nomor SPPB unik.
- Workflow wajib terbentuk saat submit.
- Approval tidak dapat dilewati.
- Audit wajib tercatat.
- Database mengikuti desain Frozen.

---

## 10. Future Expansion

- Multi Company
- ERP Integration
- Flutter Client
- External API

---

## 11. Traceability

Business Requirement
↓
Functional Requirement
↓
Domain Model
↓
Service Layer
↓
Implementation

---

## 12. Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Enterprise Domain Model|
