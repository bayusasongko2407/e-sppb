
---
document_id: DOC-006
title: System Architecture
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: Software Architect
reviewer: Lead Backend Engineer
approver: Steering Committee
depends_on:
  - DOC-001
  - DOC-003
  - DOC-004
  - DOC-005
---

# E-SPPB Enterprise
# SYSTEM ARCHITECTURE

> Dokumen ini mendefinisikan arsitektur sistem E‑SPPB Enterprise sebagai acuan seluruh implementasi.

# 1. Executive Summary

Arsitektur menggunakan **Clean Architecture**, **SOLID Principles**, pendekatan modular, serta **REST API First**. Seluruh business logic berada pada Service Layer dan tidak ditempatkan pada UI.

---

# 2. Architecture Principles

- Documentation First
- Database Frozen
- Business Logic Frozen
- API First
- Security by Design
- Modular Architecture
- Testability
- Scalability
- Maintainability

---

# 3. Logical Architecture

```text
Users
   │
Browser / Flutter
   │
Presentation Layer
(Filament / REST API)
   │
Application Layer
(Actions / Services)
   │
Domain Layer
(Business Rules)
   │
Infrastructure Layer
(Repository / Storage / Queue)
   │
MariaDB / Redis / Filesystem
```

---

# 4. Layer Responsibilities

| Layer | Responsibility |
|--------|----------------|
| Presentation | UI & API |
| Application | Use Case Orchestration |
| Domain | Business Rules |
| Infrastructure | Database, Queue, Storage |
| Persistence | MariaDB |

---

# 5. Domain Modules

- Organization
- Security
- Asset
- Workflow
- SPPB
- Attachment
- Notification
- Reporting
- Audit
- Validation

---

# 6. Request Lifecycle

```text
Request
 ↓
Validation
 ↓
Policy
 ↓
Service Layer
 ↓
Transaction
 ↓
Event
 ↓
Response
```

---

# 7. Service Layer Rules

- Seluruh business logic berada di Service.
- Resource Filament hanya mengatur presentasi.
- API menggunakan service yang sama.
- Tidak ada query kompleks di UI.

---

# 8. Security Architecture

- Authentication
- Authorization (Role & Permission)
- CSRF
- XSS Protection
- SQL Injection Prevention
- Audit Trail
- File Validation

---

# 9. Data Architecture

- MariaDB sebagai primary database.
- Redis untuk Queue & Cache.
- Attachment disimpan pada storage terpisah.
- Database mengikuti skema Frozen.

---

# 10. Integration Architecture

Current:
- Web Admin (Filament)

Future:
- REST API
- Flutter Android

Semua client menggunakan business service yang sama.

---

# 11. Deployment Overview

```text
Internet
   │
Cloudflare
   │
Nginx
   │
Laravel
 ├── Queue
 ├── Scheduler
 └── REST API
   │
MariaDB
Redis
Storage
```

---

# 12. Dependency Rules

Presentation → Application

Application → Domain

Infrastructure → Domain Contracts

Tidak diperbolehkan ketergantungan terbalik.

---

# 13. Quality Attributes Mapping

| Attribute | Strategy |
|-----------|----------|
| Performance | Cache, Queue |
| Security | RBAC, Validation |
| Scalability | Stateless API |
| Reliability | Transaction & Logging |
| Maintainability | SOLID |

---

# 14. Traceability

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

# 15. Architecture Decision Records

- ADR-001 Laravel 12
- ADR-002 Filament v5
- ADR-003 Service Layer
- ADR-004 REST API First
- ADR-005 Database Frozen
- ADR-006 Spatie Laravel Permission for Role-Based Authorization

---

# 16. Acceptance Criteria

- Layer terdefinisi jelas.
- Seluruh modul memiliki posisi arsitektur.
- Menjadi referensi implementasi backend dan API.

# Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Enterprise System Architecture|
