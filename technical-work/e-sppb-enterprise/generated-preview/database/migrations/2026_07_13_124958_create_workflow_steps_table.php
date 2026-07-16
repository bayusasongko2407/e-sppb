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
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id');
            $table->unsignedInteger('sequence');
            $table->string('code', 30);
            $table->string('name', 150);
            $table->string('approver_type', 30);
            $table->foreignId('approver_user_id')->nullable();
            $table->foreignId('approver_position_id')->nullable();
            $table->string('approver_role', 100)->nullable();
            $table->string('approval_mode', 20)->default('ANY');
            $table->unsignedInteger('minimum_approvals')->default(1);
            $table->unsignedInteger('sla_hours')->nullable();
            $table->boolean('allow_self_approval')->default(false);
            $table->boolean('is_final')->default(false);
            $table->json('configuration')->nullable();
            $table->unique(['workflow_template_id', 'sequence']);
            $table->unique(['workflow_template_id', 'code']);
            $table->index('approver_position_id');
            $table->index('approver_user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
