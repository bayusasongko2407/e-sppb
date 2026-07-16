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
        Schema::dropIfExists('legacy_references');

        Schema::table('sppb_headers', function (Blueprint $table) {
            $table->dropColumn(['legacy_fppb_hash', 'legacy_sj_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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

        Schema::table('sppb_headers', function (Blueprint $table) {
            $table->string('legacy_fppb_hash', 64)->nullable()->after('needed_name');
            $table->string('legacy_sj_number', 50)->nullable()->after('legacy_fppb_hash');
        });
    }
};
