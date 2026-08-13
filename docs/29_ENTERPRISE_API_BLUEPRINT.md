# Enterprise API Blueprint: SPPB Core API

Dokumen ini adalah acuan implementasi REST API Laravel 12 untuk modul SPPB Enterprise. Dokumen ini tidak berisi kode PHP, migration, controller, model, service, repository, route, seeder, atau test.

## 1 MODULE INFORMATION

| Item | Value |
|---|---|
| Module Name | SPPB Core API |
| Description | API enterprise untuk pengelolaan Surat Permintaan Pengeluaran Barang, detail barang/aset, lampiran, workflow approval, audit status, dokumen, dan pelepasan barang. |
| Purpose | Menyediakan kontrak REST API JSON sebagai acuan tunggal implementasi Laravel 12. |
| Business Owner | Operasional / BAT / Manajemen Plant |
| Primary User | Requester, Manager Approver, BAT Approver, Warehouse, Administrator, Auditor |
| Dependencies | Plant, Department, Location, User, Role/Permission, Item, Asset, Unit, Workflow Template, Attachment, Goods Release, Document Generation |
| Version | v1 |
| Priority | Critical |

## 2 BUSINESS REQUIREMENT

### Kebutuhan Bisnis

- Sistem memungkinkan user membuat draft SPPB berdasarkan Plant dan Department yang sah.
- Sistem mendukung permintaan barang master item dan aset terdaftar.
- Sistem memastikan lokasi asal dan lokasi tujuan valid serta tidak sama.
- Sistem menghasilkan nomor dokumen SPPB secara otomatis sesuai kebijakan dokumen.
- Sistem menjalankan workflow approval berdasarkan template aktif.
- Sistem mendukung approval Manager dan BAT sesuai konfigurasi workflow.
- Sistem mencatat riwayat status, audit trail, dan aktor pada setiap perubahan penting.
- Sistem mendukung revisi, reject, cancel, resubmit, escalation, delegation, completion, dan closed state.
- Sistem menyediakan API JSON-only, versioned, secure, searchable, filterable, sortable, dan paginated.
- Sistem siap dipakai oleh web frontend, mobile Flutter, dan integrasi internal.

### Tujuan Module

- Mendigitalisasi proses SPPB dari draft sampai closed.
- Mengurangi kesalahan manual pada dokumen pengeluaran barang.
- Memastikan approval berjalan sesuai jabatan, role, delegasi, dan scope Plant.
- Menyediakan jejak audit lengkap untuk pemeriksaan internal.
- Menyediakan kontrak API stabil dan OpenAPI-compatible.

### Masalah Yang Diselesaikan

- Permintaan barang manual sulit dilacak.
- Approval manual tidak memiliki SLA dan histori jelas.
- Dokumen dapat berubah tanpa jejak.
- Data SPPB tidak mudah difilter, dicari, atau diintegrasikan.
- Risiko IDOR dan akses lintas Plant tanpa pembatasan data.

### Proses Bisnis

1. Requester membuat draft SPPB.
2. Requester menambahkan detail barang atau aset.
3. Requester mengunggah lampiran bila diperlukan.
4. Requester submit SPPB.
5. Sistem validasi data, generate nomor dokumen, dan membuat workflow.
6. Approver menerima assignment.
7. Approver approve, reject, atau request revision.
8. Jika revisi diminta, requester memperbaiki draft revisi dan resubmit.
9. Jika seluruh step approve, SPPB menjadi Approved.
10. Warehouse melakukan pelepasan barang.
11. Setelah seluruh barang diproses, SPPB menjadi Completed atau Closed.
12. Semua aksi dicatat pada audit trail dan status log.

### Aktor

| Actor | Responsibility |
|---|---|
| Requester | Membuat, mengubah draft, submit, resubmit, cancel draft. |
| Manager Approver | Approve, reject, request revision pada step Manager. |
| BAT Approver | Verifikasi dan approve/reject pada step BAT. |
| Warehouse | Melihat SPPB approved dan melakukan pelepasan barang. |
| Administrator | Mengelola master data, workflow, role, dan permission. |
| Auditor | Melihat data, log, status, dan dokumen tanpa mutasi bisnis. |
| System | Generate workflow, audit, nomor dokumen, notifikasi, SLA, escalation. |

### Kondisi Normal

- User aktif dan memiliki token valid.
- User memiliki permission sesuai action.
- Plant, Department, Location, Item, Asset, dan Unit aktif.
- Detail SPPB minimal satu saat submit.
- Workflow template aktif ditemukan.
- Approver aktif ditemukan.
- Mutasi status dilakukan dalam transaksi.

### Kondisi Error

- Token tidak valid.
- Permission tidak cukup.
- SPPB tidak ditemukan atau berada di luar scope user.
- Status tidak mengizinkan aksi.
- Detail kosong saat submit.
- Lokasi asal dan tujuan sama.
- Workflow template aktif tidak ditemukan.
- Approver tidak tersedia.
- Konflik versi data atau concurrent approval.
- File lampiran tidak valid atau gagal scan.
- Nomor dokumen gagal dibuat.
- Kesalahan database atau infrastruktur.

## 3 DATABASE OVERVIEW

