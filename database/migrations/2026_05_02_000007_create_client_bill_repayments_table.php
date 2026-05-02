<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_bill_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_bill_id')->constrained('client_bills')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->date('repayment_date');
            $table->decimal('amount', 12, 2);
            $table->longText('note')->nullable();
            $table->boolean('has_attachments')->default(false);
            $table->unsignedInteger('attachments_count')->default(0);
            $table->longText('attachments_json')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->string('submitted_by_role', 40)->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('approved_by_role', 40)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_note')->nullable();
            $table->timestamps();

            $table->index(['client_bill_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['repayment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_bill_repayments');
    }
};
