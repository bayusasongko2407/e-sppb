
---
document_id: DOC-008
title: Database Master Plan
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: Database Architect
reviewer: Software Architect
approver: Steering Committee
depends_on:
  - DOC-006
  - DOC-007
---

# E-SPPB Enterprise
# DATABASE MASTER PLAN

## 1. Purpose

Dokumen ini menjadi acuan resmi seluruh implementasi database E‑SPPB Enterprise.
Seluruh migration, model, query, service, API, dan laporan wajib mengikuti dokumen ini.

## 2. Database Principles

- Database Schema berstatus Frozen.
- Perubahan wajib melalui Architecture Decision Record (ADR).
- Normalisasi minimal 3NF.
- Referential Integrity wajib dijaga.
- Auditability dan traceability menjadi prioritas.

---

## 3. Technology

| Item | Value |
|------|-------|
| DBMS | MariaDB |
| ORM | Eloquent ORM |
| Migration | Laravel Migration |
| Charset | utf8mb4 |
| Collation | utf8mb4_unicode_ci |

---

## 4. Domain Mapping

| Domain | Primary Tables |
|---------|----------------|
| Organization | plants, departments, locations, units |
| Security | users |
| Asset | assets, asset_areas |
| Workflow | workflow_templates, workflow_steps, workflow_instances |
| Transaction | sppb_headers, sppb_details |
| Attachment | attachments |
| Audit | activity_logs, sppb_status_logs |
| Utility | running_numbers |

---

## 5. Database Rules

1. Tidak mengubah struktur Frozen tanpa review.
2. Seluruh foreign key tervalidasi.
3. Soft delete hanya bila disetujui pada desain.
4. Timestamp wajib digunakan sesuai standar Laravel.
5. Seluruh transaksi penting memiliki audit log.

---

## 6. Relationship Strategy

Organization
  ├── Users
  ├── Assets
  └── Workflow

SPPB Header
  ├── SPPB Detail
  ├── Attachment
  ├── Workflow Instance
  └── Status Log

---

## 7. Indexing Strategy

- Primary Key pada seluruh tabel.
- Foreign Key diindeks.
- Unique Index untuk nomor dokumen.
- Composite Index pada query yang sering digunakan.
- Review index setiap perubahan besar.

---

## 8. Transaction Strategy

- Database Transaction untuk proses submit.
- Rollback bila workflow gagal dibuat.
- Konsistensi ACID dipertahankan.

---

## 9. Naming Convention

- Table : tbl_<entity>
- View : vw_<entity>
- Foreign Key : <table>_id
- Boolean : is_<name>
- Timestamp : created_at, updated_at

---

## 10. Security

- Principle of Least Privilege.
- Parameter binding wajib.
- Tidak ada dynamic SQL tanpa validasi.
- Backup terenkripsi.

---

## 11. Backup & Recovery

- Backup harian.
- Verifikasi restore berkala.
- Backup attachment terpisah.
- Recovery procedure terdokumentasi.

---

## 12. Performance Guidelines

- Hindari N+1 Query.
- Gunakan eager loading.
- Pagination untuk dataset besar.
- Optimasi query melalui EXPLAIN.

---

## 13. Traceability

Business Requirement
↓
Database Design
↓
Migration
↓
Model
↓
Service
↓
API
↓
UI

---

## 14. Review Checklist

- [ ] Konsisten dengan schema Frozen
- [ ] FK tervalidasi
- [ ] Index sesuai kebutuhan
- [ ] Naming convention sesuai
- [ ] Audit strategy terpenuhi

---

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Database Master Plan|
