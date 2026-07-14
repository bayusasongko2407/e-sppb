
---
document_id: DOC-020
title: Deployment Plan
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: DevOps Engineer
reviewer: Software Architect
approver: Steering Committee
depends_on:
  - DOC-006
  - DOC-013
  - DOC-019
---

# E-SPPB Enterprise
# DEPLOYMENT PLAN

## 1. Purpose

Dokumen ini menjadi pedoman resmi deployment E‑SPPB Enterprise pada lingkungan Ubuntu Server menggunakan Nginx, PHP 8.3, Laravel 12, MariaDB, Redis, dan GitHub.

---

## 2. Deployment Objectives

- Deployment konsisten
- Zero/Minimal Downtime
- Rollback terencana
- Monitoring aktif
- Mudah direplikasi

---

## 3. Environment Strategy

| Environment | Purpose |
|-------------|---------|
| Local | Pengembangan |
| Development | Integrasi |
| Staging | UAT |
| Production | Operasional |

---

## 4. Target Infrastructure

- Ubuntu Server LTS
- Nginx
- PHP-FPM 8.3
- Laravel 12
- MariaDB
- Redis
- Supervisor
- Cron
- GitHub Repository

---

## 5. Deployment Architecture

```text
Internet
   │
Cloudflare (optional)
   │
Nginx
   │
Laravel
 ├── Queue (Redis)
 ├── Scheduler
 ├── REST API
 └── Filament
   │
MariaDB
```

---

## 6. Deployment Workflow

1. Pull latest release
2. Composer install
3. Node build (bila diperlukan)
4. Jalankan migration
5. Cache config/route/view
6. Restart queue
7. Health check
8. Go-Live verification

---

## 7. Environment Variables

- APP_ENV
- APP_KEY
- APP_URL
- DB_*
- REDIS_*
- MAIL_*
- QUEUE_CONNECTION

Seluruh secret disimpan pada `.env` dan tidak dikomit ke repository.

---

## 8. Queue & Scheduler

- Supervisor mengelola queue worker.
- Scheduler dijalankan setiap menit melalui Cron.
- Queue dimonitor secara berkala.

---

## 9. Backup Strategy

- Backup database harian.
- Backup storage attachment.
- Verifikasi restore berkala.

---

## 10. Rollback Strategy

- Gunakan Git tag.
- Rollback aplikasi ke release stabil.
- Rollback migration mengikuti kebijakan database.
- Dokumentasikan seluruh rollback.

---

## 11. Health Check

- HTTP availability
- Database connectivity
- Queue worker
- Scheduler
- Storage permission
- Disk usage

---

## 12. Security

- HTTPS Only
- Firewall aktif
- Least Privilege
- SSH key authentication
- File permission sesuai standar Laravel

---

## 13. Post Deployment Checklist

- [ ] Login berhasil
- [ ] Dashboard tampil
- [ ] Queue berjalan
- [ ] Scheduler aktif
- [ ] Upload file berhasil
- [ ] REST API tervalidasi
- [ ] Log tanpa error kritis

---

## 14. Traceability

Release
↓
Deployment
↓
Verification
↓
Monitoring
↓
Operation

---

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Enterprise Deployment Plan|
