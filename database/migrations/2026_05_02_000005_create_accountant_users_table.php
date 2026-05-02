<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accountant_users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('email', 255)->unique();
            $table->string('contact_number', 32)->nullable();
            $table->text('password');
            $table->string('address', 255)->nullable();
            $table->string('role', 120)->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->json('metadata')->default(json_encode([]));
            $table->timestamps(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accountant_users');
    }
};
