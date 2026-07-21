<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create pivot table
        if (! Schema::hasTable('goods_release_sppb')) {
            Schema::create('goods_release_sppb', function (Blueprint $table) {
                $table->id();
                $table->foreignId('goods_release_id')->constrained('goods_releases')->cascadeOnDelete();
                $table->foreignId('sppb_header_id')->constrained('sppb_headers')->cascadeOnDelete();
                $table->unique(['goods_release_id', 'sppb_header_id']);
            });
        }

        // 2. Migrate existing data
        $releases = DB::table('goods_releases')->get();
        foreach ($releases as $release) {
            if ($release->sppb_header_id) {
                DB::table('goods_release_sppb')->insertOrIgnore([
                    'goods_release_id' => $release->id,
                    'sppb_header_id' => $release->sppb_header_id,
                ]);
            }
        }

        // 3. Make sppb_header_id nullable on goods_releases
        Schema::table('goods_releases', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['sppb_header_id']);
            // Drop unique constraint
            $table->dropUnique(['sppb_header_id', 'release_sequence']);
            // Make nullable
            $table->foreignId('sppb_header_id')->nullable()->change();
            // Re-add unique constraint
            $table->unique(['sppb_header_id', 'release_sequence']);
            // Re-add foreign key
            $table->foreign('sppb_header_id')->references('id')->on('sppb_headers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_releases', function (Blueprint $table) {
            $table->dropForeign(['sppb_header_id']);
            $table->dropUnique(['sppb_header_id', 'release_sequence']);
            $table->foreignId('sppb_header_id')->nullable(false)->change();
            $table->unique(['sppb_header_id', 'release_sequence']);
            $table->foreign('sppb_header_id')->references('id')->on('sppb_headers')->cascadeOnDelete();
        });

        Schema::dropIfExists('goods_release_sppb');
    }
};
