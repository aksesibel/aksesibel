<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follow_up_assignments', function (Blueprint $table) {
            $table->text('assignment_notes')->nullable()->after('assignment_status');
        });
    }

    public function down(): void
    {
        Schema::table('follow_up_assignments', function (Blueprint $table) {
            $table->dropColumn('assignment_notes');
        });
    }
};

