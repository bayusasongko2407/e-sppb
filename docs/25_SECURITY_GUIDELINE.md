
---
document_id: DOC-025
title: Security Guideline
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: Security Engineer
reviewer: Software Architect
approver: Steering Committee
depends_on:
  - DOC-006
  - DOC-016
  - DOC-024
---

# E-SPPB Enterprise
# SECURITY GUIDELINE

## 1. Purpose

Dokumen ini menetapkan pedoman keamanan untuk seluruh komponen E‑SPPB Enterprise, meliputi aplikasi web, REST API, database, infrastruktur, dan integrasi masa depan.

---

## 2. Security Principles

- Security by Design
- Least Privilege
- Defense in Depth
- Zero Trust (prinsip)
- Secure Defaults
- Auditability

---

## 3. Identity & Access Management

- Login menggunakan Email atau NIK.
- Role & Permission wajib diterapkan.
- Policy Laravel untuk otorisasi.
- Akun nonaktif tidak dapat login.
- Seluruh aktivitas penting dicatat.

Implementasi otorisasi menggunakan Spatie Laravel Permission dengan schema standar non-team, guard `web`, dan Laravel Policies. Keputusan schema dan pemetaan permission dicatat pada `docs/adr/ADR-006_SPATIE_LARAVEL_PERMISSION.md`.

---

## 4. Application Security

- Validasi seluruh input.
- CSRF Protection.
- XSS Protection.
- SQL Injection Prevention.
- Output escaping.
- File upload tervalidasi.

---

## 5. API Security

- HTTPS Only.
- Bearer Token.
- Role-based authorization.
- Rate limiting.
- API versioning.
- Audit untuk endpoint sensitif.

---

## 6. Database Security

- Principle of Least Privilege.
- Parameter binding.
- Backup terenkripsi.
- Kredensial melalui environment.

---

## 7. Infrastructure Security

- Ubuntu LTS diperbarui.
- Nginx hardened.
- Firewall aktif.
- SSH Key Authentication.
- Fail2ban direkomendasikan.
- TLS modern.

---

## 8. Secret Management

- APP_KEY
- Database password
- Redis password
- Mail credential
- API keys

Secret tidak boleh dikomit ke repository.

---

## 9. Logging & Audit

- Login
- Logout
- Approval
- Perubahan data penting
- Error keamanan

Log tidak boleh berisi password atau token.

---

## 10. Incident Response

Detect
↓
Contain
↓
Investigate
↓
Recover
↓
Review

---

## 11. Security Testing

- Static Analysis
- Dependency Review
- Penetration Test (pra-produksi)
- API Security Test
- Authorization Test

---

## 12. Review Checklist

- [ ] HTTPS aktif
- [ ] Policy diterapkan
- [ ] Secret aman
- [ ] Audit berjalan
- [ ] OWASP dipertimbangkan

---

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Enterprise Security Guideline|
