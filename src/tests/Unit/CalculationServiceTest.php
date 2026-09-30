<?php

use App\Services\CalculationService;

test('it calculates distance with correction factor', function () {
    $service = new CalculationService();
    // Curitiba to Curitiba (same point)
    $dist = $service->calculateDistance(-25.4284, -49.2733, -25.4284, -49.2733);
    expect($dist)->toBe(0.0);

    // Approx 1km straight line should be 1.3km with factor
    // Using approx coordinates for 1km
    $dist = $service->calculateDistance(-25.4284, -49.2733, -25.4374, -49.2733);
    expect($dist)->toBeGreaterThan(1.0);
    expect($dist)->toBeLessThan(1.5);
});

test('it calculates detour savings', function () {
    $service = new CalculationService();
    // 5km extra, 10km/l, R$ 5.00/l, 50 liters to refill, R$ 0.50 cheaper
    // Gross saving = 50 * 0.50 = 25.00
    // Detour cost = (5 / 10) * 5.00 = 2.50
    // Net saving = 22.50

    $result = $service->calculateDetour(5, 10, 5.00, 50, 0.50);

    expect($result['gross_saving'])->toBe(25.0);
    expect($result['detour_cost'])->toBe(2.5);
    expect($result['net_saving'])->toBe(22.5);
});

test('it compares ethanol and gasoline', function () {
    $service = new CalculationService();

    // Default 70% threshold
    // Ethanol R$ 3.50, Gasoline R$ 5.00 -> 0.70 (Equal, favors Gasoline by default)
    $result = $service->compareEtanolGasoline(3.50, 5.00);
    expect($result['advantageous'])->toBe('GASOLINA');

    // Ethanol R$ 3.40, Gasoline R$ 5.00 -> 0.68 (Favors Ethanol)
    $result = $service->compareEtanolGasoline(3.40, 5.00);
    expect($result['advantageous'])->toBe('ETANOL');

    // Custom consumption
    // Car does 8km/l on Ethanol and 12km/l on Gasoline -> threshold 0.666
    $result = $service->compareEtanolGasoline(3.40, 5.00, 8, 12);
    expect($result['ratio'])->toBe(0.68);
    expect($result['threshold'])->toBe(0.6667);
    expect($result['advantageous'])->toBe('GASOLINA');
});
