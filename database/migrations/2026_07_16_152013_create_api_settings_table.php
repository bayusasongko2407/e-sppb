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
        Schema::create('api_settings', function (Blueprint $table) {
            $table->id();
            $table->string('environment')->default('sandbox'); // sandbox, production
            $table->boolean('is_sandbox')->default(true);
            $table->boolean('is_mock_approval_enabled')->default(false);
            $table->string('webhook_url')->nullable();
            $table->integer('api_rate_limit')->default(60);
            $table->json('extra_config')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_settings');
    }
};
