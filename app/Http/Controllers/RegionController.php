<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RegionController extends Controller
{
    /**
     * GET /api/wilayah
     * Daftar wilayah untuk dropdown berjenjang (publik).
     *
     * Query: level=PROVINSI|KOTA_KABUPATEN|KECAMATAN|KELURAHAN, parent_id=
     * Respons di-cache karena data wilayah jarang berubah.
     */
    public function index(Request $request)
    {
        $level    = $request->input('level');
        $parentId = $request->input('parent_id') ?? $request->input('parentId');

        $cacheKey = 'regions:index:' . strtoupper((string) $level) . ':' . (string) $parentId;

        $regions = Cache::remember($cacheKey, now()->addHours(12), function () use ($level, $parentId) {
            $query = Region::query();

            if ($level) {
                $query->where('level', strtoupper($level));
            }

            if ($parentId) {
                $query->where('parent_id', $parentId);
            }

            return $query->orderBy('name')
                ->get(['region_id', 'code', 'name', 'level', 'parent_id'])
                ->toArray();
        });

        return response()->json($regions);
    }

    /**
     * GET /api/wilayah/:id
     * Detail wilayah dengan rantai induk: provinsi > kota > kecamatan > kelurahan.
     */
    public function show(Region $region)
    {
        $data = Cache::remember('regions:show:' . $region->region_id, now()->addHours(12), function () use ($region) {
            $chain  = [];
            $cursor = $region;

            while ($cursor) {
                array_unshift($chain, [
                    'id'    => $cursor->region_id,
                    'name'  => $cursor->name,
                    'level' => $cursor->level,
                    'code'  => $cursor->code,
                ]);
                $cursor = $cursor->parent;
            }

            return [
                'region' => $region->only(['region_id', 'code', 'name', 'level']),
                'chain'  => $chain,
            ];
        });

        return response()->json($data);
    }
}
