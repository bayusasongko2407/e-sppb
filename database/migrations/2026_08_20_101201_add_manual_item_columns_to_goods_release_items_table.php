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
            $table->foreignId('sppb_detail_id')->nullable()->change();
            $table->decimal('quantity_requested', 18, 2)->nullable()->change();
            $table->string('item_name', 255)->nullable()->after('sppb_detail_id');
            $table->string('item_type', 50)->nullable()->after('item_name');
            $table->string('barcode_code', 100)->nullable()->after('item_type');
            $table->string('unit_name', 50)->nullable()->after('barcode_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_release_items', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'item_type', 'barcode_code', 'unit_name']);
            $table->decimal('quantity_requested', 18, 2)->nullable(false)->change();
            $table->foreignId('sppb_detail_id')->nullable(false)->change();
        });
    }
};