| Table | Primary Key | Foreign Key | Unique Key | Index | Soft Delete | Timestamp | Audit Field | Relationships |
|---|---:|---|---|---|---|---|---|---|
| plants | id | - | code | is_active | No | Yes | created_by, updated_by bila tersedia | hasMany departments, locations, users, assets, sppb_headers |
| departments | id | plant_id | plant_id, code | plant_id, is_active | No | Yes | created_by, updated_by bila tersedia | belongsTo plant; hasMany users, sppb_headers |
| locations | id | plant_id | plant_id, code | name, plant_id, is_active | No | Yes | created_by, updated_by bila tersedia | belongsTo plant; hasMany assets |
| units | id | - | code | is_active | No | Yes | created_by, updated_by bila tersedia | hasMany items, sppb_details |
| items | id | unit_id | code | item_category, is_active | No | Yes | created_by, updated_by bila tersedia | belongsTo unit; hasMany sppb_details |
| assets | id | plant_id, location_id, unit_id | barcode | plant_id, location_id, status, is_active | No | Yes | created_by, updated_by bila tersedia | belongsTo plant, location, unit; hasMany sppb_details |
| positions | id | - | code | is_active | No | Yes | created_by, updated_by | hasMany user_positions |
| user_positions | id | user_id, position_id | user_id, position_id | - | No | Yes | - | belongsTo user, position |
| users | id | plant_id, department_id, manager_id | nik, email | plant_id, department_id, is_active | No | Yes | last_login_at | belongsTo plant, department, manager |
| email_change_requests | id | user_id | - | status | No | Yes | - | belongsTo user |
| roles | id | - | - | - | No | Yes | - | Spatie RBAC |
| permissions | id | - | - | - | No | Yes | - | Spatie RBAC |
| enum_controls | id | - | - | table_name, column_name | No | Yes | - | - |
| workflow_templates | id | plant_id, department_id | uuid; code, version | document_type, plant_id, department_id, is_active | No | Yes | created_by bila tersedia | hasMany workflow_steps, workflow_instances |
| workflow_steps | id | workflow_template_id, approver_user_id, approver_position_id | workflow_template_id, sequence; workflow_template_id, code | approver_user_id, approver_position_id | No | Yes | - | belongsTo workflow_template |
| workflow_delegations | id | delegator_id, delegate_id, plant_id | - | delegator_id/date/status; delegate_id/date/status | No | Yes | created_by bila tersedia | belongsTo delegator, delegate, plant |
| sppb_headers | id | plant_id, department_id, requester_id, origin_location_id, destination_location_id, current_workflow_instance_id, current_approver_id | uuid, document_number | requester/status/date; plant/status/date_needed; current_approver_id | Yes | Yes | submitted_by, approved_by, rejected_by, cancelled_by bila tersedia | hasMany details, attachments, workflow_instances, status_logs, goods_releases |
| sppb_details | id | sppb_header_id, item_id, asset_id, unit_id | sppb_header_id, line_no | item_id, asset_id, unit_id | No | Yes | created_by, updated_by bila tersedia | belongsTo sppb_header, item, asset, unit, goods_release_items |
| workflow_instances | id | workflow_template_id, sppb_header_id | uuid; sppb_header_id, revision_no | status, current_sequence | No | Yes | - | belongsTo sppb_header; hasMany workflow_instance_steps |
| workflow_instance_steps | id | workflow_instance_id, workflow_step_id, acted_by_id | workflow_instance_id, sequence | status, due_at | No | Yes | acted_by_id, acted_at | belongsTo workflow_instance; hasMany workflow_step_approvers |
| workflow_step_approvers | id | workflow_instance_step_id, approver_id, delegated_from_id | workflow_instance_step_id, approver_id | approver_id, status | No | Yes | acted_at | belongsTo step, approver, delegated_from |
| workflow_commands | id | actor_id | command_uuid | aggregate_type, aggregate_id, status | No | Yes | actor_id | belongsTo actor |
| sppb_status_logs | id | sppb_header_id, workflow_instance_id, workflow_instance_step_id, actor_id | - | sppb_header_id, logged_at; command_uuid, action | No | Yes | actor_id, command_uuid, correlation_id | belongsTo sppb, workflow, step, actor |
| attachments | id | sppb_header_id, uploader_id | uuid, stored_name | sppb_header_id, created_at; checksum_sha256 | Yes | Yes | uploader_id | belongsTo sppb_header, uploader |
| goods_releases | id | sppb_header_id, plant_id, released_by_id | uuid, release_number | status, created_at | No | Yes | created_by_id, received_by_id | belongsTo sppb_header; hasMany goods_release_items |
| goods_release_items | id | goods_release_id, sppb_detail_id | - | is_checked | No | Yes | - | belongsTo goods_release, sppb_detail |
| document_templates | id | plant_id | uuid, code | document_type, is_active | No | Yes | created_by_id | hasMany document_generations |
| document_generations | id | document_template_id, plant_id, sppb_header_id, goods_release_id, generated_by_id | uuid, command_uuid, stored_name | plant_id, status, generated_at | No | Yes | generated_by_id, revoked_by_id | belongsTo sppb_header or goods_release; hasMany document_validations, document_pages |
| document_pages | id | document_generation_id | verification_uuid, qr_payload_checksum | page_checksum_sha256 | No | Yes | - | belongsTo document_generation; hasMany document_validations |
| document_validations | id | document_generation_id, document_page_id, actor_id | uuid | validation_result, verified_at | No | Yes | actor_id, ip hash, user agent hash | belongsTo document_generation, page, actor |
| document_accesses | id | user_id, plant_id | - | module, is_active | No | Yes | - | belongsTo user, plant |
| activity_logs | id | user_id | - | module, action, reference, created_at | No | Yes | user_id, ip_address, user_agent | belongsTo user |

## 4 MODEL RELATION

| Model | Relation | Target | Cardinality |
|---|---|---|---|
| Plant | hasMany | Department | One To Many |
| Department | belongsTo | Plant | Many To One |
| Plant | hasMany | Location | One To Many |
| Location | belongsTo | Plant | Many To One |
| Plant | hasMany | User | One To Many |
| User | belongsTo | Plant | Many To One |
| Department | hasMany | User | One To Many |
| User | belongsTo | Department | Many To One |
| User | belongsTo | User as Manager | Many To One |
| User | hasMany | User as Subordinates | One To Many |
| Unit | hasMany | Item | One To Many |
| Item | belongsTo | Unit | Many To One |
| Unit | hasMany | Asset | One To Many |
| Asset | belongsTo | Unit | Many To One |
| Plant | hasMany | Asset | One To Many |
| Asset | belongsTo | Plant | Many To One |
| Location | hasMany | Asset | One To Many |
| Asset | belongsTo | Location | Many To One |
| Position | hasMany | UserPosition | One To Many |
| User | hasMany | UserPosition | One To Many |
| User | belongsToMany | Position through UserPosition | Many To Many |
| WorkflowTemplate | hasMany | WorkflowStep | One To Many |
| WorkflowStep | belongsTo | WorkflowTemplate | Many To One |
| WorkflowTemplate | hasMany | WorkflowInstance | One To Many |
| WorkflowInstance | belongsTo | WorkflowTemplate | Many To One |
| SppbHeader | hasMany | SppbDetail | One To Many |
| SppbDetail | belongsTo | SppbHeader | Many To One |
| SppbDetail | belongsTo | Item | Many To One |
| SppbDetail | belongsTo | Asset | Many To One |
| SppbHeader | belongsTo | Plant | Many To One |
| SppbHeader | belongsTo | Department | Many To One |
| SppbHeader | belongsTo | User as Requester | Many To One |
| SppbHeader | belongsTo | Location as OriginLocation | Many To One |
| SppbHeader | belongsTo | Location as DestinationLocation | Many To One |
| SppbHeader | hasMany | Attachment | One To Many |
| Attachment | belongsTo | SppbHeader | Many To One |
| SppbHeader | hasMany | WorkflowInstance | One To Many |
| WorkflowInstance | belongsTo | SppbHeader | Many To One |
| WorkflowInstance | hasMany | WorkflowInstanceStep | One To Many |
| WorkflowInstanceStep | belongsTo | WorkflowInstance | Many To One |
| WorkflowInstanceStep | hasMany | WorkflowStepApprover | One To Many |
| WorkflowStepApprover | belongsTo | WorkflowInstanceStep | Many To One |
| WorkflowStepApprover | belongsTo | User as Approver | Many To One |
| WorkflowDelegation | belongsTo | User as Delegator | Many To One |
| WorkflowDelegation | belongsTo | User as Delegate | Many To One |
| SppbHeader | hasMany | SppbStatusLog | One To Many |
| SppbStatusLog | belongsTo | SppbHeader | Many To One |
| SppbHeader | hasMany | GoodsRelease | One To Many |
| GoodsRelease | belongsTo | SppbHeader | Many To One |
| GoodsRelease | hasMany | GoodsReleaseItem | One To Many |
| SppbDetail | hasMany | GoodsReleaseItem | One To Many |
| DocumentGeneration | belongsTo | SppbHeader | Many To One |
| DocumentGeneration | belongsTo | GoodsRelease | Many To One |
| DocumentGeneration | hasMany | DocumentValidation | One To Many |
| DocumentTemplate | hasMany | DocumentGeneration | One To Many |
| DocumentGeneration | belongsTo | DocumentTemplate | Many To One |
| DocumentGeneration | hasMany | DocumentPage | One To Many |
| DocumentPage | belongsTo | DocumentGeneration | Many To One |
| DocumentPage | hasMany | DocumentValidation | One To Many |
| DocumentValidation | belongsTo | DocumentPage | Many To One |
| User | hasMany | EmailChangeRequest | One To Many |
| EmailChangeRequest | belongsTo | User | Many To One |

## 5 ENDPOINT LIST

