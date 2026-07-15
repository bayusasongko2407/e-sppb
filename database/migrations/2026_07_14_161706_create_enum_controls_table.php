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
        Schema::create('enum_controls', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 50);
            $table->string('column_name', 50);
            $table->string('value', 100);
            $table->string('label', 150);
            $table->unsignedInteger('sequence')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['table_name', 'column_name', 'value']);
            $table->index(['table_name', 'column_name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enum_controls');
    }
};
