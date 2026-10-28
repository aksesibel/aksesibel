<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_up_evidences', function (Blueprint $table) {
            $table->id('follow_up_evidence_id');
            $table->foreignId('progress_id')->constrained('follow_up_progress', 'progress_id')->cascadeOnDelete();
            $table->text('file_path');
            $table->string('file_type')->nullable();
            $table->dateTime('uploaded_at')->useCurrent();
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_evidences');
    }
};