| Method | URI | Action | Permission | Authentication | Description |
|---|---|---|---|---|---|
| GET | /api/v1/sppb | List SPPB | sppb.view_any | Sanctum Bearer | Menampilkan daftar SPPB sesuai scope user. |
| POST | /api/v1/sppb | Create Draft SPPB | sppb.create | Sanctum Bearer | Membuat draft SPPB. |
| GET | /api/v1/sppb/{uuid} | Show SPPB | sppb.view | Sanctum Bearer | Menampilkan detail SPPB. |
| PUT | /api/v1/sppb/{uuid} | Update Draft SPPB | sppb.update | Sanctum Bearer | Mengubah header draft/revision. |
| DELETE | /api/v1/sppb/{uuid} | Delete Draft SPPB | sppb.delete | Sanctum Bearer | Soft delete draft jika memenuhi aturan. |
| GET | /api/v1/sppb/{uuid}/details | List Detail | sppb.view | Sanctum Bearer | Menampilkan detail barang/aset SPPB. |
| POST | /api/v1/sppb/{uuid}/details | Add Detail | sppb.update | Sanctum Bearer | Menambahkan detail ke draft. |
| PUT | /api/v1/sppb/{uuid}/details/{detail_id} | Update Detail | sppb.update | Sanctum Bearer | Mengubah detail draft/revision. |
| DELETE | /api/v1/sppb/{uuid}/details/{detail_id} | Delete Detail | sppb.update | Sanctum Bearer | Menghapus detail dari draft. |
| GET | /api/v1/sppb/{uuid}/attachments | List Attachments | sppb.view | Sanctum Bearer | Menampilkan lampiran SPPB. |
| POST | /api/v1/sppb/{uuid}/attachments | Upload Attachment | sppb.update | Sanctum Bearer | Mengunggah lampiran header SPPB. |
| DELETE | /api/v1/sppb/{uuid}/attachments/{attachment_uuid} | Delete Attachment | sppb.update | Sanctum Bearer | Menghapus lampiran draft/revision. |
| POST | /api/v1/sppb/{uuid}/submit | Submit SPPB | sppb.submit | Sanctum Bearer | Submit draft dan generate workflow. |
| POST | /api/v1/sppb/{uuid}/cancel | Cancel SPPB | sppb.cancel | Sanctum Bearer | Membatalkan draft/submitted sesuai aturan. |
| POST | /api/v1/sppb/{uuid}/resubmit | Resubmit Revision | sppb.submit | Sanctum Bearer | Submit ulang setelah revision requested. |
| GET | /api/v1/sppb/{uuid}/status-logs | Status Logs | sppb.view | Sanctum Bearer | Menampilkan riwayat status SPPB. |
| GET | /api/v1/workflow/tasks | My Approval Tasks | workflow_task.view_any | Sanctum Bearer | Menampilkan daftar approval yang menunggu user. |
| GET | /api/v1/workflow/instances/{uuid} | Show Workflow | workflow.view | Sanctum Bearer | Menampilkan workflow instance. |
| POST | /api/v1/workflow/steps/{step_id}/approve | Approve Step | sppb.approve | Sanctum Bearer | Menyetujui step workflow. |
| POST | /api/v1/workflow/steps/{step_id}/reject | Reject Step | sppb.reject | Sanctum Bearer | Menolak step workflow. |
| POST | /api/v1/workflow/steps/{step_id}/revision | Request Revision | sppb.request_revision | Sanctum Bearer | Meminta revisi ke requester. |
| POST | /api/v1/workflow/delegations | Create Delegation | workflow_delegation.create | Sanctum Bearer | Membuat delegasi approval. |
| GET | /api/v1/workflow/delegations | List Delegation | workflow_delegation.view_any | Sanctum Bearer | Menampilkan delegasi aktif/historis. |
| PUT | /api/v1/workflow/delegations/{id} | Update Delegation | workflow_delegation.update | Sanctum Bearer | Mengubah delegasi yang belum berakhir. |
| DELETE | /api/v1/workflow/delegations/{id} | Cancel Delegation | workflow_delegation.delete | Sanctum Bearer | Menonaktifkan delegasi. |
| GET | /api/v1/goods-releases | List Goods Release | goods_release.view_any | Sanctum Bearer | Menampilkan daftar pelepasan barang. |
| GET | /api/v1/sppb/{uuid}/releasable-items | Releasable SPPB Items | sppb_header.view | Sanctum Bearer | Menampilkan sisa kuota barang SPPB pengiriman parsial. |
| POST | /api/v1/sppb/{uuid}/goods-releases | Create Goods Release | goods_release.create | Sanctum Bearer | Membuat pelepasan barang untuk SPPB approved. |
| GET | /api/v1/goods-releases/{uuid} | Show Goods Release | goods_release.view | Sanctum Bearer | Menampilkan detail pelepasan barang. |
| POST | /api/v1/documents/sppb/{uuid}/generate | Generate SPPB Document | document.generate | Sanctum Bearer | Membuat dokumen PDF SPPB. |
| GET | /api/v1/documents/{uuid} | Show Document | document.view | Sanctum Bearer | Menampilkan metadata dokumen. |
| GET | /api/v1/documents/{uuid}/download | Download Document | document.download | Sanctum Bearer | Mengunduh dokumen resmi. |
| GET | /api/v1/public/document-validations/{verification_uuid} | Public Verify Document | public | None / Rate Limited | Validasi QR dokumen publik. |

## 6 REQUEST DETAIL

### Global Headers

| Field | Type | Required | Nullable | Default | Validation | Enum | Length | Regex | Example | Description | Source |
|---|---|---:|---:|---|---|---|---|---|---|---|---|
| Accept | string | Yes | No | application/json | in:application/json | application/json | - | - | application/json | Wajib JSON. | HTTP |
| Content-Type | string | Conditional | No | application/json | in:application/json,multipart/form-data | - | - | - | application/json | JSON untuk body biasa, multipart untuk upload. | HTTP |
| Authorization | string | Yes except public | No | - | Bearer token | - | - | Bearer .+ | Bearer token | Token Sanctum/JWT. | Auth |
| X-Request-ID | uuid | No | Yes | generated | uuid | - | 36 | UUID | 7c2f7d2a-1302-45ac-a6e4-f4f7f8eb7b11 | Correlation ID. | Client/System |
| X-Idempotency-Key | uuid/string | Conditional | Yes | - | max:100 | - | 100 | - | submit-001 | Wajib untuk submit/approve/release/generate. | Client |

### GET /api/v1/sppb

| Parameter | Field | Type | Required | Nullable | Default | Validation | Enum | Length | Regex | Example | Description | Source |
|---|---|---|---:|---:|---|---|---|---|---|---|---|---|
| Query | page | integer | No | No | 1 | integer,min:1 | - | - | - | 1 | Nomor halaman. | Client |
| Query | per_page | integer | No | No | 15 | integer,min:1,max:100 | - | - | - | 25 | Jumlah data per halaman. | Client |
| Query | status | string | No | Yes | - | in | DRAFT,SUBMITTED,WAITING_BAT,WAITING_MANAGER,APPROVED,REJECTED,CANCELLED,REVISION_REQUESTED,RELEASE_IN_PROGRESS,COMPLETED,CLOSED | 30 | - | APPROVED | Filter status. | Client |
| Query | plant_id | integer | No | Yes | user scope | exists:plants,id | - | - | - | 1 | Filter Plant sesuai hak akses. | Client |
| Query | department_id | integer | No | Yes | - | exists:departments,id | - | - | - | 3 | Filter Department. | Client |
| Query | requester_id | integer | No | Yes | - | exists:users,id | - | - | - | 10 | Filter requester. | Client |
| Query | date_from | date | No | Yes | - | date | - | 10 | YYYY-MM-DD | 2026-07-01 | Awal tanggal request. | Client |
| Query | date_to | date | No | Yes | - | date,after_or_equal:date_from | - | 10 | YYYY-MM-DD | 2026-07-31 | Akhir tanggal request. | Client |
| Query | search | string | No | Yes | - | string,max:100 | - | 100 | - | SPPB-2026 | Keyword global. | Client |
| Query | sort | string | No | No | created_at | in | created_at,request_date,date_needed,document_number,status | 50 | - | created_at | Field sorting. | Client |
| Query | direction | string | No | No | desc | in:asc,desc | asc,desc | 4 | - | desc | Arah sorting. | Client |

