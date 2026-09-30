<?php

namespace App\Services;

class CalculationService
{
    /**
     * @param float $lat1 Latitude of point 1
     * @param float $lon1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lon2 Longitude of point 2
     * @return float Distance in km
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance * config('combustivel.distance.correction_factor', 1.3);
    }

    /**
     * @param float $distanceDiff Difference in km
     * @param float $consumption km/l
     * @param float $fuelPrice price per liter
     * @param float $litersToRefill
     * @param float $priceDiff Difference in price per liter
     * @return array{gross_saving: float, detour_cost: float, net_saving: float}
     */
    public function calculateDetour(
        float $distanceDiff,
        float $consumption,
        float $fuelPrice,
        float $litersToRefill,
        float $priceDiff
    ): array {
        $grossSaving = $priceDiff * $litersToRefill;
        $detourCost = ($distanceDiff / $consumption) * $fuelPrice;
        $netSaving = $grossSaving - $detourCost;

        return [
            'gross_saving' => round($grossSaving, 2),
            'detour_cost' => round($detourCost, 2),
            'net_saving' => round($netSaving, 2),
        ];
    }

    /**
     * @param float $priceEtanol
     * @param float $priceGasoline
     * @param float|null $consumptionEtanol km/l
     * @param float|null $consumptionGasoline km/l
     * @return array{advantageous: string, ratio: float, threshold: float}
     */
    public function compareEtanolGasoline(
        float $priceEtanol,
        float $priceGasoline,
        ?float $consumptionEtanol = null,
        ?float $consumptionGasoline = null
    ): array {
        $ratio = $priceEtanol / $priceGasoline;

        if ($consumptionEtanol && $consumptionGasoline) {
            $threshold = $consumptionEtanol / $consumptionGasoline;
        } else {
            $threshold = 0.70; // General average
        }

        return [
            'advantageous' => $ratio < $threshold ? 'ETANOL' : 'GASOLINA',
            'ratio' => round($ratio, 4),
            'threshold' => round($threshold, 4),
        ];
    }
}
