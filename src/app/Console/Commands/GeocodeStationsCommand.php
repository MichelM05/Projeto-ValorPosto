<?php

namespace App\Console\Commands;

use App\Contracts\GeocodingProvider;
use App\Models\Station;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GeocodeStationsCommand extends Command
{
    protected $signature = 'combustivel:geocode {--limit=50 : Number of stations to geocode}';
    protected $description = 'Geocode stations that are missing coordinates';

    public function handle(GeocodingProvider $provider): int
    {
        $stations = Station::whereNull('latitude')
            ->orWhereNull('longitude')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($stations->isEmpty()) {
            $this->info('No stations to geocode.');
            return 0;
        }

        $this->info("Geocoding {$stations->count()} stations...");

        foreach ($stations as $station) {
            $address = "{$station->address}, {$station->neighborhood}, {$station->city}, PR, Brasil";

            $coords = Cache::remember("geocode:" . md5($address), 86400 * 30, function () use ($provider, $address) {
                return $provider->geocode($address);
            });

            if ($coords) {
                $station->update([
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude'],
                ]);
                $this->info("Geocoded: {$station->name}");
            } else {
                $this->warn("Failed to geocode: {$station->name}");
            }
        }

        return 0;
    }
}
