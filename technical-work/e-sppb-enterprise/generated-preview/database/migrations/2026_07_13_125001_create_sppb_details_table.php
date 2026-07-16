<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sppb_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sppb_header_id');
            $table->unsignedInteger('line_no');
            $table->foreignId('item_id')->nullable();
            $table->foreignId('asset_id')->nullable();
            $table->string('item_type', 20);
            $table->string('item_code', 30)->nullable();
            $table->string('item_name', 200);
            $table->longText('specification')->nullable();
            $table->string('barcode', 100)->nullable()->index();
            $table->foreignId('unit_id');
            $table->string('unit_name', 100);
            $table->decimal('quantity', 18, 2);
            $table->decimal('approved_quantity', 18, 2)->nullable();
            $table->decimal('released_quantity', 18, 2)->default(0);
            $table->longText('remarks')->nullable();
            $table->unique(['sppb_header_id', 'line_no']);
            $table->index('item_id');
            $table->index('asset_id');
            $table->index('unit_id');
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE sppb_details ADD CONSTRAINT chk_quantity CHECK (quantity > 0)');
            DB::statement('ALTER TABLE sppb_details ADD CONSTRAINT chk_released_quantity CHECK (released_quantity <= approved_quantity)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppb_details');
    }
};
