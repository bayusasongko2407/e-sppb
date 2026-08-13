<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('UPDATE sppb_status_logs SET logged_at = DATE_ADD(logged_at, INTERVAL 7 HOUR), created_at = DATE_ADD(created_at, INTERVAL 7 HOUR), updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE logged_at IS NOT NULL');
            DB::statement('UPDATE sppb_headers SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR), updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE goods_releases SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR), updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');

            if (Schema::hasColumn('goods_releases', 'received_at')) {
                DB::statement('UPDATE goods_releases SET received_at = DATE_ADD(received_at, INTERVAL 7 HOUR) WHERE received_at IS NOT NULL');
            }

            DB::statement('UPDATE workflow_instances SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR), updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('UPDATE sppb_status_logs SET logged_at = DATE_SUB(logged_at, INTERVAL 7 HOUR), created_at = DATE_SUB(created_at, INTERVAL 7 HOUR), updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE logged_at IS NOT NULL');
            DB::statement('UPDATE sppb_headers SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR), updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE goods_releases SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR), updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');

            if (Schema::hasColumn('goods_releases', 'received_at')) {
                DB::statement('UPDATE goods_releases SET received_at = DATE_SUB(received_at, INTERVAL 7 HOUR) WHERE received_at IS NOT NULL');
            }

            DB::statement('UPDATE workflow_instances SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR), updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
        }
    }
};
