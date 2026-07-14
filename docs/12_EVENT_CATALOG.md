
---
document_id: DOC-012
title: Event Catalog
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: Software Architect
reviewer: Lead Backend Engineer
approver: Steering Committee
depends_on:
  - DOC-010
  - DOC-011
---

# E-SPPB Enterprise
# EVENT CATALOG

## 1. Purpose

Dokumen ini mendefinisikan seluruh Domain Event, Application Event, Listener, Queue, dan integrasi notifikasi yang digunakan pada E‑SPPB Enterprise.

Seluruh event harus bersifat business-driven dan menjadi mekanisme komunikasi asynchronous antar modul.

---

## 2. Event Principles

- Event mewakili kejadian bisnis.
- Event immutable.
- Event tidak mengandung business logic.
- Listener menangani side effect.
- Event harus dapat diaudit.

---

## 3. Event Categories

| Category | Description |
|----------|-------------|
| Domain | Perubahan proses bisnis |
| Application | Integrasi internal |
| Integration | Integrasi eksternal (future) |

---

## 4. Event Catalog

| ID | Event | Source | Trigger |
|----|-------|--------|---------|
| EVT-001 | SppbCreated | SppbService | Draft dibuat |
| EVT-002 | SppbSubmitted | SppbService | Submit |
| EVT-003 | WorkflowGenerated | WorkflowService | Workflow terbentuk |
| EVT-004 | ApprovalRequested | WorkflowService | Approval baru |
| EVT-005 | ApprovalApproved | ApprovalService | Approve |
| EVT-006 | ApprovalRejected | ApprovalService | Reject |
| EVT-007 | RevisionRequested | ApprovalService | Revision |
| EVT-008 | AttachmentUploaded | AttachmentService | Upload |
| EVT-009 | NotificationQueued | NotificationService | Kirim notifikasi |
| EVT-010 | DocumentValidated | ValidationService | Validasi selesai |
| EVT-011 | AuditLogged | AuditService | Audit tersimpan |

---

## 5. Listener Mapping

| Event | Listener |
|--------|----------|
| SppbSubmitted | GenerateWorkflow |
| WorkflowGenerated | QueueNotification |
| ApprovalApproved | NextApprovalResolver |
| ApprovalRejected | NotifyRequester |
| RevisionRequested | NotifyRequester |
| DocumentValidated | WriteAudit |

---

## 6. Queue Strategy

- Listener berat dijalankan melalui Queue.
- Retry maksimal 3 kali.
- Dead Letter Queue dipertimbangkan pada fase produksi.
- Queue dipantau melalui monitoring.

---

## 7. Event Flow

SppbSubmitted
↓
WorkflowGenerated
↓
ApprovalRequested
↓
ApprovalApproved / ApprovalRejected
↓
NotificationQueued
↓
AuditLogged

---

## 8. Idempotency Rules

- Event hanya diproses satu kali.
- Listener harus aman terhadap retry.
- Gunakan transaction bila diperlukan.

---

## 9. Error Handling

- Listener failure dicatat.
- Retry otomatis.
- Audit tetap ditulis bila memungkinkan.
- Tidak boleh menyebabkan data inkonsisten.

---

## 10. Future Events

- PushNotificationSent
- ERPIntegrationCompleted
- MobileSyncCompleted
- QRScanned

---

## 11. Traceability

Business Requirement
↓
Workflow
↓
Event
↓
Listener
↓
Queue
↓
Audit

---

## 12. Review Checklist

- [ ] Event memiliki tujuan bisnis
- [ ] Listener tidak berisi logika domain utama
- [ ] Queue sesuai kebutuhan
- [ ] Event terdokumentasi

---

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Enterprise Event Catalog|
