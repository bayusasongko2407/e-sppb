
---
document_id: DOC-019
title: Git Workflow
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: DevOps Engineer
reviewer: Software Architect
approver: Steering Committee
depends_on:
  - DOC-013
  - DOC-014
  - DOC-018
---

# E-SPPB Enterprise
# GIT WORKFLOW

## 1. Purpose

Dokumen ini menetapkan standar penggunaan Git dan GitHub untuk memastikan kolaborasi tim berlangsung konsisten, aman, dan dapat diaudit.

---

## 2. Workflow Strategy

Model yang digunakan:

- main
- develop
- feature/*
- release/*
- hotfix/*

Seluruh perubahan dilakukan melalui Pull Request.

---

## 3. Branch Rules

| Branch | Purpose |
|--------|---------|
| main | Production |
| develop | Integrasi |
| feature/* | Pengembangan fitur |
| release/* | Persiapan rilis |
| hotfix/* | Perbaikan produksi |

---

## 4. Branch Naming

feature/sppb-approval

feature/master-asset

release/v1.0.0

hotfix/login-error

---

## 5. Commit Convention

Gunakan Conventional Commits.

Contoh:

- feat:
- fix:
- refactor:
- docs:
- test:
- chore:
- perf:
- ci:

Contoh:

feat: add workflow approval service

fix: correct running number generation

docs: update architecture document

---

## 6. Pull Request Rules

PR wajib memuat:

- Ringkasan perubahan
- Dokumen terkait
- Dampak database
- Dampak API
- Hasil pengujian

Minimal satu reviewer.

---

## 7. Code Review Checklist

- [ ] Sesuai Coding Standard
- [ ] Tidak melanggar keputusan Frozen
- [ ] Service Layer digunakan
- [ ] Test lulus
- [ ] Dokumentasi diperbarui

---

## 8. Merge Strategy

- Squash Merge untuk feature kecil.
- Merge Commit untuk release.
- Hotfix langsung ke main lalu back-merge ke develop.

---

## 9. Versioning

Semantic Versioning

MAJOR.MINOR.PATCH

Contoh:

1.0.0

1.1.0

1.1.1

---

## 10. Repository Protection

- main protected
- develop protected
- PR wajib review
- Status check wajib lulus
- Direct push dilarang

---

## 11. Release Flow

feature/*
↓
develop
↓
release/*
↓
main
↓
tag
↓
production

---

## 12. CI/CD Recommendation

- Composer validate
- PHPStan
- Pint
- PHPUnit
- Build assets
- Deploy setelah approval

---

## 13. Rollback Strategy

- Tag setiap release.
- Rollback menggunakan release sebelumnya.
- Database rollback mengikuti migration policy.

---

## 14. Traceability

Requirement
↓
Task
↓
Branch
↓
Commit
↓
Pull Request
↓
Release

---

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Enterprise Git Workflow|
