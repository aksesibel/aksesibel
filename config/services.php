<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Peta (Leaflet) — penyedia tile dapat diganti lewat variabel lingkungan
    |--------------------------------------------------------------------------
    | Default memakai tile standar OpenStreetMap. Untuk trafik produksi yang
    | tinggi, ganti ke penyedia lain sesuai kebijakan masing-masing penyedia.
    */
    'osm' => [
        'tile_url'         => env('OSM_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'tile_attribution' => env('OSM_TILE_ATTRIBUTION', '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'),
        'tile_max_zoom'    => (int) env('OSM_TILE_MAX_ZOOM', 19),
        'directions_url'   => env('OSM_DIRECTIONS_URL', 'https://www.openstreetmap.org/directions'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Geokoding — proxy Nominatim OpenStreetMap
    |--------------------------------------------------------------------------
    | Kebijakan Nominatim mewajibkan User-Agent yang jelas dan identitas
    | aplikasi. Ganti nilai di bawah dengan kontak yang benar sebelum
    | dipakai di produksi: https://operations.osmfoundation.org/policies/nominatim/
    */
    'geocoding' => [
        'user_agent'   => env('GEOCODING_USER_AGENT', 'Aksesibel/1.0 (+https://aksesibel.id; contact@aksesibel.id)'),
        'endpoint'     => env('GEOCODING_ENDPOINT', 'https://nominatim.openstreetmap.org/search'),
        'cache_hours'  => (int) env('GEOCODING_CACHE_HOURS', 24),
    ],

];
