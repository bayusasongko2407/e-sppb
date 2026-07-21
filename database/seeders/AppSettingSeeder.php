<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Identitas Perusahaan
        AppSetting::set('company_name', 'PT Santos Jaya Abadi', 'general', 'string');
        AppSetting::set('company_address', 'Jl. Raya Gilang No. 159, Taman, Sidoarjo, Jawa Timur, Indonesia', 'general', 'string');
        AppSetting::set('company_phone', '+62-31-8971000', 'general', 'string');
        AppSetting::set('company_email', 'support@esppb.local', 'general', 'string');
        AppSetting::set('company_tax_id', '01.234.567.8-901.000', 'general', 'string');

        // 2. Visual & Branding
        AppSetting::set('app_custom_name', 'E-SPPB Enterprise', 'general', 'string');
        AppSetting::set('app_primary_color', '#0284c7', 'general', 'string'); // Sky-600
        AppSetting::set('logo_dark', null, 'general', 'string');
        AppSetting::set('logo_light', null, 'general', 'string');
        AppSetting::set('logo_height', 36, 'general', 'integer');
        AppSetting::set('logo_favicon', null, 'general', 'string');
        AppSetting::set('logo_login', null, 'general', 'string');
        AppSetting::set('logo_pdf', null, 'general', 'string');
        AppSetting::set('logo_pdf_position', 'left', 'general', 'string');
        AppSetting::set('logo_pdf_height', 40, 'general', 'integer');
        AppSetting::set('logo_pdf_show_address', true, 'general', 'boolean');

        // 3. Keamanan & Sesi
        AppSetting::set('security_session_timeout', 120, 'general', 'integer'); // 120 Menit
        AppSetting::set('security_login_limit', 5, 'general', 'integer');
        AppSetting::set('security_strong_password', true, 'general', 'boolean');

        // 4. Pengaturan Regional & Lokalisasi
        AppSetting::set('regional_timezone', 'Asia/Jakarta', 'general', 'string');
        AppSetting::set('regional_date_format', 'd/m/Y', 'general', 'string');
        AppSetting::set('regional_currency_symbol', 'Rp', 'general', 'string');
        AppSetting::set('regional_currency_code', 'IDR', 'general', 'string');
        AppSetting::set('regional_thousand_separator', '.', 'general', 'string');
        AppSetting::set('regional_decimal_separator', ',', 'general', 'string');

        // 5. Kendali Operasional Global
        AppSetting::set('op_maintenance_mode', false, 'general', 'boolean');
        AppSetting::set('op_maintenance_message', 'Sistem sedang dalam pemeliharaan rutin. Silakan coba beberapa saat lagi.', 'general', 'string');
        AppSetting::set('op_emergency_bypass', false, 'general', 'boolean');

        // 6. Kustomisasi Label & Istilah Aplikasi
        AppSetting::set('label_plant', 'Plant', 'general', 'string');
        AppSetting::set('label_department', 'Departemen', 'general', 'string');
        AppSetting::set('label_location', 'Lokasi', 'general', 'string');
        AppSetting::set('label_sppb', 'SPPB', 'general', 'string');
        AppSetting::set('label_goods_release', 'Surat Jalan', 'general', 'string');
        AppSetting::set('label_asset', 'Aset', 'general', 'string');
        AppSetting::set('label_item', 'Barang', 'general', 'string');
        AppSetting::set('label_unit', 'Satuan', 'general', 'string');
        AppSetting::set('label_qty', 'Qty', 'general', 'string');
        AppSetting::set('label_remarks', 'Keterangan', 'general', 'string');
        AppSetting::set('label_driver_name', 'Nama Pengemudi', 'general', 'string');
        AppSetting::set('label_vehicle_number', 'No Kendaraan', 'general', 'string');
        AppSetting::set('label_expedition_name', 'Ekspedisi', 'general', 'string');
        AppSetting::set('label_delivery_date', 'Tanggal Pengiriman', 'general', 'string');

        // 7. Status Dokumen & Alur Kerja
        AppSetting::set('label_status_draft', 'Draft', 'general', 'string');
        AppSetting::set('label_status_pending', 'Menunggu Persetujuan', 'general', 'string');
        AppSetting::set('label_status_approved', 'Disetujui', 'general', 'string');
        AppSetting::set('label_status_rejected', 'Ditolak', 'general', 'string');
        AppSetting::set('label_status_revision', 'Revisi', 'general', 'string');

        // 8. Aktor & Pihak Terkait
        AppSetting::set('label_actor_requester', 'Pemohon', 'general', 'string');
        AppSetting::set('label_actor_approver', 'Penyetuju', 'general', 'string');
        AppSetting::set('label_actor_receiver', 'Penerima', 'general', 'string');

        // 9. Status Pengiriman
        AppSetting::set('label_shipment_transit', 'Dalam Pengiriman', 'general', 'string');
        AppSetting::set('label_shipment_delivered', 'Terkirim', 'general', 'string');

        // 10. Detail Fisik & Informasi Kebutuhan
        AppSetting::set('label_asset_barcode', 'Barcode/Kode', 'general', 'string');
        AppSetting::set('label_sppb_purpose', 'Keperluan', 'general', 'string');

        // 11. Pengaturan Notifikasi Sistem (In-App)
        AppSetting::set('notify_system_enabled', true, 'notification', 'boolean');
        AppSetting::set('notify_system_retention_days', 30, 'notification', 'integer');
        AppSetting::set('notify_event_sppb_created', true, 'notification', 'boolean');
        AppSetting::set('notify_event_approval_requested', true, 'notification', 'boolean');
        AppSetting::set('notify_event_approval_stage_updated', true, 'notification', 'boolean');
        AppSetting::set('notify_event_sppb_approved', true, 'notification', 'boolean');
        AppSetting::set('notify_event_sppb_rejected_revised', true, 'notification', 'boolean');
        AppSetting::set('notify_event_goods_released', true, 'notification', 'boolean');

        // 12. Pengaturan Notifikasi Email (SMTP)
        AppSetting::set('notify_email_enabled', false, 'notification', 'boolean');
        AppSetting::set('mail_driver', 'smtp', 'notification', 'string');
        AppSetting::set('mail_host', '127.0.0.1', 'notification', 'string');
        AppSetting::set('mail_port', 1025, 'notification', 'integer');
        AppSetting::set('mail_username', '', 'notification', 'string');
        AppSetting::set('mail_password', '', 'notification', 'string');
        AppSetting::set('mail_from_address', 'no-reply@esppb.perusahaan.com', 'notification', 'string');
        AppSetting::set('mail_from_name', 'E-SPPB Enterprise', 'notification', 'string');

        // 13. Pengaturan Notifikasi WhatsApp (OpenWA)
        AppSetting::set('notify_wa_enabled', false, 'notification', 'boolean');
        AppSetting::set('wa_server_url', 'http://127.0.0.1:3000/send-message', 'notification', 'string');
        AppSetting::set('wa_api_secret', '', 'notification', 'string');
        AppSetting::set('wa_sender_number', '', 'notification', 'string');
    }
}
