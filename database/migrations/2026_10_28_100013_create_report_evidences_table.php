<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_evidences', function (Blueprint $table) {
            $table->id('evidence_id');
            $table->foreignId('report_id')->constrained('reports', 'report_id')->cascadeOnDelete();
            $table->text('file_path');
            $table->string('file_type')->nullable();
            $table->dateTime('uploaded_at')->useCurrent();
            $table->text('caption')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_evidences');
    }
};
