<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->string('report_number')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('facility_id')->constrained('facilities', 'facility_id');
            $table->foreignId('issue_category_id')->constrained('issue_categories', 'issue_category_id');
            $table->foreignId('status_id')->constrained('verification_statuses', 'status_id');
            $table->foreignId('period_id')->constrained('reporting_periods', 'period_id');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
