# Aturan Beku Antigravity 2.0

## Sumber kanonik

- Panduan agen: `/www/wwwroot/e-sppb/Agents.md`
- Index konteks: `/www/wwwroot/e-sppb/.codex/00_INDEX.md`
- Blueprint beku: `/www/wwwroot/e-sppb/technical-work/e-sppb-enterprise/antigravity-blueprint.md`
- Desain database kanonik: `/www/wwwroot/e-sppb/technical-work/e-sppb-enterprise/draft.yaml`
- Laporan generator: `/www/wwwroot/e-sppb/technical-work/e-sppb-enterprise/build-report.md`
- Checkpoint: `/www/wwwroot/e-sppb/memory/continuation-log.md`

Jika lokasi repositori berbeda, temukan file dengan nama dan fungsi yang sama; jangan membuat salinan blueprint baru.

## Kontrak organisasi

```text
Plant
├── Department
├── Location
├── User
├── Asset
├── Workflow Template
└── SPPB
```

Tidak ada tingkat Company. Referensi Company pada generated preview lama adalah artefak superseded, bukan desain yang berlaku.

## Kontrak teknologi

- Laravel 12
- PHP 8.3
- MariaDB 10.11+
- Filament v5; baseline terverifikasi v5.6.8
- Spatie Laravel Permission v6
- Redis untuk queue/cache pada staging dan production
- Laravel Blueprint sebagai generator awal, bukan sumber kebenaran final

## Kontrak Filament

Gunakan API package yang terpasang. Baseline v5 memakai:

```php
use Filament\Schemas\Schema;

public static function form(Schema $schema): Schema
{
    return $schema->components([
        // Komponen Filament v5.
    ]);
}
```

Jangan memakai signature Filament v3 berikut:

```php
use Filament\Forms\Form;

public static function form(Form $form): Form
```

## Kontrak Bahasa Indonesia

Wajib terjemahkan semua teks pengguna: navigasi, judul, label, tombol, placeholder, bantuan, validasi, konfirmasi, notifikasi, filter, empty state, ekspor, laporan, dan pesan kegagalan yang aman.

Jangan menerjemahkan identifier teknis, nama class, nama method, nama tabel, nama kolom, enum value, role slug, atau permission slug.

## Kontrak role dan permission

- Role minimum: `super_admin`, `admin`, `requester`, `bat_approver`, `manager_approver`, `warehouse`, `auditor`.
- Permission manajemen: `role.view`, `role.manage`, `permission.view`, `permission.manage`.
- Gunakan pivot standar Spatie; jangan simpan `role_id` pada users.
- Audit perubahan role, permission, dan assignment.
- Lindungi role sistem dan super admin terakhir.
- Bersihkan permission cache setelah mutasi.

## Pemeriksaan cepat

```bash
rg -n "Company|company_id|CompanyResource" app database technical-work/e-sppb-enterprise/draft.yaml
rg -n "Filament\\\\Forms\\\\Form|form\(Form \\$form\)" app
rg -n '"name": "filament/filament"|"version": "v5\.' composer.lock
```

Nilai hasil pencarian dengan konteks. Penyebutan Company sebagai larangan dalam blueprint dapat diterima; model, schema, migration, relasi, dan UI Company tidak dapat diterima.
