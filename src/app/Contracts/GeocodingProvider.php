<?php

namespace App\Contracts;

interface GeocodingProvider
{
    /**
     * @param string $address
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocode(string $address): ?array;
}
