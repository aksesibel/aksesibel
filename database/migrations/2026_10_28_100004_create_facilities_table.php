<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id('facility_id');
            $table->foreignId('category_id')->constrained('facility_categories', 'category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->text('operating_hours')->nullable();
            $table->string('contact')->nullable();
            $table->string('facility_status')->default('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
