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

        Schema::create('data_exports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('command_uuid')->unique();
            $table->foreignId('plant_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('requested_by_id')->constrained('users')->restrictOnDelete();
            $table->string('export_type', 50);
            $table->string('dataset', 100);
            $table->unsignedInteger('schema_version')->default(1);
            $table->string('format', 20);
            $table->string('status', 30)->default('QUEUED')->index();
            $table->json('scope_snapshot');
            $table->json('filters')->nullable();
            $table->json('columns');
            $table->json('options')->nullable();
            $table->string('disk', 50)->nullable();
            $table->string('directory', 255)->nullable();
            $table->string('stored_name', 255)->nullable()->unique();
            $table->string('path', 500)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->char('checksum_sha256', 64)->nullable()->index();
            $table->unsignedBigInteger('total_rows')->default(0);
            $table->unsignedBigInteger('processed_rows')->default(0);
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_downloaded_at')->nullable();
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->index(['plant_id', 'export_type', 'status']);
            $table->index(['requested_by_id', 'status', 'created_at']);
            $table->index(['dataset', 'format', 'status']);
            $table->index(['expires_at', 'status']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_exports');
    }
};
