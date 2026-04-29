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
        Schema::create('assigned_people', function (Blueprint $table) {
            $table->id(); // Big Increments (Primary Key)
            $table->string('name', 160); // Full name
            $table->string('email', 255)->unique(); // Primary email (unique, case-insensitive)
            $table->string('contact_number', 32)->nullable(); // Phone or WhatsApp (optional)
            $table->text('password'); // BCrypt/Argon hash of password
            $table->string('address', 255)->nullable(); // Address (optional)
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active'); // Enum for status
            $table->json('metadata')->default(json_encode([])); // JSON metadata (optional)
            $table->timestamps(0); // Created & Updated timestamps (precision 0)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assigned_people');
    }
};
