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
        Schema::table('assets', function (Blueprint $table) {
            $table->string('asset_name', 1000)->nullable()->change();
        });

        Schema::table('sppb_details', function (Blueprint $table) {
            $table->string('item_asset_name', 1000)->change();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->string('name', 1000)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('asset_name', 255)->nullable()->change();
        });

        Schema::table('sppb_details', function (Blueprint $table) {
            $table->string('item_asset_name', 200)->change();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->string('name', 200)->change();
        });
    }
};
