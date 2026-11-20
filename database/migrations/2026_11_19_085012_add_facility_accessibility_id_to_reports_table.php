<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('facility_accessibility_id')
                ->nullable()
                ->after('facility_id')
                ->constrained(
                    'facility_accessibilities',
                    'facility_accessibility_id'
                )
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['facility_accessibility_id']);
            $table->dropColumn('facility_accessibility_id');
        });
    }
};
