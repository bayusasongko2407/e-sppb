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

        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->string('document_type', 30);
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('plant_id')->nullable()->constrained();
            $table->string('renderer', 50)->default('HTML_PDF');
            $table->string('template_path', 500);
            $table->char('template_checksum_sha256', 64);
            $table->json('configuration')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_until')->nullable();
            $table->foreignId('created_by_id')->constrained('users')->restrictOnDelete();
            $table->unique(['code', 'version']);
            $table->index(['document_type', 'plant_id', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
            $table->index(['created_by_id', 'created_at']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
