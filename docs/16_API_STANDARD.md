
---
document_id: DOC-016
title: API Standard
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: Lead Backend Engineer
reviewer: Software Architect
approver: Steering Committee
depends_on:
  - DOC-006
  - DOC-011
  - DOC-015
---

# E-SPPB Enterprise
# API STANDARD

## 1. Purpose

Dokumen ini menjadi standar resmi perancangan dan implementasi REST API untuk E‑SPPB Enterprise agar seluruh endpoint konsisten, aman, terdokumentasi, dan siap digunakan oleh Flutter Android maupun integrasi sistem lain.

---

## 2. API Principles

- API First
- RESTful
- Stateless
- Consistent Contract
- Versioned API
- Secure by Default
- Reusable Service Layer

---

## 3. Versioning

- Base URL: `/api/v1`
- Perubahan breaking change menggunakan versi baru (`/api/v2`).
- Patch dan minor tidak mengubah kontrak API.

---

## 4. Endpoint Convention

| Resource | Example |
|----------|---------|
| Collection | GET /api/v1/sppb |
| Detail | GET /api/v1/sppb/{id} |
| Create | POST /api/v1/sppb |
| Update | PUT /api/v1/sppb/{id} |
| Delete | DELETE /api/v1/sppb/{id} |
| Action | POST /api/v1/sppb/{id}/submit |

---

## 5. Authentication

- Bearer Token
- Token-based authentication
- Authorization menggunakan Role & Permission
- Seluruh endpoint sensitif wajib diautentikasi

---

## 6. Request & Response Standard

Semua response JSON menggunakan struktur konsisten:

- success
- message
- data
- meta (opsional)
- errors (jika ada)

---

## 7. HTTP Status Code

- 200 OK
- 201 Created
- 204 No Content
- 400 Bad Request
- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 409 Conflict
- 422 Validation Error
- 500 Internal Server Error

---

## 8. Pagination

- Pagination wajib untuk collection besar.
- Parameter:
  - page
  - per_page
- Metadata pagination dikembalikan pada response.

---

## 9. Filtering & Sorting

Filtering:
- `?status=approved`

Sorting:
- `?sort=created_at`
- `?direction=desc`

Searching:
- `?search=keyword`

---

## 10. Validation Error

Response error validasi harus konsisten dan menyertakan daftar field yang gagal divalidasi.

---

## 11. Idempotency

Operasi penting (submit, approve, reject) harus aman terhadap request berulang bila diperlukan.

---

## 12. Security

- HTTPS Only
- Input Validation
- Rate Limiting
- Audit Logging
- Parameter Binding
- File Validation

---

## 13. Documentation

- OpenAPI / Swagger direkomendasikan.
- Setiap endpoint memiliki deskripsi, request, response, dan contoh.

---

## 14. API Testing

- Unit Test
- Feature Test
- Contract Test
- Regression Test

---

## 15. Traceability

Business Requirement
↓
Functional Requirement
↓
Service Layer
↓
REST API
↓
Flutter Client

---

## 16. Review Checklist

- [ ] Endpoint konsisten
- [ ] Service Layer digunakan
- [ ] Authentication diterapkan
- [ ] Response sesuai standar
- [ ] Dokumentasi diperbarui

---

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Enterprise API Standard|
