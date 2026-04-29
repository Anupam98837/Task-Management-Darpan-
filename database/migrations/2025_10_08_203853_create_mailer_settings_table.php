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
    Schema::create('mailer_settings', function (Blueprint $table) {
        $table->id();

        // Polymorphic owner (for admin/user linkage)
        $table->string('owner_type');   // e.g., 'admin' or 'user'
        $table->unsignedBigInteger('owner_id');

        // Optional label / display name
        $table->string('label')->nullable();

        // Mailer configuration
        $table->string('mailer')->default('smtp');
        $table->string('host')->nullable();
        $table->integer('port')->nullable();
        $table->string('username')->nullable();
        $table->string('password')->nullable();
        $table->string('encryption')->nullable();
        $table->string('from_address');
        $table->string('from_name');

        // Flags
        $table->boolean('is_default')->default(false);
        $table->enum('status', ['active', 'inactive'])->default('active');

        $table->timestamps();

        // Index for faster lookups by owner
        $table->index(['owner_type', 'owner_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailer_settings');
    }
};
