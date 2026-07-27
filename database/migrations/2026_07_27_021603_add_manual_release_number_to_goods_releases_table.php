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
        Schema::table('goods_releases', function (Blueprint $table) {
            $table->string('manual_release_number', 50)->nullable()->after('release_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_releases', function (Blueprint $table) {
            $table->dropColumn('manual_release_number');
        });
    }
};
