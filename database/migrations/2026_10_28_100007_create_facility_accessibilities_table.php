<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_accessibilities', function (Blueprint $table) {
            $table->id('facility_accessibility_id');
            $table->foreignId('facility_id')->constrained('facilities', 'facility_id')->cascadeOnDelete();
            $table->foreignId('accessibility_type_id')->constrained('accessibility_types', 'accessibility_type_id');
            $table->string('availability_status')->nullable();
            $table->string('condition_status')->nullable();
            $table->text('location_description')->nullable();
            $table->text('photo_path')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_accessibilities');
    }
};
