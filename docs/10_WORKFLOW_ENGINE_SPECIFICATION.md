
---
document_id: DOC-010
title: Workflow Engine Specification
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: Software Architect
reviewer: Product Owner
approver: Steering Committee
depends_on:
  - DOC-003
  - DOC-004
  - DOC-006
  - DOC-009
---

# E-SPPB Enterprise
# WORKFLOW ENGINE SPECIFICATION

## 1. Purpose

Dokumen ini mendefinisikan spesifikasi Workflow Engine yang mengatur siklus hidup dokumen SPPB mulai dari Draft hingga Closed. Workflow Engine menjadi pusat proses approval, revisi, penolakan, delegasi, SLA, audit, dan notifikasi.

---

## 2. Objectives

- Approval terstandarisasi
- Workflow dapat dikonfigurasi
- Audit trail lengkap
- SLA dapat dimonitor
- Mendukung REST API dan Flutter

---

## 3. Workflow Lifecycle

```text
Draft
 ↓
Submit
 ↓
Generate Workflow
 ↓
Waiting Approval
 ↓
Approved
 ↓
Executed
 ↓
Closed
```

Terminal state:
- Rejected
- Cancelled

---

## 4. State Transition Matrix

| Current | Action | Next |
|---------|--------|------|
| Draft | Submit | Waiting Approval |
| Waiting Approval | Approve | Next Step / Approved |
| Waiting Approval | Reject | Rejected |
| Waiting Approval | Request Revision | Revision |
| Revision | Submit | Waiting Approval |
| Approved | Execute | Closed |

---

## 5. Approval Rules

- Approval mengikuti Workflow Template.
- Setiap step memiliki approver.
- Step berikut aktif setelah step sebelumnya selesai.
- Final approver mengubah status menjadi Approved.

---

## 6. Delegation

Delegasi aktif jika:
- Delegasi masih berlaku.
- Approver utama tidak tersedia.

Delegasi tetap menghasilkan audit atas pengguna yang bertindak sebagai delegasi.

---

## 7. Revision Flow

```text
Approver
 ↓
Request Revision
 ↓
Requester
 ↓
Update
 ↓
Submit
 ↓
Restart Workflow
```

---

## 8. Rejection Flow

```text
Approver
 ↓
Reject
 ↓
Mandatory Reason
 ↓
Workflow Closed
```

---

## 9. SLA Strategy

- SLA per workflow step.
- Reminder sebelum jatuh tempo.
- Escalation setelah SLA terlampaui.
- SLA dicatat pada audit.

---

## 10. Events

- WorkflowGenerated
- ApprovalRequested
- ApprovalApproved
- ApprovalRejected
- RevisionRequested
- WorkflowCompleted

---

## 11. Notifications

Trigger:
- Submit
- Approval Request
- Approval Success
- Reject
- Revision
- Escalation
- Closed

---

## 12. Audit Requirements

Setiap aksi mencatat:
- User
- Waktu
- Status lama
- Status baru
- Catatan

---

## 13. REST API Mapping

- POST /sppb/{id}/submit
- POST /workflow/{id}/approve
- POST /workflow/{id}/reject
- POST /workflow/{id}/revision
- GET /workflow/{id}

---

## 14. Error Handling

- Invalid Transition
- Unauthorized Approval
- Missing Workflow Template
- SLA Configuration Missing
- Concurrent Approval

---

## 15. Testing Scenarios

- Single approval
- Multi-level approval
- Revision
- Rejection
- Delegation
- SLA timeout
- Invalid transition
- Concurrent request

---

## 16. Traceability

Business Requirement
↓
Functional Requirement
↓
Workflow Engine
↓
Service Layer
↓
API
↓
Testing

---

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Workflow Engine Specification|
