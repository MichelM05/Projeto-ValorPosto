<?php

namespace App\Console\Commands;

use App\Services\AnpImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportAnpCommand extends Command
{
    protected $signature = 'combustivel:import {file? : The path or URL of the ANP XLSX file}';
    protected $description = 'Import fuel prices from ANP XLSX';

    public function handle(AnpImportService $service): int
    {
        $file = $this->argument('file') ?: env('ANP_FILE_URL');

        if (!$file) {
            $this->error('ANP file path or URL not provided.');
            return 1;
        }

        if (filter_var($file, FILTER_VALIDATE_URL)) {
            $this->info("Downloading from {$file}...");
            try {
                $response = Http::timeout(600)->get($file);
                if (!$response->successful()) {
                    throw new \Exception("Failed to download file. Status: " . $response->status());
                }

                $tempPath = 'temp_anp_' . time() . '.xlsx';
                Storage::put($tempPath, $response->body());
                $fullPath = storage_path('app/' . $tempPath);
                $isTemporary = true;
            } catch (\Exception $e) {
                $this->error("Download Error: " . $e->getMessage());
                return 1;
            }
        } else {
            $fullPath = $file;
            if (!file_exists($fullPath)) {
                $this->error("File not found at: {$fullPath}");

                if (str_starts_with($fullPath, '/home/') || str_starts_with($fullPath, '/Users/') || preg_match('/^[a-zA-Z]:\\\\/', $fullPath)) {
                    $this->warn("\nTip: It looks like you are using a path from your host machine.");
                    $this->warn("The Docker container cannot access files outside the project folder.");
                    $this->warn("Please move the file into the 'src' directory and use a path like '/var/www/html/your-file.xlsx'");
                }

                return 1;
            }
            $isTemporary = false;
        }

        try {
            $this->info("Importing from " . basename($fullPath) . "...");
            $import = $service->import($fullPath);

            $this->info("Import finished!");
            $this->line("Read: {$import->rows_read}");
            $this->line("Inserted: {$import->rows_inserted}");
            $this->line("Ignored: {$import->rows_ignored}");

            if (!empty($import->errors)) {
                $this->warn("Warnings/Errors occurred (showing first 10):");
                foreach (array_slice($import->errors, 0, 10) as $error) {
                    $this->warn("- {$error}");
                }
            }

            if ($isTemporary) {
                Storage::delete(basename($fullPath));
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Import Error: " . $e->getMessage());
            return 1;
        }
    }
}
