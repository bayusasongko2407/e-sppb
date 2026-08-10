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
        Schema::table('goods_releases', function (Blueprint $table) {
            $table->string('recipient_name', 255)->nullable()->after('received_by_id');
            $table->mediumText('recipient_signature')->nullable()->after('recipient_name');
            $table->text('receiving_notes')->nullable()->after('recipient_signature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_releases', function (Blueprint $table) {
            $table->dropColumn(['recipient_name', 'recipient_signature', 'receiving_notes']);
        });
    }
};
