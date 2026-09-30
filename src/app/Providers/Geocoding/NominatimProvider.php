<?php

namespace App\Providers\Geocoding;

use App\Contracts\GeocodingProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NominatimProvider implements GeocodingProvider
{
    private string $userAgent;

    public function __construct()
    {
        $this->userAgent = config('combustivel.geocoding.user_agent');
    }

    public function geocode(string $address): ?array
    {
        try {
            // Respect Nominatim Usage Policy: 1 request per second
            sleep(1);

            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful() && !empty($response->json())) {
                $result = $response->json()[0];
                return [
                    'latitude' => (float) $result['lat'],
                    'longitude' => (float) $result['lon'],
                ];
            }
        } catch (\Exception $e) {
            Log::error("Geocoding error for address '{$address}': " . $e->getMessage());
        }

        return null;
    }
}
