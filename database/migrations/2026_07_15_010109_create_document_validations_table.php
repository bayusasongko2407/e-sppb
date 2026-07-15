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

        Schema::create('document_validations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('document_generation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('document_page_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('validation_result', 20)->index();
            $table->string('verification_channel', 20)->default('PUBLIC_QR')->index();
            $table->char('lookup_fingerprint_sha256', 64)->nullable()->index();
            $table->char('request_fingerprint_sha256', 64)->nullable();
            $table->char('ip_address_hash_sha256', 64)->nullable();
            $table->char('user_agent_hash_sha256', 64)->nullable();
            $table->uuid('correlation_id')->nullable()->index();
            $table->timestamp('verified_at')->index();
            $table->json('metadata')->nullable();
            $table->index(['document_generation_id', 'verified_at']);
            $table->index(['document_page_id', 'verified_at']);
            $table->index(['validation_result', 'verified_at']);
            $table->index(['lookup_fingerprint_sha256', 'verified_at']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_validations');
    }
};
