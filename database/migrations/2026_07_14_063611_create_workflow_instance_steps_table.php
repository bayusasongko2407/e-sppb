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

        Schema::create('workflow_instance_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained();
            $table->foreignId('workflow_step_id')->nullable()->constrained();
            $table->unsignedInteger('sequence');
            $table->string('code', 30);
            $table->string('name', 150);
            $table->string('approver_type', 30);
            $table->string('approval_mode', 20)->default('ANY');
            $table->unsignedInteger('minimum_approvals')->default(1);
            $table->unsignedInteger('sla_hours')->nullable();
            $table->string('status', 30)->default('QUEUED')->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('acted_at')->nullable();
            $table->foreignId('acted_by_id')->nullable()->constrained('users');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->unique(['workflow_instance_id', 'sequence']);
            $table->index(['status', 'due_at']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_instance_steps');
    }
};
