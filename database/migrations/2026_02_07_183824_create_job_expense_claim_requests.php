<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_expense_claim_requests', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title', 191);

            // ✅ FK -> assigned_people.id (assignee who requested the claim)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('assigned_people')
                ->nullOnDelete();

            // FK -> job_details.id
            $table->foreignId('job_id')
                ->nullable()
                ->constrained('job_details')
                ->nullOnDelete();

            // FK -> job_expenses.id
            $table->foreignId('expense_id')
                ->nullable()
                ->constrained('job_expenses')
                ->nullOnDelete();

            $table->text('message')->nullable();

            $table->timestamp('requested_at')->useCurrent();

            // pending | paid | failed | partially paid
            $table->string('status', 20)->default('pending')->index();

            $table->json('payment_breakdown')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index(['job_id', 'expense_id']);
            $table->index('requested_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_expense_claim_requests');
    }
};
