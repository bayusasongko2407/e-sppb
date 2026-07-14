
---
document_id: DOC-014
title: Coding Standard
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: Lead Backend Engineer
reviewer: Software Architect
approver: Steering Committee
depends_on:
  - DOC-006
  - DOC-011
---

# E-SPPB Enterprise
# CODING STANDARD

## Purpose

Dokumen ini menjadi standar penulisan kode untuk seluruh implementasi E‑SPPB Enterprise menggunakan Laravel 12, PHP 8.3, dan Filament v5.

## Core Principles

- Clean Architecture
- SOLID
- DRY
- KISS
- Convention over Configuration
- Documentation First

## PHP Standards

- PSR-1
- PSR-4
- PSR-12
- Strict typing untuk file baru bila memungkinkan.
- Gunakan enum untuk status tetap.

## Laravel Standards

- Validasi menggunakan Form Request.
- Business logic pada Service Layer.
- Eloquent untuk ORM.
- Policy untuk authorization.
- Event untuk proses asynchronous.
- Queue untuk pekerjaan berat.

## Project Structure

- app/Actions
- app/Services
- app/DTOs
- app/Enums
- app/Events
- app/Jobs
- app/Listeners
- app/Models
- app/Policies
- app/Repositories (bila diperlukan)

## Naming Convention

| Item | Standard |
|------|----------|
| Class | PascalCase |
| Method | camelCase |
| Variable | camelCase |
| Constant | UPPER_CASE |
| Migration | snake_case |
| Table | tbl_* |
| View | vw_* |

## Code Rules

- Maksimal satu tanggung jawab per class.
- Hindari query di View/Resource.
- Dependency Injection.
- Constructor Property Promotion bila sesuai.
- Hindari static helper untuk business logic.

## Database Rules

- Gunakan transaction untuk proses kritis.
- Gunakan eager loading.
- Hindari N+1 query.

## Error Handling

- Exception spesifik.
- Jangan menelan exception.
- Audit untuk error bisnis.

## Testing

- Unit Test untuk service.
- Feature Test untuk alur utama.
- Regression Test sebelum release.

## Review Checklist

- [ ] PSR compliant
- [ ] SOLID
- [ ] Service Layer
- [ ] Test tersedia
- [ ] Dokumentasi diperbarui

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Enterprise Coding Standard|
