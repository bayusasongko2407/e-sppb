<?php

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
        Schema::disableForeignKeyConstraints();

        Schema::create('sppb_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sppb_header_id')->constrained();
            $table->unsignedInteger('line_no');
            $table->boolean('barcode_confirmed')->default(false);
            $table->foreignId('item_id')->nullable()->constrained();
            $table->foreignId('asset_id')->nullable()->constrained();
            $table->string('reference_code', 100)->nullable()->index();
            $table->boolean('is_from_master')->default(false);
            $table->string('item_asset_name', 200);
            $table->foreignId('unit_id')->constrained();
            $table->decimal('quantity', 18, 2);
            $table->longText('remarks')->nullable();
            $table->string('delivery_status', 20)->nullable()->index();
            $table->unique(['sppb_header_id', 'line_no']);
            $table->index('item_id');
            $table->index('asset_id');
            $table->index('unit_id');
            $table->timestamps();
        });

        if (DB::getDriverName() !== 'sqlite') {

            DB::statement('ALTER TABLE sppb_details ADD CONSTRAINT chk_sppb_details_quantity CHECK (quantity >= 0)');

        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppb_details');
    }
};