### POST /api/v1/sppb

| Field | Type | Required | Nullable | Default | Validation | Enum | Length | Regex | Example | Description | Source |
|---|---|---:|---:|---|---|---|---|---|---|---|---|
| plant_id | integer | Yes | No | user plant | required,exists:plants,id | - | - | - | 1 | Plant SPPB. | Client/Auth |
| department_id | integer | Yes | No | user department | required,exists:departments,id | - | - | - | 4 | Department requester. | Client/Auth |
| origin_location_id | integer | Yes | No | - | required,exists:locations,id | - | - | - | 2 | Lokasi asal. | Client |
| destination_location_id | integer | Yes | No | - | required,exists:locations,id,different:origin_location_id | - | - | - | 5 | Lokasi tujuan. | Client |
| needed_name | string | No | Yes | null | nullable,string,max:255 | - | 255 | - | Proyek Line A | Nama kebutuhan. | Client |
| request_date | date | Yes | No | today UTC | required,date | - | 10 | YYYY-MM-DD | 2026-07-16 | Tanggal permintaan. | Client/System |
| date_needed | date | No | Yes | null | nullable,date,after_or_equal:request_date | - | 10 | YYYY-MM-DD | 2026-07-20 | Tanggal dibutuhkan. | Client |
| purpose | string | Yes | No | - | required,string,min:10,max:10000 | - | 10000 | - | Kebutuhan operasional | Keperluan SPPB. | Client |
| is_urgent | boolean | No | No | false | boolean | true,false | - | - | true | Tanda urgent. | Client |
| remarks | string | No | Yes | null | nullable,string,max:5000 | - | 5000 | - | Catatan | Catatan tambahan. | Client |

### POST /api/v1/sppb/{uuid}/details

| Field | Type | Required | Nullable | Default | Validation | Enum | Length | Regex | Example | Description | Source |
|---|---|---:|---:|---|---|---|---|---|---|---|---|
| barcode_confirmed | boolean | Yes | No | false | required,boolean | true,false | - | - | true | True untuk asset, false untuk item. | Client |
| item_id | integer | Conditional | Yes | null | required_if:barcode_confirmed,false,exists:items,id | - | - | - | 12 | Item non-aset. | Client |
| asset_id | integer | Conditional | Yes | null | required_if:barcode_confirmed,true,exists:assets,id | - | - | - | 88 | Aset berdasarkan barcode. | Client |
| unit_id | integer | Yes | No | derived | required,exists:units,id | - | - | - | 2 | Unit barang/aset. | Client/System |
| quantity | decimal | Yes | No | 1 | required,numeric,min:0.01,max:9999999999999999.99 | - | 18,2 | decimal | 2.00 | Jumlah permintaan. | Client |
| item_asset_name | string | Conditional | No | derived | required_without:item_id,asset_id,string,max:200 | - | 200 | - | Pompa Transfer | Nama barang/aset snapshot. | Client/System |
| reference_code | string | No | Yes | derived | nullable,string,max:100 | - | 100 | - | AST-001 | Kode item/barcode aset. | System |
| remarks | string | No | Yes | null | nullable,string,max:10000 | - | 10000 | - | Spesifikasi tambahan | Catatan detail. | Client |

### Mutating Workflow Requests

| Endpoint | Required Body | Business Validation |
|---|---|---|
| POST /api/v1/sppb/{uuid}/submit | note nullable string max:1000; lock_version required integer; idempotency_key required | SPPB DRAFT/REVISION_REQUESTED, minimal satu detail, template workflow aktif, approver tersedia. |
| POST /api/v1/sppb/{uuid}/resubmit | note nullable string max:1000; lock_version required integer; idempotency_key required | SPPB REVISION_REQUESTED, requester berwenang, detail valid. |
| POST /api/v1/sppb/{uuid}/cancel | reason required string min:10 max:2000 | Status belum terminal, requester/admin berwenang. |
| POST /api/v1/workflow/steps/{step_id}/approve | remarks nullable string max:2000; lock_version required integer; idempotency_key required | Actor adalah assigned approver atau delegate aktif. |
| POST /api/v1/workflow/steps/{step_id}/reject | reason required string min:10 max:2000; lock_version required integer; idempotency_key required | Actor adalah assigned approver atau delegate aktif. |
| POST /api/v1/workflow/steps/{step_id}/revision | reason required string min:10 max:2000; requested_fields nullable array | Actor adalah assigned approver atau delegate aktif. |

### POST /api/v1/sppb/{uuid}/attachments

| Field | Type | Required | Nullable | Default | Validation | Enum | Length | Regex | Example | Description | Source |
|---|---|---:|---:|---|---|---|---|---|---|---|---|
| file | file | Yes | No | - | required,file,mimes:pdf,jpg,jpeg,png,xlsx,docx,max:10240 | - | 10 MB | - | surat.pdf | File lampiran. | Client |
| description | string | No | Yes | null | nullable,string,max:255 | - | 255 | - | Lampiran kebutuhan | Deskripsi lampiran. | Client |

### POST /api/v1/workflow/delegations

| Field | Type | Required | Nullable | Default | Validation | Enum | Length | Regex | Example | Description | Source |
|---|---|---:|---:|---|---|---|---|---|---|---|---|
| delegate_id | integer | Yes | No | - | required,exists:users,id,different:delegator_id | - | - | - | 15 | User penerima delegasi. | Client |
| plant_id | integer | No | Yes | user plant | nullable,exists:plants,id | - | - | - | 1 | Scope Plant delegasi. | Client/Auth |
| starts_at | datetime | Yes | No | - | required,date,after_or_equal:now | - | ISO 8601 | - | 2026-07-16T00:00:00Z | Mulai delegasi UTC. | Client |
| ends_at | datetime | Yes | No | - | required,date,after:starts_at | - | ISO 8601 | - | 2026-07-20T00:00:00Z | Akhir delegasi UTC. | Client |
| reason | string | Yes | No | - | required,string,min:10,max:2000 | - | 2000 | - | Cuti tahunan | Alasan delegasi. | Client |

### POST /api/v1/sppb/{uuid}/goods-releases

| Field | Type | Required | Nullable | Default | Validation | Enum | Length | Regex | Example | Description | Source |
|---|---|---:|---:|---|---|---|---|---|---|---|---|
| release_date | date | Yes | No | today UTC | required,date | - | 10 | YYYY-MM-DD | 2026-07-16 | Tanggal pelepasan. | Client |
| carrier_name | string | No | Yes | null | nullable,string,max:150 | - | 150 | - | Kurir Internal | Pembawa barang. | Client |
| vehicle_number | string | No | Yes | null | nullable,string,max:30 | - | 30 | - | B 1234 CD | Nomor kendaraan. | Client |
| notes | string | No | Yes | null | nullable,string,max:2000 | - | 2000 | - | Partial release | Catatan pelepasan. | Client |
| items | array | Yes | No | - | required,array,min:1 | - | - | - | [] | Detail barang dilepas. | Client |
| items.*.sppb_detail_id | integer | Yes | No | - | required,exists:sppb_details,id,distinct | - | - | - | 123 | Detail SPPB. | Client |
| items.*.quantity | decimal | Yes | No | - | required,numeric,min:0.01 | - | 18,2 | - | 1.00 | Jumlah dilepas. | Client |

## 7 VALIDATION RULES

