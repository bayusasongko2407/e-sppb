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
        Schema::create('sppb_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sppb_header_id');
            $table->foreignId('workflow_instance_id')->nullable();
            $table->foreignId('workflow_instance_step_id')->nullable();
            $table->foreignId('actor_id')->nullable();
            $table->uuid('command_uuid')->nullable();
            $table->string('action', 40)->index();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('remarks')->nullable();
            $table->json('metadata')->nullable();
            $table->uuid('correlation_id')->nullable()->index();
            $table->timestamp('logged_at')->index();
            $table->index(['sppb_header_id', 'logged_at']);
            $table->index(['command_uuid', 'action']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppb_status_logs');
    }
};
