<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_expenses', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('job_id')
                  ->constrained('job_details')
                  ->cascadeOnDelete();

            $table->foreignId('expense_head_id')
                  ->constrained('expense_heads')
                  ->cascadeOnDelete();

            // Expense details
            $table->date('expense_date');
            $table->decimal('amount', 12, 2)->default(0);
            $table->char('currency', 3)->default('INR');
            $table->longText('note')->nullable();

            // Attachments tracking
            $table->boolean('has_attachments')->default(false);
            $table->unsignedInteger('attachments_count')->default(0);
            $table->longText('attachments_json')->nullable();

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            // Indexes
            $table->index(['job_id', 'expense_date']);
            $table->index('expense_head_id');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_expenses');
    }
};
