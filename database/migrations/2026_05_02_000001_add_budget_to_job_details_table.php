<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_details', function (Blueprint $table) {
            if (!Schema::hasColumn('job_details', 'budget')) {
                $table->decimal('budget', 12, 2)->nullable()->after('priority');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_details', function (Blueprint $table) {
            if (Schema::hasColumn('job_details', 'budget')) {
                $table->dropColumn('budget');
            }
        });
    }
};
