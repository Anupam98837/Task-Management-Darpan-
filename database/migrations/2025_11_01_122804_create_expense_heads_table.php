<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_heads', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255); // e.g. Travel, Materials, Food

            $table->enum('status', ['active', 'inactive'])
                  ->default('active');

            $table->unsignedBigInteger('created_by')->nullable(); // FK → users(id), optional

            // Store the role name or role id — choose one
            $table->string('created_by_role', 100)->nullable(); // e.g. admin, accountant, etc.

            $table->timestampsTz();

            $table->index(['status', 'created_at']);
            $table->index('created_by');
            $table->index('created_by_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_heads');
    }
};
