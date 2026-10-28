<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_locations', function (Blueprint $table) {
            $table->id('location_id');
            $table->foreignId('facility_id')->unique()->constrained('facilities', 'facility_id')->cascadeOnDelete();
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('location_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_locations');
    }
};