| Rule | Usage |
|---|---|
| required | Field wajib pada create, submit, approve tertentu. |
| nullable | Field boleh null. |
| sometimes | Field optional pada update partial. |
| unique | UUID, document_number, stored_name, command_uuid. |
| exists | FK ke Plant, Department, Location, User, Item, Asset, Unit, Workflow. |
| min | Minimal string, array, angka, dan file. |
| max | Maksimal panjang string, angka, array, dan ukuran file. |
| digits | Nomor tetap bila diperlukan pada NIK khusus. |
| date | request_date, date_needed, release_date. |
| after | ends_at setelah starts_at. |
| after_or_equal | date_needed setelah atau sama dengan request_date. |
| before | effective_until sebelum batas tertentu jika digunakan. |
| boolean | is_urgent, barcode_confirmed. |
| integer | id, page, per_page, lock_version. |
| numeric | quantity. |
| uuid | UUID resource dan idempotency/correlation bila format UUID. |
| email | email user pada endpoint referensi user jika dibuka. |
| regex | document number, barcode, request id bila diperlukan. |
| array | items, requested_fields, include. |
| distinct | items.*.sppb_detail_id, approver list. |
| confirmed | Untuk password endpoint auth bila module auth API dibuat. |
| mimes | pdf, jpg, jpeg, png, xlsx, docx. |
| image | jpg, jpeg, png jika lampiran gambar. |
| file | upload lampiran. |
| extensions | pdf, jpg, jpeg, png, xlsx, docx. |

### Business Validation

| Rule | Description |
|---|---|
| plant_scope | Plant harus berada dalam scope user login. |
| department_scope | Department harus berada pada Plant yang sama. |
| location_scope | Origin location harus berada pada Plant SPPB. |
| destination_not_same | Destination location tidak boleh sama dengan origin. |
| active_master | Plant, Department, Location, Item, Asset, Unit harus aktif. |
| asset_available | Asset harus aktif dan status memungkinkan dipilih. |
| item_or_asset_exclusive | Detail harus memilih tepat satu antara item_id atau asset_id. |
| draft_only_mutation | Header/detail/attachment hanya bisa diubah pada DRAFT atau REVISION_REQUESTED. |
| minimum_detail | Submit membutuhkan minimal satu detail. |
| workflow_template_available | Submit membutuhkan template workflow aktif. |
| approver_available | Semua step workflow harus memiliki approver aktif. |
| authorized_approver | Approver harus assigned atau delegate aktif. |
| no_self_approval | Self approval dilarang kecuali workflow step mengizinkan. |
| lock_version_match | Mutasi penting harus sesuai lock_version. |
| idempotent_command | Command UUID/idempotency key tidak boleh diproses dua kali. |
| rejection_reason_required | Reject wajib reason. |
| revision_reason_required | Request revision wajib reason. |
| cancellation_reason_required | Cancel wajib reason. |
| approved_only_release | Goods release hanya untuk SPPB APPROVED atau RELEASE_IN_PROGRESS. |
| release_quantity_limit | Quantity release tidak boleh melebihi sisa quantity. |
| official_document_once | Dokumen resmi terbaru menggantikan versi resmi sebelumnya. |
| public_verify_rate_limit | Validasi QR publik wajib rate limited. |

## 8 RESPONSE CONTRACT

### Standard Success Structure

| Field | Type | Description |
|---|---|---|
| success | boolean | Selalu true untuk response berhasil. |
| message | string | Pesan ringkas Bahasa Indonesia. |
| data | object/array/null | Payload utama. |
| meta | object/null | Pagination, filter, sorting, atau metadata proses. |
| links | object/null | Link pagination/download/action bila relevan. |
| errors | null | Null pada success. |
| timestamp | datetime | UTC ISO 8601. |
| request_id | string | Correlation ID. |

### Success Example

```json
{
  "success": true,
  "message": "Data SPPB berhasil ditampilkan.",
  "data": {
    "uuid": "9a64c2f1-0f2a-4f6a-8c44-1d94a57d3f11",
    "document_number": "SPPB/2026/07/0001",
    "status": "DRAFT"
  },
  "meta": null,
  "links": null,
  "errors": null,
  "timestamp": "2026-07-16T00:00:00Z",
  "request_id": "7c2f7d2a-1302-45ac-a6e4-f4f7f8eb7b11"
}
```

### Paginated Success Schema

```json
{
  "success": true,
  "message": "Daftar SPPB berhasil ditampilkan.",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "from": 1,
    "to": 15,
    "total": 120,
    "last_page": 8,
    "sort": "created_at",
    "direction": "desc",
    "filters": {}
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "errors": null,
  "timestamp": "2026-07-16T00:00:00Z",
  "request_id": "..."
}
```

### Error Contracts

| Type | HTTP Status | Message | Errors |
|---|---:|---|---|
| Validation Error | 422 | Validasi data gagal. | Field-level errors. |
| Unauthorized | 401 | Autentikasi diperlukan. | Token invalid/expired. |
| Forbidden | 403 | Anda tidak memiliki akses. | Permission/scope violation. |
| Conflict | 409 | Data sudah berubah atau status tidak valid. | Business conflict. |
| Not Found | 404 | Data tidak ditemukan. | Resource not found/scope hidden. |
| Internal Error | 500 | Terjadi kesalahan sistem. | Safe error code only. |

### Validation Error Example

```json
{
  "success": false,
  "message": "Validasi data gagal.",
  "data": null,
  "meta": null,
  "links": null,
  "errors": {
    "destination_location_id": [
      "Lokasi tujuan tidak boleh sama dengan lokasi asal."
    ]
  },
  "timestamp": "2026-07-16T00:00:00Z",
  "request_id": "..."
}
```

## 9 BUSINESS RULES

1. Plant adalah scope organisasi tertinggi.
2. Company tidak boleh digunakan pada API, request, response, filter, atau policy scope.
3. User tidak aktif tidak dapat mengakses API.
4. Semua endpoint mutasi wajib authenticated.
5. Semua response wajib JSON UTF-8.
6. Semua timestamp response wajib UTC ISO 8601.
7. Draft hanya dapat dibuat oleh user dengan permission `sppb.create`.
8. Draft hanya dapat diedit oleh pembuat, admin berwenang, atau role dengan policy eksplisit.
9. SPPB yang bukan DRAFT atau REVISION_REQUESTED tidak dapat diubah headernya.
10. Detail hanya dapat ditambah, diubah, atau dihapus pada DRAFT atau REVISION_REQUESTED.
11. SPPB tidak dapat disubmit tanpa detail.
12. Lokasi asal dan lokasi tujuan tidak boleh sama.
13. Origin location wajib berada pada Plant SPPB.
14. Department wajib berada pada Plant SPPB.
15. Requester wajib berada pada Plant yang sama kecuali policy admin lintas Plant mengizinkan.
16. Plant mengikuti hak akses user login.
17. Department default mengikuti department user login.
18. Requester default mengikuti user login.
19. Asset nonaktif tidak dapat dipilih.
20. Asset dengan status tidak tersedia tidak dapat dipilih jika kebijakan barang mensyaratkan ketersediaan.
21. Item nonaktif tidak dapat dipilih.
22. Unit nonaktif tidak dapat dipilih.
23. Detail harus memilih tepat satu: item atau asset.
24. Barcode aset harus unik.
25. Document UUID harus unik dan immutable.
26. Document number harus unik dan immutable setelah dibuat.
27. Submit wajib berjalan dalam database transaction.
28. Submit wajib membuat workflow instance.
29. Submit gagal jika template workflow aktif tidak ditemukan.
30. Submit gagal jika approver step tidak ditemukan atau tidak aktif.
31. Approval wajib dilakukan oleh approver assigned atau delegate aktif.
32. Manager hanya dapat approve step yang ditugaskan kepadanya.
33. BAT hanya dapat approve step yang ditugaskan kepadanya.
34. Approval tidak dapat dibatalkan.
35. Approval step yang sudah selesai tidak dapat diproses ulang.
36. Reject wajib alasan.
37. Reject membuat workflow terminal REJECTED.
38. Rejected tidak dapat diedit kecuali dibuat mekanisme reopen resmi.
39. Request revision wajib alasan.
40. Revision mengembalikan SPPB ke requester.
41. Resubmit wajib mempertahankan revision tracking.
42. Resubmit wajib membuat workflow instance revision yang valid.
43. Cancel wajib alasan.
44. Cancel hanya dapat dilakukan oleh requester atau role berwenang sebelum terminal state.
45. Closed tidak dapat diubah.
46. Completed tidak dapat diedit.
47. Goods release hanya dapat dibuat untuk SPPB APPROVED atau RELEASE_IN_PROGRESS.
48. Quantity release tidak boleh melebihi quantity yang belum dilepas.
49. Partial release mengubah status menjadi RELEASE_IN_PROGRESS.
50. Full release mengubah status menjadi COMPLETED.
51. Lampiran pada SPPB submitted tidak dapat dihapus kecuali policy revision mengizinkan.
52. File upload wajib divalidasi tipe, ukuran, checksum, dan scan status.
53. Semua mutasi penting wajib menulis status log.
54. Semua mutasi penting wajib menulis audit trail.
55. Concurrent approval wajib dilindungi lock_version atau row locking.
56. Idempotency key wajib untuk submit, approve, reject, revision, cancel, goods release, dan generate document.
57. Delegation hanya valid pada rentang waktu aktif.
58. Delegation tetap mencatat user asli dan user delegate.
59. SLA step dihitung dari activated_at.
60. Escalation tidak boleh mengubah approval tanpa catatan audit.

