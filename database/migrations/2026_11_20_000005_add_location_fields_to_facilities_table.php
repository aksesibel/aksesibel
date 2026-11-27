<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            // Wilayah berjenjang (opsional, non-breaking — city lama tetap)
            $table->unsignedBigInteger('region_id')->nullable()->after('city');      // kelurahan
            $table->unsignedBigInteger('province_id')->nullable()->after('region_id');
            $table->unsignedBigInteger('city_region_id')->nullable()->after('province_id'); // kota/kabupaten
            $table->unsignedBigInteger('district_id')->nullable()->after('city_region_id'); // kecamatan

            // Koordinat
            $table->decimal('latitude', 10, 7)->nullable()->after('district_id');
            $table->decimal('longitude', 11, 7)->nullable()->after('latitude');
            $table->string('coordinate_source')->default('MANUAL')->after('longitude');
            $table->timestamp('coordinate_updated_at')->nullable()->after('coordinate_source');

            // Info lokasi tambahan
            $table->string('postal_code', 10)->nullable()->after('coordinate_updated_at');
            $table->text('location_note')->nullable()->after('postal_code');
            $table->text('entrance_note')->nullable()->after('location_note');
            $table->text('nearby_transit')->nullable()->after('entrance_note');

            // Foreign keys
            $table->foreign('region_id')->references('region_id')->on('regions')->nullOnDelete();
            $table->foreign('province_id')->references('region_id')->on('regions')->nullOnDelete();
            $table->foreign('city_region_id')->references('region_id')->on('regions')->nullOnDelete();
            $table->foreign('district_id')->references('region_id')->on('regions')->nullOnDelete();

            // Index
            $table->index(['latitude', 'longitude']);
            $table->index('city_region_id');
            $table->index('district_id');
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropForeign(['province_id']);
            $table->dropForeign(['city_region_id']);
            $table->dropForeign(['district_id']);
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropIndex(['city_region_id']);
            $table->dropIndex(['district_id']);
            $table->dropColumn([
                'region_id', 'province_id', 'city_region_id', 'district_id',
                'latitude', 'longitude', 'coordinate_source', 'coordinate_updated_at',
                'postal_code', 'location_note', 'entrance_note', 'nearby_transit',
            ]);
        });
    }
};
