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
        Schema::disableForeignKeyConstraints();

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->nullable()->constrained();
            $table->foreignId('location_id')->nullable()->constrained();
            $table->string('asset_location_name', 255)->nullable();
            $table->text('asset_location_address')->nullable();
            $table->string('barcode', 100)->unique();
            $table->string('condition', 20)->default('GOOD')->index();
            $table->string('status', 20)->default('AVAILABLE')->index();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->index(['plant_id', 'location_id', 'status', 'is_active']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
