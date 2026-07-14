
---
document_id: DOC-018
title: AI Rules & Governance
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: Software Architect
reviewer: Lead Backend Engineer
approver: Steering Committee
depends_on:
  - DOC-001
  - DOC-006
  - DOC-014
  - DOC-015
  - DOC-016
---

# E-SPPB Enterprise
# AI RULES

## 1. Purpose

Dokumen ini menjadi pedoman resmi penggunaan AI pada pengembangan E‑SPPB Enterprise agar seluruh hasil tetap konsisten dengan arsitektur, business rule, dan keputusan yang telah berstatus Frozen.

---

## 2. AI Usage Principles

- Documentation First
- Human Review Required
- Architecture Driven
- Security by Default
- AI membantu implementasi, bukan mengambil keputusan bisnis.

---

## 3. Mandatory Constraints

AI **tidak boleh**:

- Mengubah database Frozen.
- Mengubah business logic Frozen.
- Memindahkan business logic ke UI.
- Menghilangkan audit trail.
- Mengubah workflow tanpa ADR.

---

## 4. AI Coding Rules

- Gunakan Laravel 12.
- PHP 8.3.
- Filament v5.
- Business logic pada Service Layer.
- Ikuti PSR-12 dan SOLID.
- Gunakan dependency injection.

---

## 5. AI Prompt Standard

Setiap prompt implementasi minimal memuat:

- Tujuan perubahan.
- Dokumen acuan.
- Modul terkait.
- Dampak terhadap database.
- Dampak terhadap API.
- Dampak terhadap Flutter.

---

## 6. AI Review Checklist

- [ ] Tidak melanggar keputusan Frozen.
- [ ] Menggunakan Service Layer.
- [ ] Tidak ada duplikasi kode.
- [ ] Tidak ada query bisnis di UI.
- [ ] Konsisten dengan Coding Standard.

---

## 7. AI Generated Code Validation

Sebelum merge:

1. Static Analysis
2. Unit Test
3. Feature Test
4. Manual Review
5. Documentation Update

---

## 8. AI Documentation Rules

AI wajib:

- Memperbarui Change Log.
- Menjaga Requirement Traceability.
- Menambahkan ADR bila terjadi keputusan arsitektur baru.

---

## 9. AI Governance

Perubahan besar memerlukan:

Proposal
↓
Architecture Review
↓
Approval
↓
AI Implementation
↓
Code Review
↓
Testing
↓
Merge

---

## 10. Security Rules

AI tidak boleh:

- Menyimpan credential.
- Membuat hardcoded secret.
- Menonaktifkan validasi.
- Mengabaikan authorization.

---

## 11. Traceability

Requirement
↓
Architecture
↓
AI Prompt
↓
Generated Code
↓
Review
↓
Testing

---

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial AI Rules & Governance|
