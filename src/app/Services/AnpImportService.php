<?php

namespace App\Services;

use App\Models\FuelPrice;
use App\Models\Import;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenSpout\Reader\XLSX\Reader;
use Illuminate\Support\Str;

class AnpImportService
{
    private array $config;
    private array $allowedCities;

    public function __construct()
    {
        $this->config = config('combustivel.import');
        $this->allowedCities = array_map(fn($city) => $this->normalizeString($city), config('combustivel.allowed_cities'));
    }

    public function import(string $filePath): Import
    {
        $import = Import::create([
            'source' => basename($filePath),
            'imported_at' => now(),
        ]);

        $rowsRead = 0;
        $rowsInserted = 0;
        $rowsIgnored = 0;
        $errors = [];

        $reader = new Reader();
        $reader->open($filePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            $headerMap = [];
            foreach ($sheet->getRowIterator() as $index => $row) {
                $cells = $row->toArray();

                // Pula linhas informativas e procura o cabeçalho técnico
                if ($index < 8) {
                    continue;
                }

                if ($index === 8) {
                    $headerMap = array_flip(array_map(fn($cell) => (string) $cell, $cells));
                    continue;
                }

                $rowsRead++;
                try {
                    $data = $this->mapRow($cells, $headerMap, $this->config['mapping']);

                    if (!$this->isCityAllowed($data['city'])) {
                        $rowsIgnored++;
                        continue;
                    }

                    $fuelType = $this->normalizeFuelType($data['fuel_type']);
                    if (!$fuelType) {
                        $rowsIgnored++;
                        continue;
                    }

                    DB::transaction(function () use ($data, $fuelType, &$rowsInserted) {
                        $station = Station::updateOrCreate(
                            ['cnpj' => $this->normalizeCnpj($data['cnpj'])],
                            [
                                'name' => $data['name'],
                                'brand' => $data['brand'],
                                'address' => $data['address'],
                                'neighborhood' => $data['neighborhood'],
                                'city' => strtoupper(trim($data['city'])),
                            ]
                        );

                        $price = $this->normalizePrice($data['price']);
                        $collectedAt = $this->normalizeDate($data['collected_at']);

                        FuelPrice::updateOrCreate(
                            [
                                'station_id' => $station->id,
                                'fuel_type' => $fuelType,
                                'collected_at' => $collectedAt,
                            ],
                            ['price' => $price]
                        );

                        $rowsInserted++;
                    });
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowsRead}: " . $e->getMessage();
                    Log::error("Import error at row {$rowsRead}: " . $e->getMessage());
                }
            }
            break; // A ANP geralmente usa apenas a primeira aba
        }

        $reader->close();

        $import->update([
            'rows_read' => $rowsRead,
            'rows_inserted' => $rowsInserted,
            'rows_ignored' => $rowsIgnored,
            'errors' => $errors,
        ]);

        return $import;
    }

    private function mapRow(array $cells, array $headerMap, array $mapping): array
    {
        $data = [];
        foreach ($mapping as $key => $columnName) {
            if (!isset($headerMap[$columnName])) {
                throw new \Exception("Column '{$columnName}' not found in file");
            }
            $data[$key] = $cells[$headerMap[$columnName]] ?? null;
        }
        return $data;
    }

    private function isCityAllowed(string $city): bool
    {
        return in_array($this->normalizeString($city), $this->allowedCities);
    }

    private function normalizeString(string $string): string
    {
        return (string) Str::of($string)
            ->ascii()
            ->upper()
            ->trim();
    }

    private function normalizeCnpj(string $cnpj): string
    {
        return preg_replace('/\D/', '', $cnpj);
    }

    private function normalizeFuelType(string $type): ?string
    {
        $normalized = $this->normalizeString($type);
        return $this->config['fuel_types'][$normalized] ?? null;
    }

    private function normalizePrice($price): float
    {
        if (is_numeric($price)) {
            return (float) $price;
        }
        return (float) str_replace(',', '.', (string) $price);
    }

    private function normalizeDate($date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return Carbon::parse($date)->format('Y-m-d');
        }
    }
}
