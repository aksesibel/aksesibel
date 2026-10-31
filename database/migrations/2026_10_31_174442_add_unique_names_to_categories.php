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
        $categoryKeys = ['facility_categories' => 'category_id', 'issue_categories' => 'issue_category_id'];

        foreach ($categoryKeys as $tableName => $primaryKey) {
            $categories = DB::table($tableName)->select($primaryKey, 'category_name')
                ->orderBy($primaryKey)->lazyById(500, $primaryKey);

            foreach ($categories as $category) {
                if ($category->category_name !== Str::trim($category->category_name)) {
                    throw new RuntimeException('Nama kategori pada '.$tableName.' (ID '.$category->{$primaryKey}.') memiliki spasi tepi. Rapikan nama dan tinjau duplikasi serta referensinya secara manual sebelum menjalankan migration; tidak ada data yang diubah otomatis.');
                }
            }

            $hasDuplicates = DB::table($tableName)
                ->select('category_name')
                ->groupBy('category_name')
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            if ($hasDuplicates) {
                throw new RuntimeException('Nama kategori duplikat pada '.$tableName.'. Tinjau dan satukan referensinya secara manual sebelum menjalankan migration; tidak ada kategori yang dihapus otomatis.');
            }
        }

        foreach (array_keys($categoryKeys) as $tableName) {
            if (! Schema::hasIndex($tableName, $tableName.'_category_name_unique', 'unique')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unique('category_name');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('issue_categories', function (Blueprint $table) {
            $table->dropUnique(['category_name']);
        });
        Schema::table('facility_categories', function (Blueprint $table) {
            $table->dropUnique(['category_name']);
        });
    }
};
