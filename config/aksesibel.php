<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Batas koordinat wilayah
    |--------------------------------------------------------------------------
    | Dipakai untuk memberi peringatan (bukan error) ketika admin menentukan
    | titik koordinat yang berada di luar Indonesia atau jauh dari provinsi
    | yang dipilih. Nilai berupa [selatan, barat, utara, timur] = [minLat,
    | minLng, maxLat, maxLng].
    |
    | Peringatan ini bersifat heuristik (bounding box kasar), bukan validasi
    | batas administratif yang presisi. Tambahkan provinsi lain sesuai
    | kebutuhan; bila kode provinsi tidak terdaftar, hanya batas Indonesia
    | yang dipakai.
    */
    'indonesia_bbox' => ['minLat' => -11.0, 'minLng' => 95.0, 'maxLat' => 6.0, 'maxLng' => 141.0],

    'province_bboxes' => [
        // DKI Jakarta
        '31' => ['minLat' => -6.45, 'minLng' => 106.60, 'maxLat' => -5.85, 'maxLng' => 107.05],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ukuran halaman & batas peta
    |--------------------------------------------------------------------------
    */
    'facilities_per_page' => (int) env('FACILITIES_PER_PAGE', 9),

    // Maksimal fitur GeoJSON per permintaan peta (lihat /api/fasilitas/peta)
    'map_max_features'    => (int) env('MAP_MAX_FEATURES', 500),

];
