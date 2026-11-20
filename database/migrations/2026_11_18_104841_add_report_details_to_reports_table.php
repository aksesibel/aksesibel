
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('report_title')->after('report_number');
            $table->date('incident_date')->after('description');
            $table->text('specific_location')->nullable()->after('incident_date');
            $table->boolean('is_anonymous')->default(false)->after('specific_location');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn([
                'report_title',
                'incident_date',
                'specific_location',
                'is_anonymous',
            ]);
        });
    }
};
