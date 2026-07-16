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
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropForeign(['approver_user_id']);
            $table->dropForeign(['approver_position_id']);
            $table->dropIndex(['approver_user_id']);
            $table->dropIndex(['approver_position_id']);

            $table->dropColumn(['approver_user_id', 'approver_position_id']);

            $table->json('approver_user_ids')->nullable()->after('approver_type');
            $table->json('approver_position_ids')->nullable()->after('approver_user_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropColumn(['approver_user_ids', 'approver_position_ids']);

            $table->foreignId('approver_user_id')->nullable()->constrained('users');
            $table->foreignId('approver_position_id')->nullable()->constrained('positions');
        });
    }
};
