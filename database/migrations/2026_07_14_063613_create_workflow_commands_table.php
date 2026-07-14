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
        Schema::create('workflow_commands', function (Blueprint $table) {
            $table->id();
            $table->uuid('command_uuid')->unique();
            $table->string('command_type', 50);
            $table->string('aggregate_type', 100);
            $table->unsignedBigInteger('aggregate_id');
            $table->foreignId('actor_id');
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('QUEUED')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->index(['aggregate_type', 'aggregate_id', 'status']);
            $table->index(['actor_id', 'created_at']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_commands');
    }
};
