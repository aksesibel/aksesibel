<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_up_progress', function (Blueprint $table) {
            $table->id('progress_id');
            $table->foreignId('assignment_id')->constrained('follow_up_assignments', 'assignment_id')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users');
            $table->string('progress_status')->nullable();
            $table->text('action_description')->nullable();
            $table->dateTime('recorded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_progress');
    }
};
