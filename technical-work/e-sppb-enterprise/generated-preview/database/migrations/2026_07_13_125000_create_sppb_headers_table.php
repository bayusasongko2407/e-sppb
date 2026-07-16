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
        Schema::create('sppb_headers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('document_number', 50)->nullable()->unique();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('plant_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('origin_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('destination_location_id')->constrained('locations')->restrictOnDelete();
            $table->string('project_name', 255)->nullable();
            $table->date('request_date');
            $table->date('date_needed')->nullable();
            $table->longText('purpose');
            $table->boolean('is_urgent')->default(false)->index();
            $table->string('status', 30)->default('DRAFT')->index();
            $table->unsignedInteger('revision_no')->default(0);
            $table->unsignedInteger('current_step_sequence')->nullable();
            $table->foreignId('current_approver_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->longText('rejected_reason')->nullable();
            $table->longText('cancelled_reason')->nullable();
            $table->index(['requester_id', 'status', 'created_at']);
            $table->index(['department_id', 'status', 'created_at']);
            $table->index(['plant_id', 'status', 'date_needed']);
            $table->index('current_approver_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppb_headers');
    }
};
