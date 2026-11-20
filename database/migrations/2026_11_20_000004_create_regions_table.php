<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id('region_id');
            $table->string('code')->unique();          // Kode Kemendagri mis. "31", "31.71", "31.71.03"
            $table->string('name');
            $table->string('level');                   // PROVINSI | KOTA_KABUPATEN | KECAMATAN | KELURAHAN
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('region_id')->on('regions')->nullOnDelete();
            $table->index('parent_id');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
