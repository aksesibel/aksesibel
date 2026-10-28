<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_histories', function (Blueprint $table) {
            $table->id('history_id');
            $table->foreignId('verification_id')->constrained('verifications', 'verification_id')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users');
            $table->foreignId('previous_status_id')->nullable()->constrained('verification_statuses', 'status_id');
            $table->foreignId('new_status_id')->constrained('verification_statuses', 'status_id');
            $table->text('notes')->nullable();
            $table->dateTime('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_histories');
    }
};
