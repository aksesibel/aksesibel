<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facility_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('category_name');
            $table->string('icon')->default('Building2')->after('slug');
            $table->boolean('is_active')->default(true)->change();
            $table->integer('sort_order')->default(0)->after('description');
        });

        // Buat unique index untuk slug setelah kolom terisi oleh seeder
        // (index ditambah di migration berikutnya agar seeder bisa berjalan dulu)
    }

    public function down(): void
    {
        Schema::table('facility_categories', function (Blueprint $table) {
            $table->dropColumn(['slug', 'icon', 'sort_order']);
        });
    }
};
