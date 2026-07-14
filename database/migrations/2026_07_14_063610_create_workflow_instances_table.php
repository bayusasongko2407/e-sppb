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
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workflow_template_id');
            $table->foreignId('sppb_header_id');
            $table->unsignedInteger('template_version');
            $table->unsignedInteger('revision_no')->default(0);
            $table->string('status', 30)->default('QUEUED')->index();
            $table->unsignedInteger('current_sequence')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('failure_code', 50)->nullable();
            $table->text('failure_message')->nullable();
            $table->unique(['sppb_header_id', 'revision_no']);
            $table->index(['status', 'current_sequence']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_instances');
    }
};
