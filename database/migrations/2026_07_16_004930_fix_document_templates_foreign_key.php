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
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('document_templates', function (Blueprint $table) {
                $table->dropForeign('document_templates_created_by_id_foreign');
                $table->foreign('created_by_id')
                    ->references('id')
                    ->on('users')
                    ->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('document_templates', function (Blueprint $table) {
                $table->dropForeign('document_templates_created_by_id_foreign');
                $table->foreign('created_by_id')
                    ->references('id')
                    ->on('created_bies')
                    ->restrictOnDelete();
            });
        }
    }
};
