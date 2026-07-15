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
        Schema::table('plants', function (Blueprint $table) {
            $table->renameColumn('description', 'address');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('asset_location_address');
            $table->renameColumn('asset_location_name', 'asset_location_data');
            $table->string('asset_name', 255)->after('location_id')->nullable();
            $table->foreignId('unit_id')->default(2)->after('status')->constrained()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
            $table->dropColumn('asset_name');
            $table->renameColumn('asset_location_data', 'asset_location_name');
            $table->text('asset_location_address')->nullable()->after('asset_location_name');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->text('description')->nullable();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->text('description')->nullable();
        });

        Schema::table('plants', function (Blueprint $table) {
            $table->renameColumn('address', 'description');
        });
    }
};
