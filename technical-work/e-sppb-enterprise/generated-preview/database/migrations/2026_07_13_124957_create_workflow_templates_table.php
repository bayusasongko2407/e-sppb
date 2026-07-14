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
        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 30);
            $table->string('name', 150);
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('company_id')->nullable();
            $table->foreignId('plant_id')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->string('document_type', 30)->default('SPPB');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_until')->nullable();
            $table->unique(['code', 'version']);
            $table->index(['document_type', 'company_id', 'plant_id', 'department_id', 'is_active']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_templates');
    }
};
