<?php

use App\Models\FuelPrice;
use App\Models\Import;
use App\Models\Station;
use App\Services\AnpImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;

test('it imports stations and prices from anp xlsx', function () {
    $filePath = storage_path('app/test_anp.xlsx');
    $writer = new Writer();
    $writer->openToFile($filePath);

    // Header
    $writer->addRow(Row::fromValues([
        'CNPJ da Revenda', 'Revenda', 'Bandeira', 'Logradouro', 'Bairro', 'Municipio', 'Produto', 'Valor de Venda', 'Data da Coleta'
    ]));

    // Rows
    $writer->addRow(Row::fromValues([
        '12.345.678/0001-90', 'POSTO TESTE', 'BR', 'RUA TESTE, 123', 'CENTRO', 'CURITIBA', 'GASOLINA', 5.59, '2026-09-29'
    ]));
    $writer->addRow(Row::fromValues([
        '12.345.678/0001-90', 'POSTO TESTE', 'BR', 'RUA TESTE, 123', 'CENTRO', 'CURITIBA', 'ETANOL', 3.99, '2026-09-29'
    ]));
    $writer->addRow(Row::fromValues([
        '98.765.432/0001-10', 'OUTRO POSTO', 'SHELL', 'AVENIDA', 'BATEL', 'CURITIBA', 'GASOLINA COMUM', 5.69, '2026-09-29'
    ]));
    $writer->addRow(Row::fromValues([
        '00.000.000/0000-00', 'OUTRA CIDADE', 'BR', 'RUA', 'BAIRRO', 'SAO PAULO', 'GASOLINA', 5.00, '2026-09-29'
    ]));
    $writer->addRow(Row::fromValues([
        '11.111.111/0001-11', 'POSTO SJP', 'IPIRANGA', 'RUA SJP', 'CENTRO', 'SAO JOSÉ DOS PINHAIS', 'GASOLINA', 5.49, '2026-09-29'
    ]));

    $writer->close();

    $service = new AnpImportService();
    $import = $service->import($filePath);

    expect($import->rows_read)->toBe(5);
    expect($import->rows_inserted)->toBe(4); // 2 de Curitiba + 1 de SJP (normalização de acento) + 1 Etanol
    expect($import->rows_ignored)->toBe(1); // Sao Paulo

    expect(Station::count())->toBe(3);
    expect(FuelPrice::count())->toBe(4);

    $station = Station::where('cnpj', '12345678000190')->first();
    expect($station->name)->toBe('POSTO TESTE');
    expect($station->fuelPrices()->count())->toBe(2);

    unlink($filePath);
});

test('import is idempotent', function () {
    $filePath = storage_path('app/test_anp_idempotent.xlsx');
    $writer = new Writer();
    $writer->openToFile($filePath);
    $writer->addRow(Row::fromValues(['CNPJ da Revenda', 'Revenda', 'Bandeira', 'Logradouro', 'Bairro', 'Municipio', 'Produto', 'Valor de Venda', 'Data da Coleta']));
    $writer->addRow(Row::fromValues(['12.345.678/0001-90', 'POSTO TESTE', 'BR', 'RUA TESTE, 123', 'CENTRO', 'CURITIBA', 'GASOLINA', 5.59, '2026-09-29']));
    $writer->close();

    $service = new AnpImportService();

    // First import
    $service->import($filePath);
    expect(Station::count())->toBe(1);
    expect(FuelPrice::count())->toBe(1);

    // Second import (same file)
    $service->import($filePath);
    expect(Station::count())->toBe(1);
    expect(FuelPrice::count())->toBe(1);

    unlink($filePath);
});
