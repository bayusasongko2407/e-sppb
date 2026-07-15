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

        Schema::create('document_generations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('command_uuid')->unique();
            $table->foreignId('document_template_id')->constrained();
            $table->unsignedInteger('template_version');
            $table->foreignId('plant_id')->constrained();
            $table->foreignId('sppb_header_id')->nullable()->constrained();
            $table->foreignId('goods_release_id')->nullable()->constrained();
            $table->string('document_type', 30);
            $table->string('document_number', 100);
            $table->unsignedInteger('source_revision_no')->default(0);
            $table->unsignedInteger('generation_no')->default(1);
            $table->foreignId('supersedes_id')->nullable()->constrained('document_generations')->nullOnDelete();
            $table->foreignId('generated_by_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('revoked_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('QUEUED')->index();
            $table->boolean('is_official')->default(false)->index();
            $table->string('plant_code_snapshot', 20);
            $table->string('plant_name_snapshot', 150);
            $table->json('render_payload');
            $table->char('source_checksum_sha256', 64);
            $table->string('disk', 50)->nullable();
            $table->string('directory', 255)->nullable();
            $table->string('stored_name', 255)->nullable()->unique();
            $table->string('path', 500)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->char('checksum_sha256', 64)->nullable()->index();
            $table->unsignedInteger('page_count')->default(0);
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->unique(['sppb_header_id', 'document_type', 'generation_no'], 'doc_gen_sppb_type_gen_unique');
            $table->unique(['goods_release_id', 'document_type', 'generation_no'], 'doc_gen_rel_type_gen_unique');
            $table->index(['plant_id', 'status', 'generated_at']);
            $table->index(['document_type', 'status', 'created_at']);
            $table->index(['sppb_header_id', 'status']);
            $table->index(['goods_release_id', 'status']);
            $table->index(['expires_at', 'status']);
            $table->index('supersedes_id');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_generations');
    }
};
