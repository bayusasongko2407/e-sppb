
---
document_id: DOC-011
title: Service Catalog
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: Software Architect
reviewer: Lead Backend Engineer
approver: Steering Committee
depends_on:
  - DOC-006
  - DOC-007
  - DOC-010
---

# E-SPPB Enterprise
# SERVICE CATALOG

## 1. Purpose

Dokumen ini mendefinisikan seluruh Service Layer yang menjadi pusat business logic aplikasi E‑SPPB Enterprise.

Semua Filament Resource, REST API, Queue, Scheduler, Event, maupun Command wajib menggunakan Service Layer.

---

## 2. Service Layer Principles

- Business logic hanya berada pada Service.
- Service bersifat reusable.
- Service tidak bergantung pada UI.
- Service dapat digunakan oleh Web maupun REST API.

---

## 3. Service Catalog

| ID | Service | Responsibility |
|----|---------|----------------|
| SRV-001 | AuthenticationService | Login & Session |
| SRV-002 | UserService | User Management |
| SRV-003 | OrganizationService | Plant, Department |
| SRV-004 | AssetService | Asset Management |
| SRV-005 | WorkflowService | Workflow Generation |
| SRV-006 | ApprovalService | Approval Process |
| SRV-007 | SppbService | SPPB Lifecycle |
| SRV-008 | AttachmentService | File Management |
| SRV-009 | ValidationService | Document Validation |
| SRV-010 | NotificationService | Notification Delivery |
| SRV-011 | ReportingService | Dashboard & Reports |
| SRV-012 | AuditService | Audit Trail |
| SRV-013 | RunningNumberService | Document Numbering |

---

## 4. Service Dependency

AuthenticationService
        │
        ▼
UserService
        │
        ▼
SppbService
 ├── WorkflowService
 ├── ApprovalService
 ├── AttachmentService
 ├── ValidationService
 ├── NotificationService
 └── AuditService

---

## 5. Transaction Rules

- Submit SPPB menggunakan database transaction.
- Approval menggunakan transaction.
- Rollback bila workflow gagal.
- Audit wajib ditulis sebelum commit selesai.

---

## 6. Event Integration

Service dapat menghasilkan event berikut:

- SppbCreated
- SppbSubmitted
- WorkflowGenerated
- ApprovalApproved
- ApprovalRejected
- RevisionRequested
- AttachmentUploaded
- NotificationQueued

---

## 7. API Integration

REST API wajib memanggil Service Layer.

Filament Resource wajib memanggil Service Layer.

Tidak diperbolehkan business logic langsung di Controller maupun Resource.

---

## 8. Error Handling

- DomainException
- ValidationException
- AuthorizationException
- BusinessRuleException

Semua exception harus menghasilkan audit log bila relevan.

---

## 9. Review Checklist

- [ ] Tidak ada business logic di UI
- [ ] Service reusable
- [ ] Mendukung REST API
- [ ] Mendukung Queue & Event

---

## 10. Traceability

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

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Enterprise Service Catalog|
