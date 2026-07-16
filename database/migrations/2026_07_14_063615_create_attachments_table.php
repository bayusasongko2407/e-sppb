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

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sppb_header_id')->constrained();
            $table->string('original_name', 255);
            $table->string('stored_name', 255)->unique();
            $table->string('disk', 50)->default('private');
            $table->string('directory', 255);
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->string('extension', 20);
            $table->unsignedBigInteger('file_size');
            $table->char('checksum_sha256', 64)->index();
            $table->foreignId('uploader_id')->constrained('users');
            $table->string('scan_status', 20)->default('PENDING')->index();
            $table->index(['sppb_header_id', 'created_at']);
            $table->index('uploader_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
