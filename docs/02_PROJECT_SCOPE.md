
---
document_id: DOC-002
title: E-SPPB Enterprise Project Scope
version: 3.0.0
status: Draft
classification: Internal
owner: Engineering Team
reviewer: Software Architect
approver: Product Owner
documentation_level: Enterprise Level 5
depends_on:
  - DOC-001
---

# E-SPPB Enterprise
# PROJECT SCOPE

> Dokumen ini mendefinisikan ruang lingkup resmi proyek E‑SPPB Enterprise dan menjadi dasar seluruh Business Requirement, Functional Requirement, estimasi pengembangan, serta perencanaan implementasi.

# 1. Executive Summary

Project Scope mendefinisikan batasan proyek, ruang lingkup bisnis, ruang lingkup teknis, deliverable, stakeholder, dependency, serta target implementasi.

Dokumen ini menjadi referensi utama sebelum penyusunan Business Requirement.

---

# 2. Business Scope

## In Scope

### Organization
- Plant
- Department
- User
- Position
- Unit
- Location

### Asset
- Master Asset
- Import Asset
- Asset Area
- Asset History

### Workflow
- Workflow Template
- Workflow Instance
- Approval
- Delegation
- Revision
- Rejection
- SLA Monitoring

### Transaction
- SPPB Header
- SPPB Detail
- Attachment
- Status Log
- Validation
- QR Validation

### Reporting
- Executive Dashboard
- Operational Dashboard
- KPI
- Export PDF
- Export Excel

### Platform
- REST API Foundation
- Mobile Ready Architecture

---

# 3. Out of Scope

- ERP Integration
- SAP Integration
- IoT Integration
- Public API
- Flutter Implementation (fase berikutnya)
- AI Decision Engine

---

# 4. Stakeholder Matrix

| Stakeholder | Responsibility | Influence |
|-------------|---------------|-----------|
| Product Owner | Business Direction | High |
| Software Architect | Solution Design | High |
| Backend Engineer | Laravel Development | High |
| QA Engineer | Verification | Medium |
| DevOps | Deployment | Medium |
| End User | Business Validation | High |

---

# 5. Scope Boundary

Business Request
        │
        ▼
Workflow Engine
        │
        ▼
Approval
        │
        ▼
Validation
        │
        ▼
Reporting

Semua proses berada di dalam batas sistem E‑SPPB.

---

# 6. Functional Boundary

- Authentication
- Authorization
- Master Data
- Workflow
- SPPB
- Notification
- Audit
- Reporting
- REST API

---

# 7. Non Functional Boundary

- Availability
- Security
- Maintainability
- Scalability
- Reliability
- Performance
- Backup
- Monitoring
- Logging

---

# 8. Constraints

- Database Frozen.
- Business Logic Frozen.
- Laravel 12.
- PHP 8.3.
- Filament v5.
- MariaDB.
- Clean Architecture.
- Service Layer.

---

# 9. Assumptions

- Ubuntu Server sebagai target produksi.
- Nginx sebagai web server.
- Redis untuk Queue & Cache.
- Flutter menggunakan REST API yang sama.

---

# 10. Deliverables

1. Enterprise Documentation
2. Laravel Foundation
3. Master Data
4. Workflow Engine
5. SPPB Module
6. Dashboard
7. REST API
8. Production Deployment

---

# 11. Requirement Traceability

Project Scope
    ↓
Business Requirement
    ↓
Functional Requirement
    ↓
Architecture
    ↓
Implementation
    ↓
Testing

---

# 12. Risks

| Risk | Impact | Mitigation |
|------|--------|------------|
| Scope Creep | High | Architecture Review |
| DB Change | High | Frozen Policy |
| Requirement Change | Medium | Version Control |
| API Incompatibility | Medium | API Versioning |

---

# 13. Acceptance Criteria

- Seluruh ruang lingkup terdokumentasi.
- Konsisten dengan Master Plan.
- Menjadi referensi resmi BRD.
- Tidak bertentangan dengan keputusan Frozen.

---

# 14. Appendix

Referensi:
- DOC-000 Document Index
- DOC-001 Master Plan
- Workflow Engine Specification
- Database Master Plan

---

# Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Enterprise Level 5 baseline|
