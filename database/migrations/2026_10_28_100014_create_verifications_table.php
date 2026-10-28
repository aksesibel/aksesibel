<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifications', function (Blueprint $table) {
            $table->id('verification_id');
            $table->foreignId('report_id')->constrained('reports', 'report_id')->cascadeOnDelete();
            $table->foreignId('verifier_id')->constrained('users');
            $table->string('verification_result')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
