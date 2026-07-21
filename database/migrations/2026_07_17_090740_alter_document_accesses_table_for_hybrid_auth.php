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
        Schema::table('document_accesses', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                // Drop foreign keys lama terlebih dahulu agar index bisa didrop
                $table->dropForeign('document_accesses_user_id_foreign');
                $table->dropForeign('document_accesses_plant_id_foreign');
                $table->dropForeign('document_accesses_department_id_foreign');

                // Drop unique index lama
                $table->dropUnique('doc_access_unique');
            }
        });

        Schema::table('document_accesses', function (Blueprint $table) {
            // Ubah kolom menjadi nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('plant_id')->nullable()->change();
            $table->unsignedBigInteger('department_id')->nullable()->change();

            // Tambahkan kolom role_id setelah user_id
            $table->foreignId('role_id')->nullable()->after('user_id')->constrained('roles')->cascadeOnDelete();

            if (DB::getDriverName() !== 'sqlite') {
                // Pasang kembali foreign key constraint
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('plant_id')->references('id')->on('plants')->cascadeOnDelete();
                $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();

                // Tambah unique constraint baru (hybrid)
                $table->unique(['user_id', 'role_id', 'plant_id', 'department_id', 'module'], 'doc_access_hybrid_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_accesses', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                // Drop foreign keys terlebih dahulu agar index bisa didrop
                $table->dropForeign('document_accesses_user_id_foreign');
                $table->dropForeign('document_accesses_plant_id_foreign');
                $table->dropForeign('document_accesses_department_id_foreign');
                $table->dropForeign(['role_id']);

                // Drop unique index baru
                $table->dropUnique('doc_access_hybrid_unique');
            }

            // Drop kolom
            $table->dropColumn('role_id');
        });

        Schema::table('document_accesses', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unsignedBigInteger('plant_id')->nullable(false)->change();
            $table->unsignedBigInteger('department_id')->nullable(false)->change();

            if (DB::getDriverName() !== 'sqlite') {
                // Pasang kembali unique index lama
                $table->unique(['user_id', 'plant_id', 'department_id', 'module'], 'doc_access_unique');

                // Pasang kembali foreign keys
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('plant_id')->references('id')->on('plants')->cascadeOnDelete();
                $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            }
        });
    }
};
