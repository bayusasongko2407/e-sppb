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

        Schema::create('workflow_step_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_step_id')->constrained();
            $table->foreignId('approver_id')->constrained('users');
            $table->foreignId('delegated_from_id')->nullable()->constrained('users');
            $table->string('status', 20)->default('QUEUED')->index();
            $table->timestamp('acted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->unique(['workflow_instance_step_id', 'approver_id'], 'idx_wf_step_approver_unique');
            $table->index(['approver_id', 'status']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_step_approvers');
    }
};
