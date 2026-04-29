<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fcm_tokens_assignee', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Link to assignee (keep column name as user_id)
            $table->unsignedBigInteger('user_id')->index();

            // ✅ Personal access token (nullable)
            $table->string('personal_access_token', 255)->nullable();

            // FCM token
            $table->string('fcm_assignee', 512);

            // Device/app metadata
            $table->string('platform', 20)->nullable();      // android / ios / web
            $table->string('device_id', 120)->nullable();    // Android ID / vendor id
            $table->string('device_model', 120)->nullable();
            $table->string('app_version', 40)->nullable();

            // Status + last seen
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();

            $table->timestamps();

            // One token should exist only once in the table
            $table->unique('fcm_assignee', 'uniq_fcm_assignee_token');

            // Helpful for per-user device queries
            $table->index(['user_id', 'device_id'], 'idx_assignee_user_device');

            // Optional: speed up lookups by personal token (if you query it)
            $table->index('personal_access_token', 'idx_assignee_pat');

            // FK -> assignee master table
            $table->foreign('user_id')
                ->references('id')->on('assigned_people')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens_assignee');
    }
};
