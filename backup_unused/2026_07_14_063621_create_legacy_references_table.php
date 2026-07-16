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

        Schema::create('legacy_references', function (Blueprint $table) {
            $table->id();
            $table->string('source_system', 50);
            $table->string('source_table', 100);
            $table->string('legacy_id', 100);
            $table->string('target_type', 100);
            $table->unsignedBigInteger('target_id');
            $table->char('raw_hash', 64)->nullable();
            $table->timestamp('migrated_at');
            $table->unique(['source_system', 'source_table', 'legacy_id']);
            $table->index(['target_type', 'target_id']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_references');
    }
};
