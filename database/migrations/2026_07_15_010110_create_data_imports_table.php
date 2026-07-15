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

        Schema::create('data_imports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('command_uuid')->unique();
            $table->uuid('commit_command_uuid')->nullable()->unique();
            $table->foreignId('plant_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('requested_by_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('committed_by_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('import_type', 50);
            $table->unsignedInteger('schema_version')->default(1);
            $table->string('status', 30)->default('UPLOADED')->index();
            $table->string('scan_status', 20)->default('PENDING')->index();
            $table->string('original_name', 255);
            $table->string('stored_name', 255)->unique();
            $table->string('disk', 50)->default('private');
            $table->string('directory', 255);
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->string('extension', 20);
            $table->unsignedBigInteger('file_size');
            $table->char('checksum_sha256', 64)->index();
            $table->json('scope_snapshot')->nullable();
            $table->json('options')->nullable();
            $table->unsignedBigInteger('total_rows')->default(0);
            $table->unsignedBigInteger('valid_rows')->default(0);
            $table->unsignedBigInteger('invalid_rows')->default(0);
            $table->unsignedBigInteger('processed_rows')->default(0);
            $table->unsignedBigInteger('successful_rows')->default(0);
            $table->unsignedBigInteger('failed_rows')->default(0);
            $table->string('validation_report_disk', 50)->nullable();
            $table->string('validation_report_path', 500)->nullable();
            $table->char('validation_report_checksum_sha256', 64)->nullable();
            $table->timestamp('validation_started_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->index(['plant_id', 'import_type', 'status']);
            $table->index(['requested_by_id', 'status', 'created_at']);
            $table->index(['committed_by_id', 'completed_at']);
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
        Schema::dropIfExists('data_imports');
    }
};
