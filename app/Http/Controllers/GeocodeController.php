<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class GeocodeController extends Controller
{
    /**
     * POST /api/geocode
     * Proxy ke Nominatim OSM — hanya untuk admin.
     * Mematuhi kebijakan Nominatim: 1 req/detik, User-Agent jelas, cache hasil.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:200'],
        ], [
            'q.required' => 'Query alamat wajib diisi.',
            'q.min'      => 'Query terlalu pendek.',
        ]);

        $query = trim($request->input('q'));

        // Batasi laju per pengguna: maksimal 1 permintaan per detik.
        $userKey = 'geocode:user:' . ($request->user()?->id ?? $request->ip());
        if (RateLimiter::tooManyAttempts($userKey, 1)) {
            return response()->json([
                'error' => 'Terlalu banyak permintaan. Tunggu sebentar sebelum mencoba lagi.',
            ], 429);
        }

        // Batasi laju global agar tetap patuh pada kebijakan Nominatim (1 req/detik).
        if (!RateLimiter::attempt('geocode:global', 1, fn () => true, 1)) {
            return response()->json([
                'error' => 'Layanan geokoding sedang sibuk. Coba lagi sebentar lagi.',
            ], 429);
        }
        RateLimiter::hit($userKey, 1);

        // Cache berdasarkan query (hasil Nominatim stabil)
        $cacheKey = 'geocode:result:' . md5(strtolower($query));
        $cacheTtl = now()->addHours((int) config('services.geocoding.cache_hours', 24));
        $cached   = Cache::get($cacheKey);

        if ($cached) {
            return response()->json(['results' => $cached, 'cached' => true]);
        }

        $userAgent = config('services.geocoding.user_agent');
        $endpoint  = config('services.geocoding.endpoint');

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => $userAgent, 'Accept-Language' => 'id,en'])
                ->get($endpoint, [
                    'q'              => $query . ', Indonesia',
                    'format'         => 'json',
                    'addressdetails' => 1,
                    'limit'          => 5,
                    'countrycodes'   => 'id',
                ]);

            if (!$response->ok()) {
                return response()->json(['error' => 'Layanan geokoding tidak tersedia saat ini. Silakan isi koordinat secara manual.'], 503);
            }

            $results = collect($response->json())->map(fn ($r) => [
                'display_name' => $r['display_name'],
                'lat'          => (float) $r['lat'],
                'lng'          => (float) $r['lon'],
                'type'         => $r['type'] ?? null,
            ])->values()->all();

            Cache::put($cacheKey, $results, $cacheTtl);

            return response()->json(['results' => $results, 'cached' => false]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Layanan geokoding gagal dihubungi. Pastikan koneksi internet aktif, atau isi koordinat secara manual.',
            ], 503);
        }
    }
}