## 10 AUTHORIZATION

| Role | Permission | Policy | Ownership | Scope | Data Restriction |
|---|---|---|---|---|---|
| pemohon | sppb.view_any, sppb.view, sppb.create, sppb.update, sppb.delete, sppb.submit, sppb.cancel | SppbPolicy | Own SPPB | Own Plant/Department | Hanya SPPB sendiri kecuali diberi akses tambahan. |
| manager, ass_manager | sppb.view, sppb.approve, sppb.reject, sppb.request_revision, workflow_task.view_any | SppbPolicy, WorkflowPolicy | Assigned step | Plant/Department terkait | Hanya task yang assigned/delegated. |
| bat | sppb.view, sppb.approve, sppb.reject, sppb.request_revision, workflow_task.view_any | SppbPolicy, WorkflowPolicy | Assigned step | Plant terkait | Hanya task BAT yang assigned/delegated. |
| warehouse | sppb.view_any, sppb.view, goods_release.view_any, goods_release.view, goods_release.create | GoodsReleasePolicy | Operational | Plant terkait | Hanya SPPB approved/release/completed pada Plant. |
| auditor | sppb.view_any, sppb.view, workflow.view, audit.view, document.view | ReadOnlyPolicy | None | Assigned Plant/all by permission | Read-only, tidak boleh mutasi. |
| admin | master.manage, sppb.view_any, workflow.manage, role.view | Module Policies | Administrative | Assigned Plant | Tidak otomatis bypass super admin. |
| super_admin | all permissions | before policy allowed | Global | All Plant | Full access, dilindungi dari penghapusan terakhir. |

### Policy Requirements

- `viewAny`: berdasarkan permission dan scope Plant.
- `view`: berdasarkan permission, ownership, assigned task, atau audit role.
- `create`: user aktif dan memiliki Plant/Department valid.
- `update`: hanya DRAFT/REVISION_REQUESTED dan ownership/policy.
- `delete`: hanya draft, soft delete, dan policy.
- `submit`: requester sendiri atau permission khusus.
- `approve/reject/revision`: hanya approver assigned/delegate aktif.
- `cancel`: requester atau admin sebelum terminal state.
- `release`: warehouse/admin pada SPPB approved.
- `download document`: user dengan akses terhadap sumber dokumen.

## 11 WORKFLOW

### Normal Flow

```text
DRAFT
  -> submit
SUBMITTED
  -> workflow generated
WAITING_BAT
  -> BAT approve
WAITING_MANAGER
  -> Manager approve
APPROVED
  -> goods release partial
RELEASE_IN_PROGRESS
  -> goods release complete
COMPLETED
  -> administrative close
CLOSED
```

### Reject Flow

```text
WAITING_BAT / WAITING_MANAGER
  -> reject with reason
REJECTED
```

### Revision Flow

```text
WAITING_BAT / WAITING_MANAGER
  -> request revision
REVISION_REQUESTED
  -> requester update
DRAFT / REVISION_REQUESTED
  -> resubmit
SUBMITTED
  -> new workflow revision
WAITING_BAT / WAITING_MANAGER
```

### Cancel Flow

```text
DRAFT / SUBMITTED / REVISION_REQUESTED
  -> cancel with reason
CANCELLED
```

### Expired, Rollback, Reopen, Delegation, Escalation

| Flow | Contract |
|---|---|
| Expired | Step melewati `due_at`, status business tetap menunggu approval, sistem mencatat SLA breach dan mengirim escalation. |
| Rollback | Submit/approval/release gagal harus rollback seluruh transaksi dan menulis error aman bila transaksi memungkinkan. |
| Reopen | Hanya boleh tersedia jika bisnis mengaktifkan fitur resmi; wajib reason, policy, dan audit. |
| Delegation | Delegate aktif dapat bertindak atas approver utama; audit wajib menyimpan `delegated_from_id`. |
| Escalation | Escalation tidak melakukan auto approval; hanya notifikasi, assignment tambahan bila disetujui bisnis, dan audit. |

## 12 ERROR MATRIX

