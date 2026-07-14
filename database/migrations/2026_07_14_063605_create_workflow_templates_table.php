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

        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 30);
            $table->string('name', 150);
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('plant_id')->nullable()->constrained();
            $table->foreignId('department_id')->nullable()->constrained();
            $table->string('document_type', 30)->default('SPPB');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_until')->nullable();
            $table->unique(['code', 'version']);
            $table->index(['document_type', 'plant_id', 'department_id', 'is_active'], 'idx_wf_templates_resolver');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_templates');
    }
};
