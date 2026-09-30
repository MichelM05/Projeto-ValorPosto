<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\FuelPrice;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            [
                'cnpj' => '12345678000101',
                'name' => 'POSTO CURITIBA CENTRO',
                'brand' => 'IPIRANGA',
                'address' => 'RUA XV DE NOVEMBRO, 100',
                'neighborhood' => 'CENTRO',
                'city' => 'CURITIBA',
                'latitude' => -25.4284,
                'longitude' => -49.2733,
                'prices' => [
                    ['fuel_type' => 'GASOLINA', 'price' => 5.89],
                    ['fuel_type' => 'ETANOL', 'price' => 3.99],
                    ['fuel_type' => 'DIESEL', 'price' => 5.79],
                ]
            ],
            [
                'cnpj' => '87654321000102',
                'name' => 'POSTO SJP AEROPORTO',
                'brand' => 'SHELL',
                'address' => 'AV. ROCHA POMBO, 2000',
                'neighborhood' => 'AGUAS BELAS',
                'city' => 'SAO JOSE DOS PINHAIS',
                'latitude' => -25.5348,
                'longitude' => -49.2064,
                'prices' => [
                    ['fuel_type' => 'GASOLINA', 'price' => 5.79],
                    ['fuel_type' => 'ETANOL', 'price' => 3.89],
                    ['fuel_type' => 'DIESEL', 'price' => 5.69],
                ]
            ],
            [
                'cnpj' => '11223344000103',
                'name' => 'POSTO BATEL',
                'brand' => 'PETROBRAS',
                'address' => 'AV. VICENTE MACHADO, 500',
                'neighborhood' => 'BATEL',
                'city' => 'CURITIBA',
                'latitude' => -25.4397,
                'longitude' => -49.2820,
                'prices' => [
                    ['fuel_type' => 'GASOLINA', 'price' => 6.09],
                    ['fuel_type' => 'ETANOL', 'price' => 4.19],
                ]
            ]
        ];

        foreach ($stations as $sData) {
            $prices = $sData['prices'];
            unset($sData['prices']);

            $station = Station::create($sData);

            foreach ($prices as $pData) {
                FuelPrice::create([
                    'station_id' => $station->id,
                    'fuel_type' => $pData['fuel_type'],
                    'price' => $pData['price'],
                    'collected_at' => Carbon::now(),
                ]);
            }
        }
    }
}
