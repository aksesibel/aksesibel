<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_up_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->foreignId('report_id')->constrained('reports', 'report_id')->cascadeOnDelete();
            $table->foreignId('assigned_to')->constrained('users');
            $table->foreignId('assigned_by')->constrained('users');
            $table->string('assignment_status')->default('assigned');
            $table->dateTime('assigned_at')->useCurrent();
            $table->date('due_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_assignments');
    }
};
