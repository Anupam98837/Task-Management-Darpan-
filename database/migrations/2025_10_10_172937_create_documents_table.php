<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGSERIAL PK

            // Foreign Keys
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->cascadeOnDelete();

            $table->foreignId('document_type_id')
                  ->constrained('document_types')
                  ->cascadeOnDelete();

            // Main Fields
            $table->string('doc_name', 160);
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('issuing_authority', 160);
            $table->string('file_url', 255);

            // Generated or derived fields
            $table->text('stored_name')->virtualAs("concat('doc-', id)");

            // Actor Info
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('created_by_role', 32)->nullable();

            // Unique slug and status
            $table->string('slug', 140)->unique();
            $table->string('status', 20)->default('active'); // active,inactive,archived

            // Timestamps
            $table->timestampsTz();

            // Indexes
            $table->index(['client_id', 'document_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