| Code | HTTP | Condition | Message | Resolution |
|---|---:|---|---|---|
| AUTH_REQUIRED | 401 | Token tidak dikirim | Autentikasi diperlukan. | Kirim Bearer token. |
| AUTH_INVALID | 401 | Token invalid/expired | Sesi tidak valid. | Login ulang. |
| USER_INACTIVE | 403 | User nonaktif | Akun tidak aktif. | Hubungi administrator. |
| PERMISSION_DENIED | 403 | Permission tidak cukup | Anda tidak memiliki akses. | Minta hak akses. |
| PLANT_SCOPE_DENIED | 403 | Akses lintas Plant | Data berada di luar scope Anda. | Pilih data sesuai Plant. |
| SPPB_NOT_FOUND | 404 | SPPB tidak ditemukan/scope hidden | SPPB tidak ditemukan. | Periksa UUID atau akses. |
| DETAIL_NOT_FOUND | 404 | Detail tidak ditemukan | Detail SPPB tidak ditemukan. | Muat ulang data. |
| ATTACHMENT_NOT_FOUND | 404 | Lampiran tidak ditemukan | Lampiran tidak ditemukan. | Pilih lampiran valid. |
| WORKFLOW_NOT_FOUND | 404 | Workflow tidak ditemukan | Workflow tidak ditemukan. | Periksa status SPPB. |
| INVALID_STATUS | 409 | Aksi tidak cocok dengan status | Status SPPB tidak mengizinkan aksi ini. | Muat ulang dan ikuti status terbaru. |
| LOCK_VERSION_CONFLICT | 409 | Data berubah concurrent | Data sudah berubah. | Muat ulang data. |
| IDEMPOTENCY_CONFLICT | 409 | Key sudah dipakai payload berbeda | Permintaan duplikat tidak konsisten. | Gunakan key baru. |
| EMPTY_DETAIL | 422 | Submit tanpa detail | Detail SPPB minimal satu. | Tambahkan detail. |
| SAME_LOCATION | 422 | Origin dan destination sama | Lokasi tujuan tidak boleh sama dengan lokasi asal. | Pilih lokasi berbeda. |
| INACTIVE_MASTER | 422 | Master data nonaktif | Data referensi tidak aktif. | Pilih data aktif. |
| ASSET_NOT_AVAILABLE | 422 | Asset tidak tersedia | Asset tidak dapat dipilih. | Pilih asset lain. |
| ITEM_ASSET_EXCLUSIVE | 422 | Item dan asset terisi bersamaan/kosong | Pilih tepat satu item atau asset. | Koreksi detail. |
| WORKFLOW_TEMPLATE_MISSING | 409 | Template aktif tidak ada | Template workflow aktif tidak ditemukan. | Hubungi administrator. |
| APPROVER_MISSING | 409 | Approver step tidak ada | Approver workflow tidak tersedia. | Perbaiki konfigurasi workflow. |
| APPROVER_UNAUTHORIZED | 403 | Actor bukan approver/delegate | Anda bukan approver untuk step ini. | Gunakan akun approver benar. |
| SELF_APPROVAL_DENIED | 403 | Self approval dilarang | Self approval tidak diperbolehkan. | Gunakan approver lain. |
| REASON_REQUIRED | 422 | Reject/cancel/revision tanpa alasan | Alasan wajib diisi. | Isi alasan. |
| RELEASE_NOT_ALLOWED | 409 | SPPB belum approved | Pelepasan hanya untuk SPPB approved. | Tunggu approval selesai. |
| RELEASE_QUANTITY_EXCEEDED | 422 | Quantity melebihi sisa | Jumlah pelepasan melebihi sisa. | Koreksi quantity. |
| FILE_INVALID | 422 | File tidak valid | File lampiran tidak valid. | Unggah file sesuai ketentuan. |
| FILE_TOO_LARGE | 422 | File terlalu besar | Ukuran file melebihi batas. | Kompres atau pilih file lain. |
| VIRUS_SCAN_FAILED | 422 | Scan gagal/terindikasi risiko | File tidak lolos pemeriksaan keamanan. | Unggah file aman. |
| DOCUMENT_NOT_READY | 409 | Dokumen belum generated | Dokumen belum siap. | Coba lagi nanti. |
| DOCUMENT_EXPIRED | 410 | Dokumen expired | Dokumen sudah kedaluwarsa. | Generate ulang. |
| RATE_LIMITED | 429 | Terlalu banyak request | Terlalu banyak permintaan. | Tunggu beberapa saat. |
| INTERNAL_ERROR | 500 | Error sistem | Terjadi kesalahan sistem. | Hubungi administrator dengan request_id. |

## 13 FILTERING

| Field | Operator | Default | Example |
|---|---|---|---|
| status | =, in | all allowed | status=APPROVED |
| plant_id | = | user scope | plant_id=1 |
| department_id | = | all allowed | department_id=4 |
| requester_id | = | all allowed | requester_id=10 |
| current_approver_id | = | all allowed | current_approver_id=15 |
| request_date | between, >=, <= | all | date_from=2026-07-01&date_to=2026-07-31 |
| date_needed | between, >=, <= | all | needed_from=2026-07-20 |
| is_urgent | = | all | is_urgent=true |
| origin_location_id | = | all | origin_location_id=2 |
| destination_location_id | = | all | destination_location_id=5 |
| document_number | like | all | document_number=SPPB/2026 |
| created_at | between | all | created_from=2026-07-01 |
| updated_at | between | all | updated_to=2026-07-31 |
| keyword | search | null | search=pompa |

## 14 SORTING

| Sortable Field | Ascending | Descending | Default Sort |
|---|---:|---:|---:|
| created_at | Yes | Yes | desc |
| updated_at | Yes | Yes | No |
| request_date | Yes | Yes | No |
| date_needed | Yes | Yes | No |
| document_number | Yes | Yes | No |
| status | Yes | Yes | No |
| plant_id | Yes | Yes | No |
| department_id | Yes | Yes | No |
| requester_id | Yes | Yes | No |
| submitted_at | Yes | Yes | No |
| approved_at | Yes | Yes | No |
| completed_at | Yes | Yes | No |

Default: `sort=created_at&direction=desc`.

## 15 SEARCHING

| Search Type | Supported | Fields | Case Sensitive | Notes |
|---|---:|---|---:|---|
| Global Search | Yes | document_number, purpose, needed_name, requester.name, department.name, plant.name | No | Query parameter `search`. |
| Field Search | Yes | document_number, barcode/reference_code, item_asset_name | No | Gunakan filter field khusus. |
| Full Text | Optional | purpose, remarks | No | Direkomendasikan jika volume besar. |
| Keyword | Yes | search | No | Trim, normalize whitespace. |
| Exact Search | Yes | uuid, document_number, barcode | No for document; exact for uuid | Untuk lookup detail. |

## 16 PAGINATION

| Item | Value |
|---|---|
| Default | page=1, per_page=15 |
| Minimum | per_page=1 |
| Maximum | per_page=100 |
| Recommended Large Dataset | Cursor pagination untuk audit/status log volume besar |
| Meta Response | current_page, per_page, from, to, total, last_page, sort, direction, filters |
| Links Response | first, last, prev, next |

## 17 PERFORMANCE

| Area | Recommendation |
|---|---|
| N+1 Prevention | Eager load plant, department, requester, originLocation, destinationLocation, details.unit, details.item, details.asset, attachments, currentWorkflowInstance. |
| Index Recommendation | Gunakan index pada plant_id/status/date_needed, requester_id/status/created_at, department_id/status/created_at, current_approver_id, workflow status/due_at. |
| Cache Recommendation | Cache master data aktif, permission, workflow template aktif, dan OpenAPI metadata. Jangan cache data draft pribadi tanpa invalidation. |
| Lazy Loading | Dilarang pada production untuk endpoint list. |
| Eager Loading | Wajib untuk response detail dan listing dengan includes. |
| Chunk | Gunakan chunk untuk export dan background processing. |
| Cursor Pagination | Direkomendasikan untuk status log, audit log, document validation. |
| Transaction Scope | Submit, approve, reject, revision, cancel, goods release, document official generation. |
| Locking | Gunakan row lock pada SPPB, workflow step, running number, dan goods release quantity. |
| Select Columns | Endpoint list hanya mengambil kolom ringkas. |
| File Handling | Upload dan generate document diproses asinkron bila berat. |
| Rate Limit | Pisahkan rate limit public verification dan authenticated API. |

## 18 AUDIT TRAIL

| Field | Required | Description |
|---|---:|---|
| created_by | Conditional | User pembuat resource. |
| updated_by | Conditional | User pengubah terakhir. |
| deleted_by | Conditional | User yang soft delete. |
| approved_by | Yes for approval | Approver step. |
| rejected_by | Yes for rejection | User yang reject. |
| cancelled_by | Yes for cancel | User yang cancel. |
| submitted_by | Yes for submit | Requester submit. |
| released_by | Yes for goods release | Warehouse/admin pelepas barang. |
| generated_by | Yes for document | User yang generate dokumen. |
| revoked_by | Conditional | User yang revoke dokumen. |
| ip_address | Yes | IP request, dapat di-hash untuk public validation. |
| device | Recommended | Device client bila tersedia. |
| browser | Recommended | Browser dari user agent. |
| platform | Recommended | OS/platform dari user agent. |
| request_id | Yes | Correlation ID. |
| activity | Yes | Nama aktivitas. |
| old_value | Conditional | Snapshot sebelum perubahan. |
| new_value | Conditional | Snapshot setelah perubahan. |
| reason | Conditional | Reject, revision, cancel, revoke, reopen. |
| timestamp | Yes | UTC ISO 8601. |

