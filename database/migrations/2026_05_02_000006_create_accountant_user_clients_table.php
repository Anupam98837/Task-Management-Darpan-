<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accountant_user_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accountant_user_id')->constrained('accountant_users')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->timestamps(0);

            $table->unique(['accountant_user_id', 'client_id'], 'accountant_user_clients_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accountant_user_clients');
    }
};
