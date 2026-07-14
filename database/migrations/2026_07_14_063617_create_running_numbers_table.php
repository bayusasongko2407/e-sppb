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
        Schema::create('running_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id');
            $table->foreignId('department_id')->nullable();
            $table->string('document_type', 30);
            $table->string('period_key', 12);
            $table->string('prefix', 30);
            $table->unsignedTinyInteger('digits')->default(4);
            $table->unsignedBigInteger('last_number')->default(0);
            $table->unsignedInteger('lock_version')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->unique(['plant_id', 'document_type', 'period_key']);
            $table->index(['document_type', 'period_key', 'is_active']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('running_numbers');
    }
};
