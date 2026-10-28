<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessibility_inspections', function (Blueprint $table) {
            $table->id('inspection_id');
            $table->foreignId('facility_accessibility_id')->constrained('facility_accessibilities', 'facility_accessibility_id')->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained('users');
            $table->date('inspection_date');
            $table->string('condition_result')->nullable();
            $table->text('notes')->nullable();
            $table->string('information_source')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessibility_inspections');
    }
};
