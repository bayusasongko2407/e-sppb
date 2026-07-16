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

        Schema::create('document_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_generation_id')->constrained()->cascadeOnDelete();
            $table->uuid('verification_uuid')->unique();
            $table->unsignedInteger('page_number');
            $table->char('page_checksum_sha256', 64);
            $table->char('qr_payload_checksum_sha256', 64)->unique();
            $table->char('verification_token_hash', 64)->unique();
            $table->unique(['document_generation_id', 'page_number']);
            $table->index('page_checksum_sha256');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_pages');
    }
};