## 19 SECURITY

| Area | Requirement |
|---|---|
| Authentication | Bearer token via Sanctum/JWT-ready. |
| Authorization | Laravel Policy + Spatie Permission; tidak boleh hanya UI hiding. |
| Mass Assignment | Hanya field whitelist di DTO/Form Request. |
| Rate Limit | Authenticated API, workflow action, upload, document download, public QR validation. |
| CSRF | API stateless Bearer token tidak bergantung CSRF; web guard tetap mengikuti Laravel. |
| XSS | Escape output pada client; validasi string; response JSON aman. |
| SQL Injection | Eloquent/query builder parameter binding; tidak boleh raw dynamic SQL dari input. |
| IDOR | Semua lookup resource wajib policy + Plant scope. |
| Sensitive Field | password, token, remember_token, internal checksum tertentu tidak dikirim. |
| Hidden Field | deleted_at, internal lock jika tidak dibutuhkan, storage path fisik private. |
| Encryption | Token, private files at rest jika tersedia, backup terenkripsi. |
| File Upload Validation | MIME, extension, size, checksum, storage private, random stored name. |
| Virus Scan Recommendation | Scan async; file PENDING tidak boleh dianggap final bila policy mensyaratkan clean. |
| Audit | Forbidden, failed auth, workflow actions, download document wajib audit. |
| Transport | HTTPS wajib untuk staging/production. |
| CORS | Whitelist origin resmi. |
| Replay Protection | Idempotency key untuk mutasi kritikal. |
| Concurrency | lock_version dan DB transaction untuk workflow/release. |

## 20 API VERSIONING

| Item | Strategy |
|---|---|
| Version | v1 |
| URI Strategy | `/api/v1/...` |
| Header Strategy | Optional `Accept: application/vnd.e-sppb.v1+json`; default tetap `application/json`. |
| Deprecation | Breaking change diumumkan dengan `Deprecation` dan `Sunset` header. |
| Backward Compatibility | Field baru additive diperbolehkan; rename/remove/status semantic change wajib v2. |
| Error Compatibility | Error code tidak boleh diubah maknanya dalam v1. |
| Pagination Compatibility | Struktur `meta` dan `links` stabil selama v1. |
| OpenAPI Compatibility | Setiap endpoint wajib tercatat pada spesifikasi OpenAPI v3.1. |

## 21 OPENAPI MAPPING

| Endpoint | Summary | Tags | Operation ID | Request Schema | Response Schema | Security | Examples |
|---|---|---|---|---|---|---|---|
| GET /api/v1/sppb | List SPPB | SPPB | listSppb | SppbListQuery | PaginatedSppbCollection | bearerAuth | List approved SPPB |
| POST /api/v1/sppb | Create draft SPPB | SPPB | createSppb | CreateSppbRequest | SppbResource | bearerAuth | Create draft |
| GET /api/v1/sppb/{uuid} | Show SPPB | SPPB | showSppb | UuidPath + IncludeQuery | SppbDetailResource | bearerAuth | Show with details |
| PUT /api/v1/sppb/{uuid} | Update draft SPPB | SPPB | updateSppb | UpdateSppbRequest | SppbResource | bearerAuth | Update purpose |
| DELETE /api/v1/sppb/{uuid} | Delete draft SPPB | SPPB | deleteSppb | UuidPath | EmptySuccess | bearerAuth | Soft delete draft |
| GET /api/v1/sppb/{uuid}/details | List details | SPPB Detail | listSppbDetails | UuidPath | SppbDetailCollection | bearerAuth | List details |
| POST /api/v1/sppb/{uuid}/details | Add detail | SPPB Detail | addSppbDetail | CreateSppbDetailRequest | SppbDetailResource | bearerAuth | Add asset detail |
| PUT /api/v1/sppb/{uuid}/details/{detail_id} | Update detail | SPPB Detail | updateSppbDetail | UpdateSppbDetailRequest | SppbDetailResource | bearerAuth | Update quantity |
| DELETE /api/v1/sppb/{uuid}/details/{detail_id} | Delete detail | SPPB Detail | deleteSppbDetail | DetailPath | EmptySuccess | bearerAuth | Delete draft detail |
| POST /api/v1/sppb/{uuid}/submit | Submit SPPB | Workflow | submitSppb | SubmitSppbRequest | SppbWorkflowResource | bearerAuth | Submit draft |
| GET /api/v1/workflow/tasks | My tasks | Workflow | listMyWorkflowTasks | WorkflowTaskQuery | WorkflowTaskCollection | bearerAuth | Pending approvals |
| POST /api/v1/workflow/steps/{step_id}/approve | Approve step | Workflow | approveWorkflowStep | ApproveStepRequest | WorkflowActionResource | bearerAuth | Approve |
| POST /api/v1/workflow/steps/{step_id}/reject | Reject step | Workflow | rejectWorkflowStep | RejectStepRequest | WorkflowActionResource | bearerAuth | Reject |
| POST /api/v1/workflow/steps/{step_id}/revision | Request revision | Workflow | requestWorkflowRevision | RevisionStepRequest | WorkflowActionResource | bearerAuth | Revision |
| POST /api/v1/sppb/{uuid}/goods-releases | Create goods release | Goods Release | createGoodsRelease | CreateGoodsReleaseRequest | GoodsReleaseResource | bearerAuth | Partial release |
| POST /api/v1/documents/sppb/{uuid}/generate | Generate document | Document | generateSppbDocument | GenerateDocumentRequest | DocumentGenerationResource | bearerAuth | Generate PDF |
| GET /api/v1/public/document-validations/{verification_uuid} | Public document validation | Public Verification | verifyPublicDocument | VerificationPath | PublicDocumentValidationResource | none | QR verification |

## 22 IMPLEMENTATION ROADMAP

| Order | Layer | Deliverable | Notes |
|---:|---|---|---|
| 1 | Migration | Verify existing schema only | Jangan ubah schema tanpa ADR dan approval. |
| 2 | Model | Confirm relationships, casts, soft delete | Ikuti Laravel 12 casts method dan existing convention. |
| 3 | Factory | Factory untuk SPPB, detail, workflow, goods release | Untuk testing feature/API. |
| 4 | Seeder | Permission canonical, workflow template, master minimal | Idempotent, Spatie cache reset. |
| 5 | Policy | SppbPolicy, WorkflowPolicy, GoodsReleasePolicy, DocumentPolicy | Enforce Plant scope, ownership, assigned approver. |
| 6 | Form Request | Create/Update/Submit/Approve/Reject/Revision/Release/Upload requests | Semua validasi input dan business precondition ringan. |
| 7 | Service | SppbService, WorkflowService, GoodsReleaseService, DocumentGenerationService | Semua business rule dan transaction di service. |
| 8 | Controller | Thin API controllers | Hanya authorize, call request DTO/service, return resource. |
| 9 | API Resource | SppbResource, DetailResource, WorkflowResource, ErrorResource | Response contract konsisten. |
| 10 | Route | `/api/v1` route group | Sanctum/JWT middleware, throttle, bindings. |
| 11 | Feature Test | API contract tests | Happy path, validation, forbidden, conflict, not found. |
| 12 | Documentation | OpenAPI v3.1 spec | Dibuat setelah kontrak final disetujui. |
| 13 | Performance Validation | Query count, index check, pagination test | Pastikan endpoint list N+1 safe. |
| 14 | Security Validation | IDOR, permission, rate limit, upload validation | Wajib sebelum release. |
| 15 | Release Readiness | Regression suite, smoke test, health check | Pastikan backward compatibility v1. |
