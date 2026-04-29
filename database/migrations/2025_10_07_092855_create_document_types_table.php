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
        Schema::create('document_types', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary Key (BIGSERIAL)
            $table->string('name', 120)->unique(); // UQ Document type name
            $table->string('description', 255)->nullable(); // Optional
            $table->string('note', 255)->nullable(); // Optional internal note
            $table->string('status', 20)->default('active'); // active,inactive,archived
            $table->timestampTz('created_at')->useCurrent(); // Default NOW()
            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate(); // Default 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
