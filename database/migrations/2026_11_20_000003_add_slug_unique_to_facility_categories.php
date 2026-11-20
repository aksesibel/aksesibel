<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Isi slug untuk baris lama yang belum memilikinya
        $rows = DB::table('facility_categories')->whereNull('slug')->orWhere('slug', '')->get();
        foreach ($rows as $row) {
            $base  = Str::slug($row->category_name, '-');
            $slug  = $base;
            $count = 1;
            while (DB::table('facility_categories')->where('slug', $slug)->where('category_id', '!=', $row->category_id)->exists()) {
                $slug = $base . '-' . $count++;
            }
            DB::table('facility_categories')
                ->where('category_id', $row->category_id)
                ->update(['slug' => $slug]);
        }

        // Sekarang aman untuk menjadikan slug NOT NULL dan UNIQUE
        Schema::table('facility_categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('facility_categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->string('slug')->nullable()->change();
        });
    }
};
