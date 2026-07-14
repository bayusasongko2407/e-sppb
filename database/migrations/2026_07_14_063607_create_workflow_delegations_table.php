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
        Schema::create('workflow_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegator_id');
            $table->foreignId('delegate_id');
            $table->foreignId('plant_id')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->text('reason');
            $table->boolean('is_active')->default(true);
            $table->index(['delegator_id', 'starts_at', 'ends_at', 'is_active'], 'idx_wf_delegations_search');
            $table->index(['delegate_id', 'starts_at', 'ends_at', 'is_active'], 'idx_wf_delegates_search');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_delegations');
    }
};
