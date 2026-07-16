<?php

declare(strict_types=1);

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
        Schema::create('goods_release_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_release_id');
            $table->foreignId('sppb_detail_id');
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_release_items');
    }
};
