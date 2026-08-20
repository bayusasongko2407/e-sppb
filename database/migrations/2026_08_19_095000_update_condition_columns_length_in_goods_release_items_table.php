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
        Schema::table('goods_release_items', function (Blueprint $table) {
            $table->string('condition_on_release', 255)->nullable()->change();
            $table->string('condition_on_receipt', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_release_items', function (Blueprint $table) {
            $table->string('condition_on_release', 20)->nullable()->change();
            $table->string('condition_on_receipt', 20)->nullable()->change();
        });
    }
};
