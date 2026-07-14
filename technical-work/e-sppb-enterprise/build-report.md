# Laporan Build Awal Laravel Blueprint

> **Status artefak: SUPERSEDED / WAJIB BUILD ULANG**  
> Laporan ini mempertahankan jejak build awal tanggal 13 Juli 2026. Setelah pembekuan blueprint tanggal 14 Juli 2026, desain kanonik menghapus entitas `Company`, menetapkan Plant sebagai tingkat organisasi tertinggi, menambahkan kontrak manajemen role/permission, mewajibkan Bahasa Indonesia pada UI, dan mengunci implementasi pada Filament v5.6.8. Isi `generated-preview/` tidak boleh dipindahkan ke aplikasi utama dan bukan representasi `draft.yaml` terkini.

## Ringkasan

- Tanggal eksekusi: 2026-07-13 (Asia/Jakarta)
- Laravel: 12.x
- Laravel Blueprint: 2.13.0
- Desain kanonik: `technical-work/e-sppb-enterprise/draft.yaml`
- Lokasi eksekusi terisolasi: `technical-work/e-sppb-enterprise/generated-preview/`
- Jumlah model pada YAML: 27
- Model yang dihasilkan: 27
- Factory yang dihasilkan: 27
- Migration pada sandbox setelah build: 30, termasuk 3 migration bawaan Laravel
- Seeder yang dihasilkan: 9 ditambah `DatabaseSeeder` bawaan

## Status Eksekusi

Perintah Laravel Blueprint berhasil membaca dan mengeksekusi `draft.yaml`. Generator berhasil membuat model, factory, migration, dan seeder awal. Tidak ada file model atau migration aplikasi utama yang ditimpa karena proses dilakukan pada aplikasi Laravel 12 terisolasi.

## Temuan Build Awal yang Memerlukan Penyesuaian Tahap Berikutnya

1. Migration `users` hasil Blueprint bertabrakan dengan migration `users` bawaan Laravel 12. Tahap penyesuaian harus memilih strategi extend/replace dan menjaga tabel session/password reset.
2. Model `GoodsRelease` menghasilkan method `receivedBy()` ganda karena kolom dan alias relationship saling bertabrakan.
3. Beberapa alias relationship pada banyak foreign key menuju model yang sama tidak diinterpretasikan dengan benar oleh generator. Dampaknya terlihat pada factory berupa `use App\Models\;` dan pemanggilan `::factory()` tanpa nama model.
4. Blueprint menambahkan field konvensional seperti `creator_id`, `sender_id`, atau `received_by_id` dari relationship alias selain field eksplisit `created_by`, `sender_user_id`, dan `received_by`. Penamaan FK harus dinormalisasi pada YAML sebelum build final.
5. Migration `activity_logs` menghasilkan `created_at` eksplisit sekaligus `$table->timestamps()`, sehingga kolom `created_at` akan terdefinisi dua kali.
6. Circular reference `sppb_headers.current_workflow_instance_id` menuju `workflow_instances` memerlukan migration FK tahap kedua setelah kedua tabel tersedia.
7. Hasil generator belum menambahkan seluruh `constrained()`, `restrictOnDelete()`, `nullOnDelete()`, check constraint kuantitas, append-only enforcement, dan indeks khusus MariaDB sebagaimana ditetapkan Master Blueprint.
8. Hasil generator belum memakai `declare(strict_types=1);` dan generic PHPDoc Eloquent; ini memang menjadi pekerjaan penyesuaian setelah baseline generator disimpan.

## Hasil Validasi

- Sintaks YAML: lulus.
- Eksekusi `blueprint:build`: lulus.
- PHP lint seluruh artefak: belum lulus karena konflik relationship/factory yang dijelaskan di atas.
- `migrate:fresh`: belum lulus karena duplikasi migration tabel `users` bawaan dan hasil Blueprint.
- Tidak dilakukan penyesuaian terhadap hasil generator pada tahap ini.

## Rekomendasi Tahap Penyesuaian

1. Koreksi `draft.yaml`, terutama foreign key yang menuju `User`, dengan nama relationship/FK yang eksplisit dan kompatibel dengan sintaks Laravel Blueprint.
2. Jangan menghasilkan ulang tabel `users` secara paralel dengan migration bawaan; gunakan model `User` yang mengakomodasi autentikasi Laravel dan migration alter/add-columns terencana, atau hilangkan migration bawaan hanya pada proyek baru yang benar-benar bersih.
3. Pisahkan circular FK ke migration korektif setelah build.
4. Hapus `created_at` eksplisit dari model append-only bila generator tetap menambahkan timestamps, kemudian nonaktifkan `updated_at` melalui penyesuaian model/migration.
5. Jalankan build kedua di sandbox bersih, lalu wajibkan PHP lint dan `migrate:fresh` lulus sebelum artefak dipindahkan ke aplikasi utama.
6. Hapus seluruh model, migration, factory, seeder, relationship, dan field yang berkaitan dengan `Company`/`company_id`; seluruh scope organisasi dimulai dari Plant.
7. Publikasikan migration resmi Spatie Laravel Permission dan implementasikan manajemen role melalui service serta Filament v5, bukan melalui model hasil Laravel Blueprint.
8. Pada build/review berikutnya, tolak signature atau import Filament v3/v4. Acuan lokal yang telah diverifikasi adalah `filament/filament` v5.6.8 dan `Filament\Schemas\Schema`.
