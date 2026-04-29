<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            // Primary Key
            $table->bigIncrements('id');

            // Basic info
            $table->string('name', 255);
            $table->string('org_type', 32)->nullable(); // company, hospital, clinic, ngo, individual, other

            // Contact info
            $table->string('email', 255)->nullable()->unique(); // unique + nullable
            $table->string('phone', 32)->nullable()->unique();  // added unique constraint
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postcode', 20)->nullable();
            $table->char('country', 2)->nullable(); // ISO-3166-1 alpha-2
            $table->string('timezone', 64)->nullable();
            $table->string('website_url', 255)->nullable();
            $table->string('image_url', 255)->nullable();

            // Contact person details
            $table->string('contact_name', 120)->nullable();
            $table->string('contact_email', 255)->nullable()->unique(); // added unique constraint
            $table->string('contact_phone', 32)->nullable()->unique();  // added unique constraint

            // Status
            $table->string('status', 20)->default('active'); // active, inactive, archived

            // Slug (unique human-friendly ID)
            $table->string('slug', 140)->unique();

            // Metadata
            $table->json('metadata')->nullable(); // JSONB equivalent in MySQL

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
