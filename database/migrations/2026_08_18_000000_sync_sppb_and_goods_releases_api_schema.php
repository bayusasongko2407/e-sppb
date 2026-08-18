<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sppb_headers', function (Blueprint $table) {
            if (! Schema::hasColumn('sppb_headers', 'verification_hash')) {
                $table->char('verification_hash', 64)->nullable()->unique()->after('lock_version');
            }
            if (! Schema::hasColumn('sppb_headers', 'qr_code_url')) {
                $table->text('qr_code_url')->nullable()->after('verification_hash');
            }
        });

        Schema::table('sppb_status_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('sppb_status_logs', 'actor_name')) {
                $table->string('actor_name', 150)->nullable()->after('actor_id');
            }
            if (! Schema::hasColumn('sppb_status_logs', 'actor_nik')) {
                $table->string('actor_nik', 50)->nullable()->after('actor_name');
            }
            if (! Schema::hasColumn('sppb_status_logs', 'status')) {
                $table->string('status', 50)->nullable()->after('action');
            }
        });

        Schema::table('goods_releases', function (Blueprint $table) {
            if (! Schema::hasColumn('goods_releases', 'release_date')) {
                $table->dateTime('release_date')->nullable()->after('delivery_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sppb_headers', function (Blueprint $table) {
            if (Schema::hasColumn('sppb_headers', 'verification_hash')) {
                $table->dropColumn('verification_hash');
            }
            if (Schema::hasColumn('sppb_headers', 'qr_code_url')) {
                $table->dropColumn('qr_code_url');
            }
        });

        Schema::table('sppb_status_logs', function (Blueprint $table) {
            if (Schema::hasColumn('sppb_status_logs', 'actor_name')) {
                $table->dropColumn('actor_name');
            }
            if (Schema::hasColumn('sppb_status_logs', 'actor_nik')) {
                $table->dropColumn('actor_nik');
            }
            if (Schema::hasColumn('sppb_status_logs', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('goods_releases', function (Blueprint $table) {
            if (Schema::hasColumn('goods_releases', 'release_date')) {
                $table->dropColumn('release_date');
            }
        });
    }
};
