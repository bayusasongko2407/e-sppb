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

        Schema::create('workflow_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegator_id')->constrained('users');
            $table->foreignId('delegate_id')->constrained('users');
            $table->foreignId('plant_id')->nullable()->constrained();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->text('reason');
            $table->boolean('is_active')->default(true);
            $table->index(['delegator_id', 'starts_at', 'ends_at', 'is_active'], 'idx_wf_delegator_active');
            $table->index(['delegate_id', 'starts_at', 'ends_at', 'is_active'], 'idx_wf_delegate_active');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_delegations');
    }
};
