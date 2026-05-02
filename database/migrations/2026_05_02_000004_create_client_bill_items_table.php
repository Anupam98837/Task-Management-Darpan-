<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_bill_id')->constrained('client_bills')->cascadeOnDelete();
            $table->foreignId('client_bill_head_id')->nullable()->constrained('client_bill_heads')->nullOnDelete();
            $table->string('bill_head_title', 255);
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('ordering')->default(0);
            $table->json('metadata')->nullable();
            $table->timestampsTz();

            $table->index(['client_bill_id', 'ordering']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_bill_items');
    }
};
