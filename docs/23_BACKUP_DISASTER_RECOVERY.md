
---
document_id: DOC-023
title: Backup & Disaster Recovery
version: 3.0.0
status: Draft
documentation_level: Enterprise Level 5
owner: DevOps Engineer
reviewer: Software Architect
approver: Steering Committee
depends_on:
  - DOC-020
  - DOC-021
  - DOC-022
---

# E-SPPB Enterprise
# BACKUP & DISASTER RECOVERY

## 1. Purpose

Dokumen ini mendefinisikan kebijakan backup, proses pemulihan, target RPO/RTO, serta prosedur Disaster Recovery untuk memastikan keberlangsungan operasional E‑SPPB Enterprise.

---

## 2. Objectives

- Menjamin ketersediaan data
- Meminimalkan kehilangan data
- Mempercepat proses pemulihan
- Menyediakan prosedur terdokumentasi
- Mendukung Business Continuity

---

## 3. Scope

Meliputi:

- Database MariaDB
- Storage Attachment
- Source Code
- Konfigurasi Server
- File .env
- Redis (bila diperlukan)

---

## 4. Recovery Targets

| Metric | Target |
|--------|--------|
| RPO | ≤ 24 jam |
| RTO | ≤ 4 jam |

Nilai dapat disesuaikan sesuai SLA organisasi.

---

## 5. Backup Strategy

### Database
- Full backup harian
- Incremental bila tersedia
- Enkripsi backup
- Verifikasi hasil backup

### File Storage
- Backup attachment harian
- Sinkronisasi ke media cadangan

### Configuration
- Backup konfigurasi Nginx
- PHP
- Supervisor
- Cron
- .env (terenkripsi)

---

## 6. Backup Schedule

| Asset | Frequency |
|-------|-----------|
| Database | Daily |
| Attachments | Daily |
| Configuration | Weekly |
| Source Code | Git Repository |

---

## 7. Backup Retention

- Daily : 30 hari
- Weekly : 12 minggu
- Monthly : 12 bulan

---

## 8. Restore Procedure

1. Verifikasi backup
2. Siapkan server
3. Restore database
4. Restore attachment
5. Restore konfigurasi
6. Jalankan migrasi bila diperlukan
7. Validasi aplikasi
8. Go-Live

---

## 9. Disaster Recovery Flow

Incident
↓
Assessment
↓
Activate DR Plan
↓
Restore
↓
Validation
↓
Production Recovery
↓
Post Incident Review

---

## 10. Validation

- Login berhasil
- Queue aktif
- Scheduler aktif
- API berfungsi
- Dashboard normal
- Attachment dapat diakses

---

## 11. Business Continuity

Prioritas layanan:

1. Authentication
2. Workflow
3. SPPB
4. Reporting
5. Dashboard

---

## 12. Risks

| Risk | Mitigation |
|------|------------|
| Backup gagal | Monitoring & Alert |
| Restore gagal | Uji restore berkala |
| Storage rusak | Backup offsite |
| Human error | SOP & Review |

---

## 13. Review Checklist

- [ ] Backup tervalidasi
- [ ] Restore diuji
- [ ] RPO/RTO terpenuhi
- [ ] SOP diperbarui
- [ ] DR drill dilakukan

---

## Change Log

| Version | Description |
|---------|-------------|
|3.0.0|Initial Backup & Disaster Recovery Standard|
