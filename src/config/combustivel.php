<?php

return [
    'allowed_cities' => explode(',', env('ANP_ALLOWED_CITIES', 'CURITIBA,SAO JOSE DOS PINHAIS')),

    'import' => [
        'mapping' => [
            'cnpj' => 'CNPJ',
            'name' => 'FANTASIA',
            'brand' => 'BANDEIRA',
            'address' => 'ENDEREÇO',
            'neighborhood' => 'BAIRRO',
            'city' => 'MUNICÍPIO',
            'fuel_type' => 'PRODUTO',
            'price' => 'PREÇO DE REVENDA',
            'collected_at' => 'DATA DA COLETA',
        ],
        'fuel_types' => [
            'GASOLINA' => 'GASOLINA',
            'GASOLINA COMUM' => 'GASOLINA',
            'GASOLINA ADITIVADA' => 'GASOLINA ADITIVADA',
            'ETANOL' => 'ETANOL',
            'ETANOL HIDRATADO' => 'ETANOL',
            'DIESEL S10' => 'DIESEL S10',
            'GNV' => 'GNV',
        ],
    ],

    'geocoding' => [
        'provider' => env('GEOCODING_PROVIDER', 'nominatim'),
        'user_agent' => env('GEOCODING_USER_AGENT', 'CombustivelCWB-SJP/1.0'),
    ],

    'distance' => [
        'correction_factor' => env('DISTANCE_CORRECTION_FACTOR', 1.3),
    ],
];
