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
        Schema::create('asset_import_completions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('requested_by_id')->constrained('users')->cascadeOnDelete();
            $table->string('stored_name', 255);
            $table->string('original_name', 255);
            $table->json('missing_barcodes')->comment('List of barcodes in DB but missing from upload');
            $table->string('status', 30)->default('PENDING')->comment('PENDING, PROCESSED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_import_completions');
    }
};
