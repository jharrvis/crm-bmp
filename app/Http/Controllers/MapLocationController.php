<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class MapLocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:clients.view');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:3|max:120',
        ], [
            'q.min' => 'Masukkan minimal 3 karakter untuk mencari lokasi.',
        ]);

        $query = trim($validated['q']);
        $cacheKey = 'map-location-search:'.sha1(mb_strtolower($query));

        if (Cache::has($cacheKey)) {
            return response()->json(['data' => Cache::get($cacheKey)]);
        }

        if (RateLimiter::tooManyAttempts('map-location-search:nominatim', 1)) {
            return response()->json([
                'message' => 'Pencarian lokasi sedang dibatasi. Coba lagi dalam beberapa detik.',
            ], 429);
        }

        RateLimiter::hit('map-location-search:nominatim', 1);

        try {
            $response = Http::acceptJson()
                ->withUserAgent(config('maps.nominatim.user_agent'))
                ->withHeaders(['Referer' => config('app.url')])
                ->timeout(config('maps.nominatim.timeout'))
                ->get(config('maps.nominatim.url'), [
                    'q' => $query,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'countrycodes' => 'id',
                    'accept-language' => 'id',
                    'limit' => config('maps.nominatim.result_limit'),
                ])
                ->throw();
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Pencarian lokasi belum tersedia. Silakan tentukan titik secara manual di peta.',
            ], 503);
        }

        $results = collect($response->json())
            ->filter(fn ($item) => isset($item['lat'], $item['lon'], $item['display_name']))
            ->map(fn ($item) => [
                'label' => $item['display_name'],
                'latitude' => (float) $item['lat'],
                'longitude' => (float) $item['lon'],
                'type' => $item['type'] ?? null,
            ])
            ->values()
            ->all();

        Cache::put($cacheKey, $results, now()->addDays(config('maps.nominatim.cache_days')));

        return response()->json(['data' => $results]);
    }
}
