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

        Schema::create('goods_releases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('release_number', 50)->unique();
            $table->foreignId('sppb_header_id')->constrained();
            $table->unsignedInteger('release_sequence');
            $table->boolean('is_manual')->default(false);
            $table->foreignId('created_by_id')->constrained('users');
            $table->string('sender_name', 255);
            $table->text('sender_address')->nullable();
            $table->string('receiver_name', 255);
            $table->text('receiver_address')->nullable();
            $table->foreignId('sender_user_id')->nullable()->constrained('users');
            $table->foreignId('receiver_user_id')->nullable()->constrained('users');
            $table->string('driver_name', 100)->nullable();
            $table->string('vehicle_number', 50)->nullable();
            $table->string('expedition_name', 100)->nullable();
            $table->date('delivery_date')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by_id')->nullable()->constrained('users');
            $table->string('status', 20)->default('DRAFT')->index();
            $table->text('notes')->nullable();
            $table->char('verification_hash', 64)->unique();
            $table->unique(['sppb_header_id', 'release_sequence']);
            $table->index(['status', 'delivery_date']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_releases');
    }
};
