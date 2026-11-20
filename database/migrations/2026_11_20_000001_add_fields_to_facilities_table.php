<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address');
            $table->string('phone')->nullable()->after('contact');
            $table->string('email')->nullable()->after('phone');
            $table->string('accessibility_status')->default('MENUNGGU_KONFIRMASI')->after('facility_status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn(['city', 'phone', 'email', 'accessibility_status', 'created_at', 'updated_at']);
        });
    }
};
