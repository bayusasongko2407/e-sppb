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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id');
            $table->foreignId('company_id');
            $table->foreignId('plant_id');
            $table->foreignId('location_id');
            $table->string('barcode', 100)->unique();
            $table->string('serial_number', 100)->nullable()->index();
            $table->string('condition', 20)->default('GOOD')->index();
            $table->string('status', 20)->default('AVAILABLE')->index();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->index(['plant_id', 'location_id', 'status', 'is_active']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
