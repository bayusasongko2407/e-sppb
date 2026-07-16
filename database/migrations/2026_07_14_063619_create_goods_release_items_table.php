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

        Schema::create('goods_release_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_release_id')->constrained();
            $table->foreignId('sppb_detail_id')->constrained();
            $table->decimal('quantity_requested', 18, 2);
            $table->decimal('quantity_released', 18, 2);
            $table->decimal('quantity_received', 18, 2)->default(0);
            $table->string('condition_on_release', 20)->nullable();
            $table->string('condition_on_receipt', 20)->nullable();
            $table->boolean('is_checked')->default(false);
            $table->text('notes')->nullable();
            $table->unique(['goods_release_id', 'sppb_detail_id']);
            $table->index('sppb_detail_id');
            $table->timestamps();
        });

        if (DB::getDriverName() !== 'sqlite') {

            DB::statement('ALTER TABLE goods_release_items ADD CONSTRAINT chk_gr_items_req CHECK (quantity_requested >= 0)');

        }
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE goods_release_items ADD CONSTRAINT chk_gr_items_rel CHECK (quantity_released >= 0)');
        }
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE goods_release_items ADD CONSTRAINT chk_gr_items_rec CHECK (quantity_received >= 0)');
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_release_items');
    }
};
